<?php

namespace App\Modules\EmployeeManager\Domain;

use App\Enums\EmployeePosition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'email', 'position', 'phone_number'];

    public function user()
    {
        return $this->belongsTo('App\Modules\User\Domain\User', 'user_id');
    }

    public function isManager()
    {
        if ($this->position == EmployeePosition::fromName('MANAGER')) {
            return true;
        }

        return false;
    }

    public function isEmployee()
    {
        if ($this->position == EmployeePosition::fromName('EMPLOYEE')) {
            return true;
        }

        return false;
    }
}
