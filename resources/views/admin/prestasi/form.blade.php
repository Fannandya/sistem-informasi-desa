@extends('admin.layouts.app')

@php $ubah = $prestasi->exists; @endphp

@section('judul', $ubah ? 'Ubah Prestasi' : 'Tambah Prestasi')

@section('konten')
<div class="max-w-2xl">
    <nav class="mb-5 text-sm">
        <a href="{{ route('admin.prestasi.index') }}" class="text-slate-500 hover:text-slate-800">
            &larr; Kembali ke Prestasi Desa
        </a>
    </nav>

    <form method="POST"
          action="{{ $ubah ? route('admin.prestasi.update', $prestasi) : route('admin.prestasi.store') }}"
          enctype="multipart/form-data"
          class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf
        @if ($ubah) @method('PUT') @endif

        <x-admin.field name="judul" label="Judul Prestasi" :value="$prestasi->judul" required
                       placeholder="mis. Juara 1 Lomba Desa Bersih" />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field name="tahun" label="Tahun Diraih" type="number" required
                           :value="$prestasi->tahun" :max="now()->year" placeholder="{{ now()->year }}" />

            <x-admin.select name="tingkat" label="Tingkat" :value="$prestasi->tingkat?->value"
                            :pilihan="\App\Enums\TingkatPrestasi::pilihan()" kosong="— Tidak ditentukan —" />
        </div>

        <x-admin.field name="penyelenggara" label="Penyelenggara" :value="$prestasi->penyelenggara"
                       placeholder="mis. Pemerintah Kabupaten Bandung Barat" />

        <x-admin.textarea name="deskripsi" label="Deskripsi" rows="5" :value="$prestasi->deskripsi" />

        <x-admin.gambar name="foto" label="Foto Piagam / Dokumentasi" :path="$prestasi->foto" jenis="prestasi"
                        keterangan="Foto piagam, plakat, atau momen penyerahan penghargaan." />

        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="tampil" value="1" @checked(old('tampil', $prestasi->tampil ?? true))
                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            Tampilkan di halaman publik
        </label>

        <x-admin.simpan :label="$ubah ? 'Simpan Perubahan' : 'Simpan Prestasi'">
            <a href="{{ route('admin.prestasi.index') }}" class="text-sm text-slate-500 hover:text-slate-800">Batal</a>
        </x-admin.simpan>
    </form>
</div>
@endsection
