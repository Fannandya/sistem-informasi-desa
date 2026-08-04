@extends('publik.layouts.app')

@section('judul', $kategori ? 'Berita — ' . $kategori->nama : 'Berita Desa')
@section('deskripsi', 'Berita, pengumuman, dan informasi terbaru dari ' . $desa->nama_desa . '.')

@section('konten')
<div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">

    <nav class="mb-6 text-sm text-slate-500">
        <a href="{{ route('beranda') }}" class="hover:text-emerald-700">Beranda</a>
        <span class="mx-1.5">/</span>
        @if ($kategori)
            <a href="{{ route('publik.berita.index') }}" class="hover:text-emerald-700">Berita</a>
            <span class="mx-1.5">/</span>
            <span class="text-slate-700">{{ $kategori->nama }}</span>
        @else
            <span class="text-slate-700">Berita</span>
        @endif
    </nav>

    <header class="mb-8 max-w-2xl">
        <h1 class="text-3xl font-semibold text-slate-900">
            {{ $kategori ? $kategori->nama : 'Berita Desa' }}
        </h1>
        <p class="mt-2 text-slate-600">
            Kabar, pengumuman, dan informasi terbaru dari {{ $desa->nama_desa }}.
        </p>
    </header>

    @if ($kategoriList->isNotEmpty())
        <div class="mb-8 flex flex-wrap gap-2">
            <a href="{{ route('publik.berita.index') }}"
               class="rounded-full px-4 py-2 text-sm font-medium transition
                      {{ $kategori === null ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Semua
            </a>
            @foreach ($kategoriList as $k)
                <a href="{{ route('publik.berita.kategori', $k) }}"
                   class="rounded-full px-4 py-2 text-sm font-medium transition
                          {{ $kategori?->id === $k->id ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    {{ $k->nama }}
                    <span class="ml-1 opacity-60">{{ $k->berita_count }}</span>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Berita utama --}}
    @if ($utama)
        <a href="{{ route('publik.berita.show', $utama) }}"
           class="group mb-10 grid overflow-hidden rounded-2xl border border-slate-200 transition hover:border-slate-300 hover:shadow-md md:grid-cols-2">
            <div class="aspect-[16/10] overflow-hidden bg-slate-100 md:aspect-auto">
                <img src="{{ $utama->urlSampul() }}" alt="{{ $utama->judul }}"
                     class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
            </div>
            <div class="flex flex-col justify-center p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-emerald-600 px-2.5 py-1 font-medium text-white">Berita Utama</span>
                    @if ($utama->kategori)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">{{ $utama->kategori->nama }}</span>
                    @endif
                </div>
                <h2 class="mt-3 text-2xl font-semibold text-slate-900 group-hover:text-emerald-700">
                    {{ $utama->judul }}
                </h2>
                <p class="mt-2 line-clamp-3 text-slate-600">{{ $utama->ringkasanTampil() }}</p>
                <p class="mt-4 text-sm text-slate-400">
                    {{ $utama->tanggal_terbit?->translatedFormat('d F Y') }} · {{ $utama->waktuBaca() }} menit baca
                </p>
            </div>
        </a>
    @endif

    @if ($daftar->isEmpty() && ! $utama)
        <div class="rounded-2xl border border-dashed border-slate-300 px-6 py-20 text-center">
            <p class="font-medium text-slate-900">
                {{ $kategori ? 'Belum ada berita pada kategori ini' : 'Belum ada berita' }}
            </p>
            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                Kabar dan pengumuman desa akan muncul di sini.
            </p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($daftar as $berita)
                <a href="{{ route('publik.berita.show', $berita) }}"
                   class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 transition hover:border-slate-300 hover:shadow-md">
                    <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                        <img src="{{ $berita->urlSampulKecil() }}" alt="{{ $berita->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        @if ($berita->kategori)
                            <span class="mb-2 self-start rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700">
                                {{ $berita->kategori->nama }}
                            </span>
                        @endif
                        <h2 class="font-semibold text-slate-900 group-hover:text-emerald-700">{{ $berita->judul }}</h2>
                        <p class="mt-1.5 line-clamp-3 flex-1 text-sm text-slate-600">{{ $berita->ringkasanTampil() }}</p>
                        <p class="mt-3 text-xs text-slate-400">
                            {{ $berita->tanggal_terbit?->translatedFormat('d F Y') }}
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
