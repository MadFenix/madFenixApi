<?php

namespace App\Modules\EmployeeManager\Middleware;

use App\Exceptions\ForbiddenException;
use App\Modules\EmployeeManager\Service\EmployeeService;
use Closure;

class EmployeeMiddleware
{
    public function handle($request, Closure $next)
    {
        $employee = EmployeeService::getEmployeesByAuthUser();

        $user = auth()->user();
        $accesoPermitido = false;
        if ($user->id == 1) {
            $accesoPermitido = true;
        } else {
            if ($employee) {
                $accesoPermitido = $employee->isEmployee() || $employee->isManager();
            }
        }

        if (!$accesoPermitido) {
            throw new ForbiddenException();
        }

        return $next($request);
    }
}
