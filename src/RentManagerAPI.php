<?php

namespace zinapse\RentManagerAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class RMAPI
{
    private $api_token;
    private $headers;
    private $username;
    private $password;
    private $location_id;

    /**
     * RentManagerAPI constructor. Can optionally pass a GuzzleHttp Client object to use,
     * or one will be created for you.
     *
     * @param Client $client Optional client object can be passed. If null one will be created.
     * @param bool $auth If this is not true the api_token will not be automatically generated.
     */
    function __construct(Client $client = null, bool $auth = true)
    {
        if (empty($client)) 
        {
            // Create the client object
            $this->client = new Client([
                'base_uri' => config('rentmanager.base_uri')
            ]);

            // Make the headers empty (just in case for below)
            $this->headers = [];
        }

        // The rent manager API variables we'll need to get an auth token
        $this->username     = config('rentmanager.api_username');
        $this->password     = config('rentmanager.api_password');
        $this->location_id  = config('rentmanager.api_locationid');
        
        // Check that we have our required variables
        if (empty($this->username) || empty($this->password) || empty($this->location_id)) 
        {
            Log::error('RentManagerAPI Error: Missing requried variable for authentication.');
            return null;
        }

        if($auth) $this->authenticate();
    }

    /**
     * Verify or create a client class variable.
     *
     * @return bool
     */
    protected function verifyClient() : bool
    {
        if (empty($this->client)) 
        {
            // Create the client object
            $this->client = new Client([
                'base_uri' => config('rentmanager.base_uri')
            ]);

            // Make the headers empty (just in case for below)
            $this->headers = [];
        }

        return !empty($this->client);
    }

    /**
     * Authenticate us with the Rent Manager API to get an API token we store
     * in our headers variable.
     *
     * @return void
     */
    protected function authenticate() : void
    {
        // Make sure we have a valid client object
        if(!$this->verifyClient()) 
        {
            Log::error('RentManagerAPI Error: Cannot verify client object. (RMAPI::authenticate)');

            // Reset the class variables if there's an error
            $this->api_token = null;
            $this->headers = [];
            
            return null;
        }

        // The URI we can get an auth token from, and the JSON body to send
        $headers = ['Content-Type' => 'application/json'];
        $validationEndpoint = 'Authentication/AuthorizeUser';
        $authJSON = "{
            \"Username\": \"$this->username\",
            \"Password\": \"$this->password\",
            \"LocationID\": \"$this->location_id\"
        }";

        // Get our auth token
        try {
            $response = $this->client->post($validationEndpoint, [
                'body'  => $authJSON,
                'headers' => $headers
            ]);
        } catch (ClientException $e) {
            $code = $e->getResponse()->getStatusCode();
            if ($code == 401) {
                Log::error('RentManagerAPI Error: Unauthorized');
                
                return null;
            }
        }

        // Format the token and set our class variables
        $token = trim($response->getBody(), '\"');
        $this->headers['X-RM12Api-ApiToken'] = $token;
        $this->api_token = $token;
    }

    /**
     * Update the local class API variables.
     *
     * @param array $headers Optional headers to pass
     * @param bool $force If this is true the class headers will be forced to overwrite
     * @return array Updated header array
     */
    public function updateHeaders(array $headers = [], bool $force = false) : array
    {
        // Check variables
        if((empty($headers) || empty($headers['Content-Type']) || empty($headers['X-RM12Api-ApiToken'])) && !$force)
        {
            // Authenticate if we can't find a token
            if(empty($this->api_token) && empty($this->headers['X-RM12Api-ApiToken'])) $this->authenticate();
            
            // Create a headers variable to update with
            $update_headers = [
                'Content-Type' => 'application/json',
                'X-RM12Api-ApiToken' => $this->headers['X-RM12Api-ApiToken'] ?? $this->api_token
            ];
        } elseif($force) {
            // If force is true we just let them override it
            $update_headers = $headers;
        } else {
            // Just return the current headers if we don't need to update them
            return $this->headers;
        }

        // Set the new headers and return the variable
        $this->headers = $update_headers;
        return $update_headers;
    }

    /**
     * Send a POST request to the URI from the JSON data
     * 
     * See variable examples in the function's comments
     *
     * @param string $uri
     * @param string $json The JSON formatted for the POST request
     * @return bool
     */
    public function post(string $uri, string $json) : bool
    {
        if (!$this->verifyClient()) 
        {
            Log::error('RentManagerAPI Error: Cannot verify client object. (RMAPI::post)');

            // Reset the class variables if there's an error
            $this->api_token = null;
            $this->headers = [];

            return false;
        }
        // Example JSON variable -----------------------------------------------
        // $updateMarketRentJSON = "{                                           |
        //     \"MarketRentID\": \"$marketRentID\",                             |
        //     \"Amount\": \"$amount\",                                         |
        //     \"UpdateDate\": \"$date\",                                       |
        //     \"Comment\": \"Base Rent: $originalMarketRent\"                  |
        // }";                                                                  |
        // ---------------------------------------------------------------------
        if(empty($json))
        {
            Log::error('RentManagerAPI Error: Empty JSON passed to update.');
            return false;
        }

        // Example URI variable ------------------------------------------------
        // $uri = 'MarketRents?fields=MarketRentID,Amount,UpdateDate,Comment';  |
        // ---------------------------------------------------------------------
        if(empty($uri))
        {
            Log::error('RentManagerAPI Error: Empty URI passed to update.');
            return false;
        }

        try {
            // Set required $headers variable
            $headers = $this->updateHeaders();
            
            // Send our POST request
            $this->client->post($uri, [
                'body'      => $json,
                'headers'   => $headers
            ]);
            
            return true;
        } catch (ClientException $e) {
            $code = $e->getResponse()->getStatusCode();
            if ($code == 401) {
                Log::error("RentManagerAPI Error: Unauthorized");
                return false;
            } else {
                Log::error(strval($e->getMessage()));
                return false;
            }
        }
    }

    /**
     * Send a GET request to the URI and return the response as an array
     *
     * @param string $uri The URI to send the requesy to, prepended with config('rentmanager.base_uri)
     * @param bool $paginate If this is true the URI will be appended with "?PageSize=$pagesize&PageNumber=" for iteration
     * @param int $pagesize The page size for pagination
     * @return array
     */
    public function get($uri, $paginate = false, $pagesize = 50) : array
    {
        if (!$this->verifyClient()) 
        {
            Log::error('RentManagerAPI Error: Cannot verify client object. (RMAPI::get)');

            // Reset the class variables if there's an error
            $this->api_token = null;
            $this->headers = [];

            return [];
        }

        // Define our final return variable
        $final_ret = [];

        
        // Example URI variable ------------------------------------------------
        // $uri = '/Amenities';                                                 |
        // ---------------------------------------------------------------------
        // Define the URI here to manipulate it
        $get_uri = $uri;

        // Check if we need to iterate
        if($paginate)
        {
            $itter = 1;
            $get_uri = rtrim('/', $get_uri) . "?PageSize=$pagesize&PageNumber=";
            while (true) 
            {
                try {
                    // Define a headers array
                    $headers = $this->updateHeaders();

                    // Send the request
                    $response = $this->client->get($get_uri . $itter, [
                        'headers' => $headers
                    ]);
                } catch (ClientException $e) {
                    Log::error($e->getMessage());
                    return [];
                }
    
                // Status code 204 would mean we've gone through all the pages
                if ($response->getStatusCode() == 204) {
                    return $final_ret;
                }
    
                // Decode the response JSON and add it to the final return
                $ret = json_decode($response->getBody(), true);
                $final_ret[] = $ret;
    
                // Increment our loop variable
                $itter++;
            }
        } else {
            try {
                // Send the request
                $response = $this->client->get($get_uri, [
                    'headers' => $this->headers
                ]);
            } catch (ClientException $e) {
                Log::error($e->getMessage());
                return [];
            }

            // Decode the response JSON and add it to the final return
            $ret = json_decode($response->getBody(), true);
            $final_ret[] = $ret;
        }

        // Return the final array
        return $final_ret;
    }
}