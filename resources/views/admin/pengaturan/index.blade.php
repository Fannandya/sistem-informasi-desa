@extends('admin.layouts.app')

@section('judul', 'Pengaturan Desa')

@php
    $bagianAktif = request('bagian', 'identitas');

    // Bila validasi gagal, buka tab tempat kesalahannya berada agar admin
    // tidak melihat halaman yang seolah tidak bereaksi.
    $petaKesalahan = [
        'identitas' => ['nama_desa', 'kecamatan', 'kabupaten', 'provinsi', 'kode_pos', 'tahun_berdiri', 'luas_desa', 'logo', 'foto_sampul'],
        'profil' => ['visi', 'misi', 'nilai_tujuan', 'sejarah'],
        'geografis' => ['letak_geografis', 'peta_embed'],
        'kontak' => ['alamat_kantor', 'telepon_kantor', 'email', 'website', 'medsos'],
        'seo' => ['meta_description'],
    ];

    foreach ($petaKesalahan as $nama => $kolom) {
        if ($errors->hasAny($kolom) || $errors->hasAny(array_map(fn ($k) => $k . '.*', $kolom))) {
            $bagianAktif = $nama;
            break;
        }
    }

    $tab = [
        'identitas' => 'Identitas Desa',
        'profil' => 'Visi, Misi & Sejarah',
        'geografis' => 'Letak & Peta',
        'kontak' => 'Kontak & Media Sosial',
        'seo' => 'SEO',
    ];
@endphp

@section('konten')
<div x-data="{ bagian: @js($bagianAktif) }" class="max-w-3xl">

    {{-- Navigasi bagian --}}
    <div class="mb-6 overflow-x-auto">
        <nav class="flex gap-1 border-b border-slate-200" role="tablist">
            @foreach ($tab as $kunci => $label)
                <button type="button" role="tab" @click="bagian = @js($kunci)"
                        class="shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition"
                        :class="bagian === @js($kunci)
                            ? 'border-emerald-600 text-emerald-700'
                            : 'border-transparent text-slate-500 hover:text-slate-800'">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Ringkasan kesalahan sudah ditampilkan x-admin.notifikasi di layout. --}}

    {{-- ============================ IDENTITAS ============================ --}}
    <section x-show="bagian === 'identitas'" x-cloak
             class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <form method="POST" action="{{ route('admin.pengaturan.perbarui', 'identitas') }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <x-admin.field name="nama_desa" label="Nama Desa" :value="$pengaturan->nama_desa" required />

            <div class="grid gap-5 sm:grid-cols-3">
                <x-admin.field name="kecamatan" label="Kecamatan" :value="$pengaturan->kecamatan" />
                <x-admin.field name="kabupaten" label="Kabupaten" :value="$pengaturan->kabupaten" />
                <x-admin.field name="provinsi" label="Provinsi" :value="$pengaturan->provinsi" />
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-admin.field name="kode_pos" label="Kode Pos" :value="$pengaturan->kode_pos" />
                <x-admin.field name="tahun_berdiri" label="Tahun Berdiri" type="number"
                               :value="$pengaturan->tahun_berdiri" :max="now()->year" />
                <x-admin.field name="luas_desa" label="Luas Desa (hektare)" type="number" step="0.01"
                               :value="$pengaturan->luas_desa" />
            </div>

            <x-admin.gambar name="logo" label="Logo Desa" :path="$pengaturan->logo"
                            keterangan="Tampil di kop halaman dan panel admin. Sebaiknya berbentuk persegi." />

            <x-admin.gambar name="foto_sampul" label="Gambar Sampul" :path="$pengaturan->foto_sampul"
                            keterangan="Gambar besar di bagian paling atas halaman muka. Sebaiknya melebar (landscape)." />

            <x-admin.simpan />
        </form>
    </section>

    {{-- ============================= PROFIL ============================== --}}
    <section x-show="bagian === 'profil'" x-cloak
             class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <form method="POST" action="{{ route('admin.pengaturan.perbarui', 'profil') }}" data-lacak-perubahan class="space-y-5">
            @csrf @method('PUT')

            <x-admin.textarea name="visi" label="Visi" rows="3" :value="$pengaturan->visi" />

            <x-admin.textarea name="misi" label="Misi" rows="6" :value="$pengaturan->misi"
                              keterangan="Tulis satu misi per baris. Di halaman publik akan tampil sebagai daftar bernomor — tidak perlu menulis angka sendiri." />

            <x-admin.textarea name="nilai_tujuan" label="Nilai / Tujuan Pembangunan" rows="3"
                              :value="$pengaturan->nilai_tujuan" />

            <x-admin.textarea name="sejarah" label="Sejarah Desa" rows="8" :value="$pengaturan->sejarah" />

            <x-admin.simpan />
        </form>
    </section>

    {{-- =========================== GEOGRAFIS ============================= --}}
    <section x-show="bagian === 'geografis'" x-cloak
             class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <form method="POST" action="{{ route('admin.pengaturan.perbarui', 'geografis') }}" data-lacak-perubahan class="space-y-5">
            @csrf @method('PUT')

            <x-admin.textarea name="letak_geografis" label="Letak Geografis & Batas Wilayah" rows="6"
                              :value="$pengaturan->letak_geografis" />

            <x-admin.textarea name="peta_embed" label="Kode Peta Google Maps" rows="4"
                              :value="$pengaturan->peta_embed"
                              keterangan="Buka Google Maps → cari lokasi desa → Bagikan → Sematkan peta → Salin HTML. Tempelkan seluruh kodenya di sini; sistem akan mengambil alamat petanya saja." />

            @if (filled($pengaturan->peta_embed))
                <div>
                    <p class="mb-2 text-sm font-medium text-slate-700">Pratinjau peta tersimpan</p>
                    <div class="overflow-hidden rounded-lg ring-1 ring-slate-200">
                        <iframe src="{{ $pengaturan->peta_embed }}" class="h-64 w-full" style="border:0"
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                                title="Peta lokasi {{ $pengaturan->nama_desa }}"></iframe>
                    </div>
                </div>
            @endif

            <x-admin.simpan />
        </form>
    </section>

    {{-- ============================= KONTAK ============================== --}}
    <section x-show="bagian === 'kontak'" x-cloak
             class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <form method="POST" action="{{ route('admin.pengaturan.perbarui', 'kontak') }}" data-lacak-perubahan class="space-y-5">
            @csrf @method('PUT')

            <x-admin.field name="alamat_kantor" label="Alamat Kantor Desa" :value="$pengaturan->alamat_kantor" />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.field name="telepon_kantor" label="Telepon Kantor" :value="$pengaturan->telepon_kantor" />
                <x-admin.field name="email" label="Email Resmi" type="email" :value="$pengaturan->email" />
            </div>

            <x-admin.field name="website" label="Website" type="url" :value="$pengaturan->website"
                           placeholder="https://namadesa.desa.id" />

            <div class="border-t border-slate-200 pt-5">
                <p class="mb-4 text-sm font-medium text-slate-700">Media Sosial</p>
                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'] as $kunci => $label)
                        <x-admin.field :name="'medsos[' . $kunci . ']'" :label="$label" type="url"
                                       :value="$pengaturan->medsos[$kunci] ?? null"
                                       :error="'medsos.' . $kunci"
                                       :placeholder="'https://' . $kunci . '.com/namadesa'" />
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-slate-500">
                    Tautan yang dikosongkan tidak akan menampilkan ikon apa pun di halaman publik.
                </p>
            </div>

            <x-admin.simpan />
        </form>
    </section>

    {{-- =============================== SEO =============================== --}}
    <section x-show="bagian === 'seo'" x-cloak
             class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <form method="POST" action="{{ route('admin.pengaturan.perbarui', 'seo') }}" data-lacak-perubahan class="space-y-5"
              x-data="{ teks: @js(old('meta_description', $pengaturan->meta_description ?? '')) }">
            @csrf @method('PUT')

            <div>
                <label for="meta_description" class="mb-1.5 block text-sm font-medium text-slate-700">
                    Deskripsi Ringkas Desa
                </label>
                <textarea id="meta_description" name="meta_description" rows="3" maxlength="255"
                          x-model="teks"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm
                                 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"></textarea>

                <div class="mt-1.5 flex items-start justify-between gap-4">
                    <p class="text-xs text-slate-500">
                        Muncul di hasil pencarian Google dan saat tautan desa dibagikan.
                        Panjang ideal sekitar 150 karakter.
                    </p>
                    <p class="shrink-0 text-xs tabular-nums"
                       :class="teks.length > 160 ? 'text-amber-600' : 'text-slate-500'"
                       x-text="`${teks.length} / 255`"></p>
                </div>

                @error('meta_description')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <p class="rounded-lg bg-slate-50 px-4 py-3 text-xs text-slate-600">
                Bila dikosongkan, sistem otomatis memakai potongan sejarah atau visi desa —
                bukan teks bawaan yang sama di semua halaman.
            </p>

            <x-admin.simpan />
        </form>
    </section>

</div>
@endsection
