<?php

namespace Zinapse\RentManagerAPI\Exceptions;

use Exception;

class EmptyAuthenticateException extends Exception
{
    public function report(Exception $e)
    {
        if($e instanceof MissingVariableException)
        {
            \Illuminate\Support\Facades\Log::error('-- RentManagerAPI Error: Authenticate variable ($auth) missing or empty.');
        }
    }
}