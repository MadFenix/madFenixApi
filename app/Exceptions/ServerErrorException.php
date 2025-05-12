<?php

namespace App\Exceptions;

use Exception;

class ServerErrorException extends Exception
{
    public function __construct($message = "Internal server error.")
    {
        parent::__construct($message);
    }
}
