<?php

namespace App\Modules\EmployeeManager\Infrastructure\Controller;

use App\Modules\EmployeeManager\Domain\Employee;
use App\Modules\EmployeeManager\Infrastructure\Request\EmployeeRequest;
use App\Modules\EmployeeManager\Service\EmployeeService;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $employeeService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return $this->employeeService->getAllEmployees();
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeRequest $request)
    {
        try {
            $this->employeeService->createEmployee($request->validated());
            return response(null, Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $employeeId)
    {
        try {
            return $this->employeeService->getEmployeeById($employeeId);
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function userIsManager()
    {
        try {
            $employee = EmployeeService::getEmployeesByAuthUser();

            $isManager = false;
            $user = auth()->user();
            if ($user->id == 1) {
                $isManager = true;
            } else {
                if ($employee) {
                    $isManager = $employee->isManager();
                }
            }

            return response()->json($isManager);
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function userIsEmployee()
    {
        try {
            $employee = EmployeeService::getEmployeesByAuthUser();

            $isEmployee = false;
            $user = auth()->user();
            if ($user->id == 1) {
                $isEmployee = true;
            } else {
                if ($employee) {
                    $isEmployee = $employee->isEmployee() || $employee->isManager();
                }
            }

            return response()->json($isEmployee);
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function usersWithoutEmployee()
    {
        try {
            return $this->employeeService->usersWithoutEmployee();
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeRequest $request, $account, int $employeeId)
    {
        try {
            $updatedEmployee = $this->employeeService->updateEmployee($employeeId, $request->validated());
            return response()->json($updatedEmployee);
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($account, int $employeeId)
    {
        try {
            $this->employeeService->deleteEmployee($employeeId);
            return response()->noContent();
        } catch (\Throwable $th) {
            return $this->formatExceptionError($th);
        }
    }
}
