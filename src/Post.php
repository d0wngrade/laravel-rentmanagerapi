<?php

namespace Zinapse\RentManagerAPI\Request;

use GuzzleHttp\Exception\ClientException;

class Post
{
    // Example JSON variable ------------------------------------------------
    // $updateMarketRentJSON = "{                                           |
    //     \"MarketRentID\": \"$marketRentID\",                             |
    //     \"Amount\": \"$amount\",                                         |
    //     \"UpdateDate\": \"$date\",                                       |
    //     \"Comment\": \"Base Rent: $originalMarketRent\"                  |
    // }";                                                                  |
    // ----------------------------------------------------------------------
    public $json;

    // Example URI variable -------------------------------------------------
    // $uri = 'MarketRents?fields=MarketRentID,Amount,UpdateDate,Comment';  |
    // ----------------------------------------------------------------------
    public $uri;

    /**
     * Variable to hold the headers array.
     *
     * @var array
     */
    public $headers;
    
    /**
     * Constructor
     *
     * @param string $uri
     * @param string $json
     * @param array $headers
     */
    function __construct($uri, $json, $headers)
    {
        if(empty($json))
        {
            // Log::error('RentManagerAPI Error: Empty JSON passed to update.');
            return false;
        }
        $this->json = $json;

        if(empty($uri))
        {
            // Log::error('RentManagerAPI Error: Empty URI passed to update.');
            return false;
        }
        $this->uri = $uri;

        if(empty($headers))
        {
            return false;
        }
        $this->headers = $headers;
    }

    /**
     * Send a POST request.
     *
     * @param Client $client GuzzleHttp Client object to use
     * @return bool
     */
    public function send($client) : bool
    {
        // Make sure we get a valid client
        if(!$client instanceof \GuzzleHttp\Client)
        {
            return false;
        }
        
        try {
            // Send our POST request
            $client->post($this->uri, [
                'body'      => $this->json,
                'headers'   => $this->headers
            ]);
        } catch (ClientException $e) {
            $code = $e->getResponse()->getStatusCode();
            if ($code == 401) {
                throw new \Zinapse\RentManagerAPI\Exceptions\UnauthorizedException;
            } else {
                // Log::error(strval($e->getMessage()));
            }

            // Return false on catch()
            return false;
        }

        return true;
    }

    /**
     * Set the object's JSON variable
     *
     * @param string $json The new JSON string to use.
     * @return void
     */
    public function setJson($json) : void
    {
        $this->json = $json;
    }
}