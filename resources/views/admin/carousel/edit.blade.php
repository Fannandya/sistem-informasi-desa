@extends('admin.layouts.app')

@section('judul', 'Ubah Gambar Carousel')

@section('konten')
<div class="max-w-2xl">

    <nav class="mb-5 text-sm">
        <a href="{{ route('admin.carousel.index') }}" class="text-on-surface-variant hover:text-on-surface">
            &larr; Kembali ke Carousel Beranda
        </a>
    </nav>

    <form method="POST" action="{{ route('admin.carousel.update', $carouselImage) }}"
          enctype="multipart/form-data"
          data-lacak-perubahan
          class="space-y-5 rounded-2xl bg-surface p-6 shadow-sm ring-1 ring-outline">
        @csrf
        @method('PUT')

        <x-admin.gambar name="gambar" label="Gambar Carousel (opsional)"
                        keterangan="Format: JPG, PNG, WebP. Ukuran maksimal: 4 MB. Biarkan kosong jika tidak ingin mengubah."
                        :value="$carouselImage->path" />

        <x-admin.field name="judul" label="Judul (opsional)"
                       :value="$carouselImage->judul"
                       placeholder="Judul atau caption untuk gambar" />

        <x-admin.textarea name="deskripsi" label="Deskripsi (opsional)"
                         rows="3"
                         :value="$carouselImage->deskripsi"
                         placeholder="Deskripsi singkat tentang gambar" />

        <div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="tampil" value="1"
                       {{ $carouselImage->tampil ? 'checked' : '' }}
                       class="rounded border-outline bg-on-surface/5 text-primary">
                <span class="text-sm font-medium text-on-surface">
                    Tampilkan di beranda
                </span>
            </label>
        </div>

        <x-admin.simpan route="admin.carousel.index" />
    </form>

</div>
@endsection
