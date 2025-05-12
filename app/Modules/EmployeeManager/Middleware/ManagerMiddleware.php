<?php

namespace App\Modules\EmployeeManager\Middleware;

use App\Enums\EmployeePosition;
use App\Exceptions\ForbiddenException;
use App\Modules\EmployeeManager\Domain\Employee;
use App\Modules\EmployeeManager\Service\EmployeeService;
use Closure;

class ManagerMiddleware
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
                $accesoPermitido = $employee->isManager();
            }
        }

        if (!$accesoPermitido) {
            throw new ForbiddenException();
        }

        return $next($request);
    }
}
