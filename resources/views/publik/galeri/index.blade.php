@extends('publik.layouts.app')

@section('judul', 'Galeri Kegiatan')
@section('deskripsi', 'Dokumentasi kegiatan dan acara yang berlangsung di ' . $desa->nama_desa . '.')

@section('konten')
<div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">

    <nav class="mb-6 text-sm text-slate-500">
        <a href="{{ route('beranda') }}" class="hover:text-emerald-700">Beranda</a>
        <span class="mx-1.5">/</span>
        <span class="text-slate-700">Galeri</span>
    </nav>

    <header class="mb-8 max-w-2xl">
        <h1 class="text-3xl font-semibold text-slate-900">Galeri Kegiatan</h1>
        <p class="mt-2 text-slate-600">
            Dokumentasi kegiatan dan acara yang berlangsung di {{ $desa->nama_desa }}.
        </p>
    </header>

    @if ($daftar->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 px-6 py-20 text-center">
            <p class="font-medium text-slate-900">Belum ada dokumentasi kegiatan</p>
            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                Foto kegiatan desa akan ditampilkan di sini setelah diunggah aparat desa.
            </p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($daftar as $acara)
                <a href="{{ route('publik.galeri.show', $acara) }}"
                   class="group overflow-hidden rounded-2xl border border-slate-200 transition hover:border-slate-300 hover:shadow-md">
                    <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                        <img src="{{ $acara->urlSampulKecil() }}" alt="{{ $acara->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @if ($acara->foto_count > 1)
                            <span class="absolute right-3 bottom-3 rounded-full bg-slate-900/70 px-2.5 py-1 text-xs font-medium text-white">
                                {{ $acara->foto_count }} foto
                            </span>
                        @endif
                    </div>
                    <div class="p-5">
                        <h2 class="font-semibold text-slate-900 group-hover:text-emerald-700">{{ $acara->judul }}</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $acara->tanggal_acara->translatedFormat('d F Y') }}
                            @if (filled($acara->lokasi))
                                · {{ $acara->lokasi }}
                            @endif
                        </p>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($daftar->hasPages())
            <div class="mt-10">{{ $daftar->links() }}</div>
        @endif
    @endif
</div>
@endsection
