<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest {
    public function authorize(): bool {
        return auth('admin')->check() && auth('admin')->user()->is_super_admin;
    }

    public function rules(): array {
        $adminId = $this->admin?->id;
        return [
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:admins,email,' . $adminId,
            'password' => 'required_if:method,POST|nullable|min:8',
            'is_super_admin' => 'boolean',
        ];
    }

    public function messages(): array {
        return [
            'nama.required' => 'Nama admin wajib diisi.',
            'nama.max' => 'Nama admin maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required_if' => 'Sandi wajib diisi saat membuat admin baru.',
            'password.min' => 'Sandi minimal 8 karakter.',
        ];
    }
}
