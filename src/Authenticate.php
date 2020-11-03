<?php

namespace Zinapse\RentManagerAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Authenticate
{
    /**
     * The API token returned from the Rent Manager API.
     *
     * @var string
     */
    protected $api_token;

    /**
     * The username to pass to the Rent Manager API.
     *
     * @var string
     */
    private $username;

    /**
     * The password to pass to the Rent Manager API.
     *
     * @var string
     */
    private $password;

    /**
     * The location ID to pass to the Rent Manager API.
     *
     * @var string
     */
    private $location_id;

    /**
     * The Guzzle client variable to use.
     *
     * @var Client
     */
    private $client;

    /**
     * RentManagerAPI constructor. Can optionally pass a GuzzleHttp Client object to use,
     * or one will be created for you.
     *
     * @param Client $client Optional client object can be passed. If null one will be created.
     */
    function __construct(Client $client = null)
    {
        if (empty($client))
        {
            // Create the client object
            $this->client = new Client([
                'base_uri' => getenv('RENT_MANAGER_API_BASE_URI', '')
            ]);
        }

        // The rent manager API variables we'll need to get an auth token
        $this->username     = getenv('RENT_MANAGER_API_USERNAME', '');
        $this->password     = getenv('RENT_MANAGER_API_PASSWORD', '');
        $this->location_id  = getenv('RENT_MANAGER_API_LOCATION_ID', '');
        
        // Check that we have our required variables
        if (empty($this->username) || empty($this->password) || empty($this->location_id)) 
        {
            throw new \Zinapse\RentManagerAPI\Exceptions\MissingVariableException;
            return null;
        }

        // Authenticate with the API
        $this->authenticate();
    }

    /**
     * Authenticate us with the Rent Manager API to get an API token.
     *
     * @return void
     */
    public function authenticate()
    {
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
                throw new \Zinapse\RentManagerAPI\Exceptions\UnauthorizedException;
                return null;
            }
        }

        // Format the token and set our class variables
        $token = trim($response->getBody(), '\"');
        $this->api_token = $token;
    }

    public function getToken() : string
    {
        return $this->api_token ?? 'E';
    }
}