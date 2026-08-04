@extends('admin.layouts.app')

@php $ubah = $fasilitas->exists; @endphp

@section('judul', $ubah ? 'Ubah Fasilitas' : 'Tambah Fasilitas')

@section('konten')
<div class="max-w-2xl">
    <nav class="mb-5 text-sm">
        <a href="{{ route('admin.fasilitas.index') }}" class="text-slate-500 hover:text-slate-800">
            &larr; Kembali ke Fasilitas Umum
        </a>
    </nav>

    <form method="POST"
          action="{{ $ubah ? route('admin.fasilitas.update', $fasilitas) : route('admin.fasilitas.store') }}"
          enctype="multipart/form-data" data-lacak-perubahan
          class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf
        @if ($ubah) @method('PUT') @endif

        <x-admin.field name="nama" label="Nama Fasilitas" :value="$fasilitas->nama" required
                       placeholder="mis. SDN Sukamaju 01" />

        <x-admin.select name="kategori_fasilitas_id" label="Kategori" required :value="$fasilitas->kategori_fasilitas_id"
                        :pilihan="\App\Models\RefKategoriFasilitas::urut()->pluck('nama', 'id')" />

        <x-admin.textarea name="deskripsi" label="Deskripsi" rows="5" :value="$fasilitas->deskripsi" />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field name="alamat" label="Alamat" :value="$fasilitas->alamat" />
            <x-admin.select name="wilayah_id" label="Wilayah" :value="$fasilitas->wilayah_id"
                            :pilihan="$wilayahList->mapWithKeys(fn ($w) => [$w->id => $w->label()])"
                            kosong="— Tidak ditentukan —" />
        </div>

        <x-admin.gambar name="foto" label="Foto Fasilitas" :path="$fasilitas->foto" jenis="fasilitas" />

        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="tampil" value="1" @checked(old('tampil', $fasilitas->tampil ?? true))
                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            Tampilkan di halaman publik
        </label>

        <x-admin.simpan :label="$ubah ? 'Simpan Perubahan' : 'Simpan Fasilitas'">
            <a href="{{ route('admin.fasilitas.index') }}" class="text-sm text-slate-500 hover:text-slate-800">Batal</a>
        </x-admin.simpan>
    </form>
</div>
@endsection
