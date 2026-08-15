<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:120'],
            'password' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'email',
            'password' => 'kata sandi',
        ];
    }

    /**
     * Kunci pembatas percobaan login: gabungan email + alamat IP,
     * sesuai fitur 01 sub-fitur "Batas Percobaan Login".
     */
    public function kunciPembatas(): string
    {
        return 'login:'.mb_strtolower((string) $this->input('email')).'|'.$this->ip();
    }
}
