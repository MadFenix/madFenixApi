<?php

namespace App\Modules\EmployeeManager\Infrastructure\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
            ],
            'position' => [
                'required',
                'string',
                'max:255',
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => trans('Employee name is required.'),
            'email.required' => trans('Email address is required.'),
            'email.unique' => trans('This email is already taken.'),
        ];
    }
}
