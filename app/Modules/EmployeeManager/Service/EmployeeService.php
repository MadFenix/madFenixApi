<?php

namespace App\Modules\EmployeeManager\Service;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerErrorException;
use App\Exceptions\ValidationErrorException;
use App\Modules\EmployeeManager\Domain\Employee;
use App\Modules\User\Domain\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EmployeeService
{
    public function getAllEmployees()
    {
        try {
            $employee = EmployeeService::getEmployeesByAuthUser();
            if (!$employee || !$employee->isManager()) {
                $newEmployees = new Collection();
                $newEmployees->push($employee);

                return $newEmployees;
            }
            return Employee::all();
        } catch (\Throwable $th) {
            Log::error("Could not retrieve employees", [
                "message" => $th->getMessage(),
                "trace" => $th->getTrace(),
            ]);
            throw new ServerErrorException(trans("Server error, could not retrieve employees!"));
        }
    }

    /**
     * @return null|Employee
     */
    static public function getEmployeesByAuthUser()
    {
        $user = auth()->user();

        return Employee::where('user_id', '=', $user->id)->first();
    }

    public function usersWithoutEmployee()
    {
        try {
            $users = User::all();
            $employees = Employee::all();

            $finalUsers = [];
            foreach ($users as $user) {
                $employeeExists = false;
                foreach ($employees as $employee) {
                    if ($user->id == $employee->user_id) {
                        $employeeExists = true;
                    }
                }
                if (!$employeeExists) {
                    $finalUsers[] = $user;
                }
            }

            return $finalUsers;
        } catch (\Throwable $th) {
            Log::error("Could not retrieve users without employee", [
                "message" => $th->getMessage(),
                "trace" => $th->getTrace(),
            ]);
            throw new ServerErrorException(trans("Server error, could not retrieve users without employee!"));
        }
    }

    public function getEmployeeById(int $employeeId)
    {
        try {
            $employee = EmployeeService::getEmployeesByAuthUser();
            if (!$employee || !$employee->isManager()) {
                $employeeId = $employee->id;
            }
            $employee = Employee::find($employeeId);

            if (!$employee) {
                throw new NotFoundException(trans("Employee not found!"));
            }

            return $employee;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Throwable $th) {
            Log::error("Could not retrieve employee", [
                "id" => $employeeId,
                "message" => $th->getMessage(),
                "trace" => $th->getTrace(),
            ]);
            throw new ServerErrorException(trans("Server error, could not retrieve employee!"));
        }
    }

    public function updateEmployee(int $employeeId, array $data)
    {
        try {
            $employee = EmployeeService::getEmployeesByAuthUser();
            if (!$employee || !$employee->isManager()) {
                throw new ForbiddenException();
            }
            $employee = Employee::find($employeeId);

            if (!$employee) {
                throw new NotFoundException(trans("Employee not found!"));
            }

            $employee->update($data);

            return $employee;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Throwable $th) {
            Log::error("Could not update employee", [
                "id" => $employeeId,
                "data" => $data,
                "message" => $th->getMessage(),
                "trace" => $th->getTrace(),
            ]);
            throw new ServerErrorException(trans("Server error, could not update employee!"));
        }
    }

    public function createEmployee(array $data)
    {
        try {
            $employee = EmployeeService::getEmployeesByAuthUser();
            if (!$employee || !$employee->isManager()) {
                throw new ForbiddenException();
            }
            $employeeExists = Employee::where("email", $data['email'])->exists();
            if ($employeeExists) {
                throw new ValidationErrorException(trans("Employee already exists!"));
            }

            return Employee::create($data);
        } catch (ValidationErrorException $e) {
            throw $e;
        } catch (\Throwable $th) {
            Log::error("Could not create employee", [
                "data" => $data,
                "message" => $th->getMessage(),
                "trace" => $th->getTrace(),
            ]);
            throw new ServerErrorException(trans("Server error, could not create employee!"));
        }
    }

    public function deleteEmployee(int $employeeId)
    {
        try {
            $employee = EmployeeService::getEmployeesByAuthUser();
            if (!$employee || !$employee->isManager()) {
                throw new ForbiddenException();
            }
            $employee = Employee::find($employeeId);

            if (!$employee) {
                throw new NotFoundException(trans("Employee not found!"));
            }

            $employee->delete();
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Throwable $th) {
            Log::error("Could not delete employee", [
                "id" => $employeeId,
                "message" => $th->getMessage(),
                "trace" => $th->getTrace(),
            ]);
            throw new ServerErrorException(trans("Server error, could not delete employee!"));
        }
    }
}
