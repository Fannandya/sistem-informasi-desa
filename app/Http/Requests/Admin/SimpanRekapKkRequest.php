<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SimpanRekapKkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function rules(): array
    {
        return [
            'jumlah' => ['array'],
            // Nullable disengaja: isian yang dikosongkan berarti "belum
            // diisi", dan itu berbeda dari nol yang berarti "sudah diperiksa,
            // memang tidak ada KK".
            'jumlah.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'jumlah.*.integer' => 'Jumlah KK harus berupa angka bulat.',
            'jumlah.*.min' => 'Jumlah KK tidak boleh negatif.',
            'jumlah.*.max' => 'Jumlah KK terlalu besar. Periksa kembali angkanya.',
        ];
    }

    public function attributes(): array
    {
        return collect($this->input('jumlah', []))
            ->keys()
            ->mapWithKeys(fn ($id) => ["jumlah.{$id}" => 'jumlah KK'])
            ->all();
    }

    /**
     * @return array<int, int|null> wilayah_id => jumlah KK (null = dikosongkan)
     */
    public function isian(): array
    {
        return collect($this->validated()['jumlah'] ?? [])
            ->mapWithKeys(fn ($nilai, $id) => [
                (int) $id => $nilai === null || $nilai === '' ? null : (int) $nilai,
            ])
            ->all();
    }
}
