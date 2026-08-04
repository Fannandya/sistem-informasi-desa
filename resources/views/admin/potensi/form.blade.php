@extends('admin.layouts.app')

@php $ubah = $potensi->exists; @endphp

@section('judul', $ubah ? 'Ubah Potensi' : 'Tambah Potensi')

@section('konten')
<div class="max-w-2xl">

    <nav class="mb-5 text-sm">
        <a href="{{ route('admin.potensi.index') }}" class="text-slate-500 hover:text-slate-800">
            &larr; Kembali ke Potensi Desa
        </a>
    </nav>

    <form method="POST"
          action="{{ $ubah ? route('admin.potensi.update', $potensi) : route('admin.potensi.store') }}"
          enctype="multipart/form-data"
          data-lacak-perubahan
          x-data="{ singkat: @js(old('deskripsi_singkat', $potensi->deskripsi_singkat ?? '')) }"
          class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf
        @if ($ubah) @method('PUT') @endif

        <x-admin.field name="nama" label="Nama Potensi" :value="$potensi->nama" required
                       placeholder="mis. Keripik Singkong Bu Sari" />

        <x-admin.select name="kategori" label="Kategori" required :value="$potensi->kategori?->value"
                        :pilihan="\App\Enums\KategoriPotensi::pilihan()" />

        <div>
            <label for="deskripsi_singkat" class="mb-1.5 block text-sm font-medium text-slate-700">
                Deskripsi Singkat
            </label>
            <textarea id="deskripsi_singkat" name="deskripsi_singkat" rows="2" maxlength="255" x-model="singkat"
                      class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm
                             focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"></textarea>
            <div class="mt-1.5 flex items-start justify-between gap-4">
                <p class="text-xs text-slate-500">Tampil pada kartu katalog dan hasil pencarian Google.</p>
                <p class="shrink-0 text-xs tabular-nums text-slate-500" x-text="`${singkat.length} / 255`"></p>
            </div>
            @error('deskripsi_singkat')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <x-admin.textarea name="deskripsi" label="Deskripsi Lengkap" rows="8" :value="$potensi->deskripsi" />

        <x-admin.field name="kontak" label="Kontak Usaha" :value="$potensi->kontak"
                       keterangan="Nomor atau nama yang bisa dihubungi pembeli. Bukan data pribadi warga."
                       placeholder="mis. 0812xxxxxxx (Bu Sari)" />

        <x-admin.gambar name="foto" label="Foto Potensi" :path="$potensi->foto" jenis="potensi" />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field name="urutan" label="Urutan Tampil" type="number" :value="$potensi->urutan ?? 0"
                           keterangan="Semakin kecil, semakin awal." />
            <div class="flex items-end">
                <label class="flex items-center gap-2 pb-2.5 text-sm text-slate-700">
                    <input type="checkbox" name="tampil" value="1"
                           @checked(old('tampil', $potensi->tampil ?? true))
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    Tampilkan di halaman publik
                </label>
            </div>
        </div>

        @if ($ubah)
            <details class="rounded-lg border border-slate-200 p-4">
                <summary class="cursor-pointer text-sm font-medium text-slate-700">Alamat URL halaman</summary>
                <div class="mt-4">
                    <x-admin.field name="slug" label="Slug" :value="$potensi->slug" />
                    <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        Alamat ini <strong>tidak ikut berubah</strong> saat nama potensi diubah, supaya tautan
                        yang sudah tersebar tetap hidup. Mengubahnya di sini akan <strong>mematikan tautan lama</strong>.
                    </div>
                </div>
            </details>
        @endif

        <x-admin.simpan :label="$ubah ? 'Simpan Perubahan' : 'Simpan Potensi'">
            <a href="{{ route('admin.potensi.index') }}" class="text-sm text-slate-500 hover:text-slate-800">Batal</a>
        </x-admin.simpan>
    </form>
</div>
@endsection
