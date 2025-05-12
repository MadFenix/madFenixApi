<?php

namespace App\Exceptions;

use Exception;

class ForbiddenException extends Exception
{
    public function __construct($message = "Access denied.")
    {
        parent::__construct($message);
    }
}
