<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


/**
 * @property string $email
 * @property string $password
 */
class LoginRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'email' => [
            //     'required',
            //     'email',
            //     function ($attribute, $value, $fail) {
            //         if (!preg_match('/@huit\.edu\.vn$/', $value)) {
            //             $fail('Must register with HUIT student email');
            //         }
            //     },
            // ],
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'password.required' => 'Password is required',
            'password.string' => 'Password must be a string',
            'password.min' => 'Password must be at least 6 characters',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorResponse(
                422,
                'Validation failed',
                $validator->errors()
            )
        );
    }
}
