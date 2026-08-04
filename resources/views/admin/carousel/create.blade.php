@extends('admin.layouts.app')

@section('judul', 'Tambah Gambar Carousel')

@section('konten')
<div class="max-w-2xl">

    <nav class="mb-5 text-sm">
        <a href="{{ route('admin.carousel.index') }}" class="text-on-surface-variant hover:text-on-surface">
            &larr; Kembali ke Carousel Beranda
        </a>
    </nav>

    <form method="POST" action="{{ route('admin.carousel.store') }}"
          enctype="multipart/form-data"
          data-lacak-perubahan
          class="space-y-5 rounded-2xl bg-surface p-6 shadow-sm ring-1 ring-outline">
        @csrf

        <x-admin.gambar name="gambar" label="Gambar Carousel" required
                        keterangan="Format: JPG, PNG, WebP. Ukuran maksimal: 4 MB" />

        <x-admin.field name="judul" label="Judul (opsional)"
                       placeholder="Judul atau caption untuk gambar" />

        <x-admin.textarea name="deskripsi" label="Deskripsi (opsional)"
                         rows="3"
                         placeholder="Deskripsi singkat tentang gambar" />

        <x-admin.simpan route="admin.carousel.index" />
    </form>

</div>
@endsection
