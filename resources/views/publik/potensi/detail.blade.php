@extends('publik.layouts.app')

@section('judul', $potensi->nama)
@section('deskripsi', $potensi->ringkasan() ?: $potensi->nama . ' — potensi ' . $desa->nama_desa)

@section('konten')
<article class="mx-auto max-w-4xl px-6 md:px-12 py-12 md:py-16">

    <nav class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('beranda') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('publik.potensi.index') }}" class="hover:text-primary transition-colors">Potensi Desa</a>
        <span class="mx-1.5">/</span>
        <span class="text-on-surface">{{ $potensi->nama }}</span>
    </nav>

    <header class="mb-6">
        <span class="badge-kategori">
            {{ $potensi->kategori?->nama ?? 'Lainnya' }}
        </span>
        <h1 class="mt-3 text-3xl font-semibold text-primary">{{ $potensi->nama }}</h1>
        @if ($potensi->deskripsi_singkat)
            <p class="mt-2 text-lg text-on-surface-variant">{{ $potensi->deskripsi_singkat }}</p>
        @endif
    </header>

    <div class="overflow-hidden rounded-[12px] bg-surface-container-highest shadow-[0px_10px_30px_rgba(0,0,0,0.08)]">
        <img src="{{ $potensi->urlFoto() }}" alt="{{ $potensi->nama }}"
             class="max-h-[28rem] w-full object-cover">
    </div>

    @if (filled($potensi->deskripsi))
        <div class="mt-8 max-w-2xl leading-relaxed whitespace-pre-line text-on-surface">{{ $potensi->deskripsi }}</div>
    @endif

    @if (filled($potensi->kontak))
        <div class="mt-8 rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest p-5 shadow-[0px_4px_20px_rgba(0,0,0,0.03)]">
            <p class="text-xs font-semibold tracking-wider text-on-surface-variant uppercase">Kontak</p>
            <p class="mt-1.5 text-on-surface">{{ $potensi->kontak }}</p>
        </div>
    @endif

    @if ($lainnya->isNotEmpty())
        <section class="mt-14 border-t border-outline-variant/30 pt-10">
            <h2 class="mb-5 text-lg font-semibold text-primary">
                {{ $potensi->kategori?->nama ?? 'Lainnya' }} lainnya
            </h2>
            <div class="grid gap-5 sm:grid-cols-3">
                @foreach ($lainnya as $lain)
                    <a href="{{ route('publik.potensi.show', $lain) }}"
                       class="group overflow-hidden rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest shadow-[0px_4px_20px_rgba(0,0,0,0.03)] transition hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)]">
                        <div class="aspect-[4/3] overflow-hidden bg-surface-container-highest">
                            <img src="{{ $lain->urlFotoKecil() }}" alt="{{ $lain->nama }}" loading="lazy"
                                 class="h-full w-full object-cover transition group-hover:scale-105">
                        </div>
                        <p class="p-3 text-sm font-medium text-on-surface group-hover:text-primary transition-colors">
                            {{ $lain->nama }}
                        </p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="mt-10">
        <a href="{{ route('publik.potensi.index') }}"
           class="text-sm text-on-surface-variant hover:text-primary transition-colors">&larr; Kembali ke daftar potensi</a>
    </div>
</article>
@endsection
