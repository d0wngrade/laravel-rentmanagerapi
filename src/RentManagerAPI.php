<?php

namespace Zinapse\RentManagerAPI;

use GuzzleHttp\Client;
use Zinapse\RentManagerAPI\Authenticate;
use Zinapse\RentManagerAPI\Request\Get;
use Zinapse\RentManagerAPI\Request\Post;

class RentManagerAPI
{
    /**
     * Variable to hold the client.
     *
     * @var Client
     */
    protected $client;

    /**
     * Variable to hold the Authenticate variable.
     *
     * @var Authenticate
     */
    protected $authenticate;

    /**
     * Headers array.
     *
     * @var array
     */
    protected $headers;

    /**
     * Hold the response here, if one exists.
     *
     * @var array
     */
    public $response;

    function __construct()
    {
        // Create our Authenticate variable
        $auth = new Authenticate();
        $token = $auth->getToken() ?? '';

        // Throw an exception if there's an error
        if($token == 'E' || empty($token)) throw new \Zinapse\RentManagerAPI\Exceptions\EmptyAuthenticateException;
        else 
        {
            // Assign our new token
            $this->authenticate = $auth;

            // Assign the headers
            $this->headers = [
                'Content-Type' => 'application/json',
                'X-RM12Api-ApiToken' => $token
            ];
        }
    }

    /**
     * Send a request to the Rent Manager API. Will return an array for GET requests.
     *
     * @param string $uri
     * @param string $method The method to use (GET/POST)
     * @param string $post_json If POST send this JSON
     * @return void|array
     */
    public function request($uri, $method = 'GET', $post_json = '')
    {
        if(empty($uri))
        {
            return null;
        }

        // Format the method
        $method = strtoupper($method);

        // GET request
        if($method == 'GET')
        {
            $request = new Get($uri, $this->headers);
            $response = $request->send($this->client);
            $this->response = $response;
        }

        // POST request
        if($method == 'POST')
        {
            $request = new Post($uri, $post_json, $this->headers);
            $request->send($this->client);
        }

        return $response ?? null;
    }

    /**
     * Get our API token.
     *
     * @return string
     */
    public function getToken() : string
    {
        return $this->authenticate->getToken() ?? 'E';
    }
}