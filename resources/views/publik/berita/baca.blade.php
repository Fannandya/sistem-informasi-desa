@extends('publik.layouts.app')

@section('judul', $berita->judul)
@section('deskripsi', $berita->deskripsiMeta())

@push('skrip')
<meta property="og:image" content="{{ $berita->urlSampul() }}">
@endpush

@section('konten')
<article class="mx-auto max-w-3xl px-6 md:px-12 py-12 md:py-16">

    <nav class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('beranda') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('publik.berita.index') }}" class="hover:text-primary transition-colors">Berita</a>
        <span class="mx-1.5">/</span>
        <span class="text-on-surface">{{ Str::limit($berita->judul, 40) }}</span>
    </nav>

    <header class="mb-6">
        @if ($berita->kategori)
            <a href="{{ route('publik.berita.kategori', $berita->kategori) }}"
               class="badge-kategori inline-block hover:border-primary-container">
                {{ $berita->kategori->nama }}
            </a>
        @endif

        <h1 class="mt-3 text-3xl leading-tight font-semibold text-primary">{{ $berita->judul }}</h1>

        <p class="mt-3 text-sm text-on-surface-variant">
            {{ $berita->tanggal_terbit?->translatedFormat('l, d F Y') }}
            · {{ $berita->waktuBaca() }} menit baca
            @if ($berita->admin)
                · {{ $berita->admin->nama }}
            @endif
        </p>
    </header>

    @if (filled($berita->gambar_sampul))
        <div class="mb-8 overflow-hidden rounded-[12px] bg-surface-container-highest shadow-[0px_10px_30px_rgba(0,0,0,0.08)]">
            <img src="{{ $berita->urlSampul() }}" alt="{{ $berita->judul }}" class="w-full object-cover">
        </div>
    @endif

    {{--
        Isi sudah dibersihkan HTML Purifier saat disimpan (profil "berita"),
        sehingga aman dirender apa adanya di sini.
    --}}
    <div class="prose-desa">{!! $berita->isi !!}</div>

    @if ($lainnya->isNotEmpty())
        <section class="mt-14 border-t border-outline-variant/30 pt-10">
            <h2 class="mb-5 text-lg font-semibold text-primary">Berita lainnya</h2>
            <div class="grid gap-5 sm:grid-cols-3">
                @foreach ($lainnya as $lain)
                    <a href="{{ route('publik.berita.show', $lain) }}"
                       class="group overflow-hidden rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest shadow-[0px_4px_20px_rgba(0,0,0,0.03)] transition hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)]">
                        <div class="aspect-[16/10] overflow-hidden bg-surface-container-highest">
                            <img src="{{ $lain->urlSampulKecil() }}" alt="{{ $lain->judul }}" loading="lazy"
                                 class="h-full w-full object-cover transition group-hover:scale-105">
                        </div>
                        <div class="p-3">
                            <p class="line-clamp-2 text-sm font-medium text-on-surface group-hover:text-primary transition-colors">
                                {{ $lain->judul }}
                            </p>
                            <p class="mt-1 text-xs text-on-surface-variant">
                                {{ $lain->tanggal_terbit?->translatedFormat('d M Y') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="mt-10">
        <a href="{{ route('publik.berita.index') }}" class="text-sm text-on-surface-variant hover:text-primary transition-colors">
            &larr; Kembali ke daftar berita
        </a>
    </div>
</article>
@endsection
