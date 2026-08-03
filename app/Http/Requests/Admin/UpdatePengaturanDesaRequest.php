<?php

namespace App\Http\Requests\Admin;

use App\Rules\EmbedPetaValid;
use App\Services\LayananUnggahGambar;
use App\Support\EmbedPeta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Fitur 03 — validasi pengaturan desa per bagian.
 *
 * Halaman pengaturan terbagi menjadi lima bagian dengan tombol simpan
 * masing-masing, sehingga admin bisa menyimpan satu bagian tanpa menyentuh
 * bagian lain. Aturan validasi dipilih berdasarkan bagian yang dikirim.
 */
class UpdatePengaturanDesaRequest extends FormRequest
{
    public const BAGIAN = ['identitas', 'profil', 'geografis', 'kontak', 'seo'];

    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    public function bagian(): string
    {
        return (string) $this->route('bagian');
    }

    protected function prepareForValidation(): void
    {
        if ($this->bagian() !== 'geografis') {
            return;
        }

        // Admin biasanya menempelkan seluruh tag <iframe>. Yang disimpan hanya
        // URL src-nya. Bila ekstraksi gagal, masukan asli dibiarkan apa adanya
        // agar aturan validasi bisa menolaknya dengan pesan yang jelas.
        if (filled($this->input('peta_embed'))) {
            $this->merge([
                'peta_embed' => EmbedPeta::ekstrakUrl($this->input('peta_embed'))
                    ?? $this->input('peta_embed'),
            ]);
        }
    }

    public function rules(): array
    {
        return match ($this->bagian()) {
            'identitas' => [
                'nama_desa' => ['required', 'string', 'max:80'],
                'kecamatan' => ['nullable', 'string', 'max:80'],
                'kabupaten' => ['nullable', 'string', 'max:80'],
                'provinsi' => ['nullable', 'string', 'max:80'],
                'kode_pos' => ['nullable', 'string', 'max:10'],
                'tahun_berdiri' => ['nullable', 'integer', 'min:1000', 'max:'.now()->year],
                'luas_desa' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
                'logo' => LayananUnggahGambar::aturanValidasi(),
                'foto_sampul' => LayananUnggahGambar::aturanValidasi(),
                'hapus_logo' => ['nullable', 'boolean'],
                'hapus_foto_sampul' => ['nullable', 'boolean'],
            ],

            'profil' => [
                'visi' => ['nullable', 'string', 'max:2000'],
                'misi' => ['nullable', 'string', 'max:5000'],
                'nilai_tujuan' => ['nullable', 'string', 'max:5000'],
                'sejarah' => ['nullable', 'string', 'max:20000'],
            ],

            'geografis' => [
                'letak_geografis' => ['nullable', 'string', 'max:5000'],
                'peta_embed' => ['nullable', 'string', 'max:2000', new EmbedPetaValid()],
            ],

            'kontak' => [
                'alamat_kantor' => ['nullable', 'string', 'max:255'],
                'telepon_kantor' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-() ]+$/'],
                'email' => ['nullable', 'email', 'max:120'],
                'website' => ['nullable', 'url', 'max:120'],
                'medsos' => ['nullable', 'array'],
                'medsos.*' => ['nullable', 'url', 'max:255'],
            ],

            'seo' => [
                'meta_description' => ['nullable', 'string', 'max:255'],
            ],

            default => [],
        };
    }

    public function attributes(): array
    {
        return [
            'nama_desa' => 'nama desa',
            'tahun_berdiri' => 'tahun berdiri',
            'luas_desa' => 'luas desa',
            'kode_pos' => 'kode pos',
            'nilai_tujuan' => 'nilai / tujuan pembangunan',
            'letak_geografis' => 'letak geografis',
            'peta_embed' => 'kode peta',
            'alamat_kantor' => 'alamat kantor',
            'telepon_kantor' => 'telepon kantor',
            'meta_description' => 'deskripsi ringkas',
            'medsos.facebook' => 'tautan Facebook',
            'medsos.instagram' => 'tautan Instagram',
            'medsos.youtube' => 'tautan YouTube',
            'medsos.tiktok' => 'tautan TikTok',
        ];
    }

    public function messages(): array
    {
        return [
            'telepon_kantor.regex' => 'Telepon kantor hanya boleh berisi angka, spasi, dan tanda + - ( ).',
            'tahun_berdiri.max' => 'Tahun berdiri tidak boleh melebihi tahun ini.',
        ];
    }

    /**
     * Aturan yang boleh dilewatkan ke model, tanpa kolom bantu formulir
     * dan tanpa berkas unggahan yang ditangani terpisah.
     *
     * @return array<string, mixed>
     */
    public function dataBersih(): array
    {
        return collect($this->validated())
            ->except(['logo', 'foto_sampul', 'hapus_logo', 'hapus_foto_sampul'])
            ->all();
    }
}
