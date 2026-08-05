<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ResetSandiAdminRequest extends FormRequest {
    public function authorize(): bool {
        return auth('admin')->check() && auth('admin')->user()->is_super_admin;
    }

    public function rules(): array {
        return [
            'password' => 'required|min:8',
        ];
    }

    public function messages(): array {
        return [
            'password.required' => 'Sandi wajib diisi.',
            'password.min' => 'Sandi minimal 8 karakter.',
        ];
    }
}
