@extends('publik.layouts.app')

@section('judul', 'Halaman Tidak Ditemukan')

@section('konten')
<div class="mx-auto flex max-w-xl flex-col items-center px-4 py-28 text-center sm:px-6">
    <p class="text-6xl font-semibold text-slate-200">404</p>
    <h1 class="mt-4 text-2xl font-semibold text-slate-900">Halaman tidak ditemukan</h1>
    <p class="mt-2 text-slate-600">
        Alamat yang Anda buka mungkin sudah berubah, atau halamannya memang tidak ada.
    </p>
    <div class="mt-8 flex flex-wrap justify-center gap-3">
        <a href="{{ route('beranda') }}"
           class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
            Kembali ke Beranda
        </a>
        <a href="{{ route('publik.berita.index') }}"
           class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Lihat Berita Desa
        </a>
    </div>
</div>
@endsection
