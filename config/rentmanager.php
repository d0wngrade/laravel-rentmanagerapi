<?php

return [

    'rentmanager' => [

        /*
        |--------------------------------------------------------------------------
        | api_username
        |--------------------------------------------------------------------------
        |
        | Your username for the Rent Manager API
        |
        */
        'api_username'          => getenv('RENTMANAGER_API_USERNAME', ''),

        /*
        |--------------------------------------------------------------------------
        | api_password
        |--------------------------------------------------------------------------
        |
        | Your password for the Rent Manager API
        |
        */
        'api_password'          => getenv('RENTMANAGER_API_PASSWORD', ''),

        /*
        |--------------------------------------------------------------------------
        | api_locationid
        |--------------------------------------------------------------------------
        |
        | The location ID your user has access to
        |
        */
        'api_locationid'        => getenv('RENTMANAGER_API_LOCATIONID', ''),

        /*
        |--------------------------------------------------------------------------
        | base_uri
        |--------------------------------------------------------------------------
        |
        | Your Rent Manager API's base URI ( Like: 'https://yoursubdomain.api.rentmanager.com/' )
        |
        */
        'rentmanager.base_uri'  => getenv('RENTMANAGER_API_BASE_URI', '')

    ]
    
];
