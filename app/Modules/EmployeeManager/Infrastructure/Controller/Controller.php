<?php

namespace App\Modules\EmployeeManager\Infrastructure\Controller;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerErrorException;
use App\Exceptions\ValidationErrorException;
use Throwable;

abstract class Controller
{

    public function formatExceptionError(Throwable $e)
    {

        $message = $e->getMessage();

        if ($e instanceof NotFoundException) {
            $status = 404;
        } else if ($e instanceof ForbiddenException) {
            $status = 403;
        } else if ($e instanceof ServerErrorException) {
            $status = 500;
        } else if ($e instanceof ValidationErrorException) {
            $status = 422;
        } else {
            $status = 500;
        }

        return response()->json([
            "message" => $message,
            "status" => $status
        ], $status);
    }
}
