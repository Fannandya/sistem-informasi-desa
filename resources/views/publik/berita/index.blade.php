@extends('publik.layouts.app')

@section('judul', $kategori ? 'Berita — ' . $kategori->nama : 'Berita Desa')
@section('deskripsi', 'Berita, pengumuman, dan informasi terbaru dari ' . $desa->nama_desa . '.')

@section('konten')
<div class="mx-auto max-w-7xl px-6 md:px-12 py-12 md:py-16">

    <nav class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('beranda') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="mx-1.5">/</span>
        @if ($kategori)
            <a href="{{ route('publik.berita.index') }}" class="hover:text-primary transition-colors">Berita</a>
            <span class="mx-1.5">/</span>
            <span class="text-on-surface">{{ $kategori->nama }}</span>
        @else
            <span class="text-on-surface">Berita</span>
        @endif
    </nav>

    <header class="mb-8 max-w-2xl">
        <h1 class="text-3xl font-semibold text-on-surface">
            {{ $kategori ? $kategori->nama : 'Berita Desa' }}
        </h1>
        <p class="mt-2 text-on-surface-variant">
            Kabar, pengumuman, dan informasi terbaru dari {{ $desa->nama_desa }}.
        </p>
    </header>

    @if ($kategoriList->isNotEmpty())
        <div class="mb-8 flex flex-wrap gap-2">
            <a href="{{ route('publik.berita.index') }}"
               class="rounded-full px-4 py-2 text-sm font-medium transition
                      {{ $kategori === null ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface hover:bg-surface-container-low' }}">
                Semua
            </a>
            @foreach ($kategoriList as $k)
                <a href="{{ route('publik.berita.kategori', $k) }}"
                   class="rounded-full px-4 py-2 text-sm font-medium transition
                          {{ $kategori?->id === $k->id ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface hover:bg-surface-container-low' }}">
                    {{ $k->nama }}
                    <span class="ml-1 opacity-60">{{ $k->berita_count }}</span>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Berita utama --}}
    @if ($utama)
        <a href="{{ route('publik.berita.show', $utama) }}"
           class="group mb-10 grid overflow-hidden rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest shadow-[0px_4px_20px_rgba(0,0,0,0.03)] transition hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] md:grid-cols-2">
            <div class="aspect-[16/10] overflow-hidden bg-surface-container-highest md:aspect-auto">
                <img src="{{ $utama->urlSampul() }}" alt="{{ $utama->judul }}"
                     class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
            </div>
            <div class="flex flex-col justify-center p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-primary text-on-primary px-2.5 py-1 font-medium">Berita Utama</span>
                    @if ($utama->kategori)
                        <span class="rounded-full bg-primary-container text-on-primary-container px-2.5 py-1">{{ $utama->kategori->nama }}</span>
                    @endif
                </div>
                <h2 class="mt-3 text-2xl font-semibold text-on-surface group-hover:text-primary transition-colors">
                    {{ $utama->judul }}
                </h2>
                <p class="mt-2 line-clamp-3 text-on-surface-variant">{{ $utama->ringkasanTampil() }}</p>
                <p class="mt-4 text-sm text-on-surface-variant">
                    {{ $utama->tanggal_terbit?->translatedFormat('d F Y') }} · {{ $utama->waktuBaca() }} menit baca
                </p>
            </div>
        </a>
    @endif

    @if ($daftar->isEmpty() && ! $utama)
        <div class="rounded-[12px] border border-dashed border-outline-variant/50 px-6 py-20 text-center">
            <p class="font-medium text-on-surface">
                {{ $kategori ? 'Belum ada berita pada kategori ini' : 'Belum ada berita' }}
            </p>
            <p class="mx-auto mt-1 max-w-md text-sm text-on-surface-variant">
                Kabar dan pengumuman desa akan muncul di sini.
            </p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($daftar as $berita)
                <a href="{{ route('publik.berita.show', $berita) }}"
                   class="group flex flex-col overflow-hidden rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest shadow-[0px_4px_20px_rgba(0,0,0,0.03)] transition hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)]">
                    <div class="aspect-[16/10] overflow-hidden bg-surface-container-highest">
                        <img src="{{ $berita->urlSampulKecil() }}" alt="{{ $berita->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        @if ($berita->kategori)
                            <span class="mb-2 self-start rounded-full bg-primary-container px-2.5 py-1 text-xs text-on-primary-container">
                                {{ $berita->kategori->nama }}
                            </span>
                        @endif
                        <h2 class="font-semibold text-on-surface group-hover:text-primary transition-colors">{{ $berita->judul }}</h2>
                        <p class="mt-1.5 line-clamp-3 flex-1 text-sm text-on-surface-variant">{{ $berita->ringkasanTampil() }}</p>
                        <p class="mt-3 text-xs text-on-surface-variant">
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
