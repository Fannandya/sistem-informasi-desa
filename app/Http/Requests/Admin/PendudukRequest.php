<?php

namespace App\Http\Requests\Admin;

use App\Enums\JenisKelamin;
use App\Enums\JenisMasuk;
use App\Enums\Pendidikan;
use App\Enums\StatusPerkawinan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PendudukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:120'],

            // Wajib di formulir manual meski kolomnya nullable di database —
            // yang nullable hanya untuk menampung data hasil impor (PRD 2.4).
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],

            'jenis_kelamin' => ['required', Rule::enum(JenisKelamin::class)],
            'wilayah_id' => ['required', 'exists:wilayah,id'],
            'pekerjaan_id' => ['nullable', 'exists:ref_pekerjaan,id'],
            'agama_id' => ['nullable', 'exists:ref_agama,id'],
            'pendidikan' => ['nullable', Rule::enum(Pendidikan::class)],
            'status_perkawinan' => ['nullable', Rule::enum(StatusPerkawinan::class)],

            'jenis_masuk' => ['required', Rule::enum(JenisMasuk::class)],
            'tanggal_masuk' => [
                'required',
                'date',
                'before_or_equal:today',
                // Seseorang tidak bisa tercatat masuk sebelum ia lahir.
                'after_or_equal:tanggal_lahir',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'nama lengkap',
            'tanggal_lahir' => 'tanggal lahir',
            'jenis_kelamin' => 'jenis kelamin',
            'wilayah_id' => 'wilayah',
            'pekerjaan_id' => 'pekerjaan',
            'agama_id' => 'agama',
            'status_perkawinan' => 'status perkawinan',
            'jenis_masuk' => 'cara masuk',
            'tanggal_masuk' => 'tanggal masuk',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melewati hari ini.',
            'tanggal_masuk.before_or_equal' => 'Tanggal masuk tidak boleh melewati hari ini.',
            'tanggal_masuk.after_or_equal' => 'Tanggal masuk tidak boleh lebih awal dari tanggal lahir.',
            'wilayah_id.required' => 'Pilih wilayah tempat warga tinggal.',
            'wilayah_id.exists' => 'Wilayah yang dipilih tidak terdaftar.',
        ];
    }
}
