<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => 'required|string|min:3|max:100',
            'email'                 => 'required|email|max:191|unique:users,email',
            'phone'                 => ['required', 'string', 'min:8', 'max:20', Rule::unique('users', 'phone')],
            'password'              => 'required|string|min:8|confirmed|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
            'g-recaptcha-response'  => 'sometimes|nullable|string',
            'captcha'               => 'sometimes|nullable|string',
            'terms'                 => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex'         => 'Password harus ada huruf besar, kecil, dan angka.',
            'phone.unique'           => 'No HP udah kepake.',
            'email.unique'           => 'Email udah terdaftar.',
            'terms.accepted'         => 'Loe harus setuju dengan Terms of Service.',
        ];
    }
}
