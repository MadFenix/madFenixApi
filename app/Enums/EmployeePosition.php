<?php

namespace App\Enums;

enum EmployeePosition: string
{
    case EMPLOYEE = 'Employee';

    case DEVELOPER = 'Developer';
    case MANAGER = 'Manager';
    case ENGINEER = 'Engineer';
    case DESIGNER = 'Designer';

    public static function fromName(string $name): string
    {
        foreach (self::cases() as $status) {
            if( $name === $status->name ){
                return $status->value;
            }
        }
        throw new \ValueError("$name is not a valid backing value for enum " . self::class );
    }
}
