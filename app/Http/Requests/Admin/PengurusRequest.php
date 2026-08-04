<?php

namespace App\Http\Requests\Admin;

use App\Services\LayananUnggahGambar;
use Illuminate\Foundation\Http\FormRequest;

class PengurusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:120'],
            'jabatan' => ['required', 'string', 'max:80'],
            'organisasi_id' => ['required', 'exists:ref_organisasi,id'],
            'wilayah_id' => ['nullable', 'exists:wilayah,id'],
            'nomor_hp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-() ]+$/'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
            'tampil' => ['nullable', 'boolean'],
            'foto' => LayananUnggahGambar::aturanValidasi(),
            'hapus_foto' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'organisasi_id' => 'organisasi',
            'wilayah_id' => 'wilayah',
            'nomor_hp' => 'nomor HP',
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_hp.regex' => 'Nomor HP hanya boleh berisi angka, spasi, dan tanda + - ( ).',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dataBersih(): array
    {
        $data = collect($this->validated())
            ->except(['foto', 'hapus_foto'])
            ->all();

        $data['tampil'] = $this->boolean('tampil');
        $data['urutan'] = $data['urutan'] ?? 0;

        return $data;
    }
}
