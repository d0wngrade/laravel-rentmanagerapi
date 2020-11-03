<?php

namespace Zinapse\RentManagerAPI\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    public function report(Exception $e)
    {
        if($e instanceof UnauthorizedException) 
        {
            \Illuminate\Support\Facades\Log::error('-- RentManagerAPI Error: Unauthorized');
        }
    }
}
