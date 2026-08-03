<?php

namespace App\Http\Requests\Admin;

use App\Enums\AlasanKeluar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Fitur 04 — pengarsipan warga.
 *
 * Alasan keluar diwajibkan, bukan opsional. Tanpa itu statistik kematian
 * ikut menghitung warga yang sebenarnya pindah atau datanya ganda.
 */
class ArsipkanPendudukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'alasan_keluar' => ['required', Rule::enum(AlasanKeluar::class)],
            'tanggal_keluar' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:'.$this->route('penduduk')->tanggal_masuk->toDateString(),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'alasan_keluar' => 'alasan keluar',
            'tanggal_keluar' => 'tanggal keluar',
        ];
    }

    public function messages(): array
    {
        return [
            'alasan_keluar.required' => 'Pilih alasan warga keluar dari daftar aktif.',
            'tanggal_keluar.before_or_equal' => 'Tanggal keluar tidak boleh melewati hari ini.',
            'tanggal_keluar.after_or_equal' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk warga.',
        ];
    }
}
