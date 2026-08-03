<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'kata_sandi_lama' => ['required', 'string', 'current_password:admin'],
            'kata_sandi_baru' => [
                'required',
                'string',
                'confirmed',
                Password::min(8),
                // Fitur 01: kata sandi baru wajib berbeda dari yang lama.
                Rule::notIn([$this->input('kata_sandi_lama')]),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'kata_sandi_lama' => 'kata sandi lama',
            'kata_sandi_baru' => 'kata sandi baru',
        ];
    }

    public function messages(): array
    {
        return [
            'kata_sandi_lama.current_password' => 'Kata sandi lama tidak sesuai.',
            'kata_sandi_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'kata_sandi_baru.not_in' => 'Kata sandi baru harus berbeda dari kata sandi lama.',
        ];
    }
}
