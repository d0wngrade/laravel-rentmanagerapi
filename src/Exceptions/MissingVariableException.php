<?php

namespace Zinapse\RentManagerAPI\Exceptions;

use Exception;

class MissingVariableException extends Exception
{
    public function report(Exception $e)
    {
        if($e instanceof MissingVariableException)
        {
            \Illuminate\Support\Facades\Log::error('-- RentManagerAPI Error: Missing requried variable for authentication.');
        }
    }
}