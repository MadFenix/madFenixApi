<?php

namespace App\Exceptions;

use Exception;

class ValidationErrorException extends Exception
{
    public function __construct($message = "Validation error.")
    {
        parent::__construct($message);
    }
}
