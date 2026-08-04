<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi dinamis: aturan dibangun dari config('teks-tampilan') sesuai
 * grup yang dikirim di URL. Nama field HTTP memakai kunci termanggl
 * (titik -> __) karena Laravel memperlakukan titik pada nama field sebagai
 * pemisah array bersarang — lihat Global Constraints di plan ini.
 */
class UpdateTeksTampilanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function grup(): string
    {
        return (string) $this->route('grup');
    }

    protected function prepareForValidation(): void
    {
        $nilai = collect($this->input('nilai', []))
            ->map(fn ($v) => $v === '' ? null : $v)
            ->all();

        $this->merge(['nilai' => $nilai]);
    }

    public function rules(): array
    {
        $aturan = [];

        foreach ($this->kunciGrupIni() as $kunci => $meta) {
            $termanggl = str_replace('.', '__', $kunci);

            $aturan["nilai.{$termanggl}"] = $meta['tipe'] === 'angka'
                ? ['nullable', 'integer', 'min:'.$meta['min'], 'max:'.$meta['max']]
                : ['nullable', 'string', 'max:'.$meta['maks']];
        }

        return $aturan;
    }

    public function attributes(): array
    {
        $atribut = [];

        foreach ($this->kunciGrupIni() as $kunci => $meta) {
            $atribut['nilai.'.str_replace('.', '__', $kunci)] = $meta['label'];
        }

        return $atribut;
    }

    /**
     * Nilai tervalidasi, dipetakan kembali dari kunci termanggl ke kunci
     * asli bertitik supaya controller bisa langsung menyimpannya.
     *
     * @return array<string, string|null>
     */
    public function nilaiUntukDisimpan(): array
    {
        $termanggl = collect($this->validated('nilai', []));

        return collect($this->kunciGrupIni())
            ->keys()
            ->mapWithKeys(fn (string $kunci) => [
                $kunci => $termanggl->get(str_replace('.', '__', $kunci)),
            ])
            ->all();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function kunciGrupIni(): array
    {
        return collect(config('teks-tampilan'))
            ->filter(fn (array $meta) => $meta['grup'] === $this->grup())
            ->all();
    }
}
