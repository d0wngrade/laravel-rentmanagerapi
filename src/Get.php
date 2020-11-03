<?php

namespace Zinapse\RentManagerAPI\Request;

use GuzzleHttp\Exception\ClientException;

class Get
{
    /**
     * The URI to send the GET request to.
     *
     * @var string
     */
    protected $uri;

    /**
     * Variable to hold our headers array.
     *
     * @var array
     */
    protected $headers;

    /**
     * Constructor
     *
     * @param string $uri
     * @param array $headers
     */
    function __construct($uri, $headers)
    {
        if (empty($uri)) {
            // Log::error('RentManagerAPI Error: Empty JSON passed to update.');
            return false;
        }
        $this->uri = $uri;

        if (empty($headers)) {
            return false;
        }
        $this->headers = $headers;
    }

    public function getResponse()
    {

    }

    /**
     * Send a GET request to the URI and return the response as an array
     *
     * @param Client $client GuzzleHttp Client object to use
     * @param bool $paginate If this is true the URI will be appended with "?PageSize=$pagesize&PageNumber=" for iteration
     * @param int $pagesize The page size for pagination
     * @return array
     */
    public function send($client, $paginate = false, $pagesize = 50): array
    {
        // Make sure we get a valid client
        if(!$client instanceof \GuzzleHttp\Client)
        {
            return false;
        }

        // Define our final return variable
        $final_ret = [];

        // Example URI variable ------------------------------------------------
        // $uri = '/Amenities';                                                 |
        // ---------------------------------------------------------------------
        // Define the URI here to manipulate it
        $get_uri = $this->uri;

        // Check if we need to iterate
        if ($paginate) {
            $itter = 1;
            $get_uri = rtrim('/', $get_uri) . "?PageSize=$pagesize&PageNumber=";
            while (true) {
                try {
                    // Send the request
                    $response = $client->get($get_uri . $itter, [
                        'headers' => $this->headers
                    ]);
                } catch (ClientException $e) {
                    // Log::error($e->getMessage());
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
                $response = $client->get($get_uri, [
                    'headers' => $this->headers
                ]);
            } catch (ClientException $e) {
                // Log::error($e->getMessage());
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