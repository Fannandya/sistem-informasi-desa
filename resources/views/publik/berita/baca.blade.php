@extends('publik.layouts.app')

@section('judul', $berita->judul)
@section('deskripsi', $berita->deskripsiMeta())

@push('skrip')
<meta property="og:image" content="{{ $berita->urlSampul() }}">
@endpush

@section('konten')
<article class="mx-auto max-w-3xl px-4 py-12 sm:px-6">

    <nav class="mb-6 text-sm text-slate-500">
        <a href="{{ route('beranda') }}" class="hover:text-emerald-700">Beranda</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('publik.berita.index') }}" class="hover:text-emerald-700">Berita</a>
        <span class="mx-1.5">/</span>
        <span class="text-slate-700">{{ Str::limit($berita->judul, 40) }}</span>
    </nav>

    <header class="mb-6">
        @if ($berita->kategori)
            <a href="{{ route('publik.berita.kategori', $berita->kategori) }}"
               class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-100">
                {{ $berita->kategori->nama }}
            </a>
        @endif

        <h1 class="mt-3 text-3xl leading-tight font-semibold text-slate-900">{{ $berita->judul }}</h1>

        <p class="mt-3 text-sm text-slate-500">
            {{ $berita->tanggal_terbit?->translatedFormat('l, d F Y') }}
            · {{ $berita->waktuBaca() }} menit baca
            @if ($berita->admin)
                · {{ $berita->admin->nama }}
            @endif
        </p>
    </header>

    @if (filled($berita->gambar_sampul))
        <div class="mb-8 overflow-hidden rounded-2xl bg-slate-100">
            <img src="{{ $berita->urlSampul() }}" alt="{{ $berita->judul }}" class="w-full object-cover">
        </div>
    @endif

    {{--
        Isi sudah dibersihkan HTML Purifier saat disimpan (profil "berita"),
        sehingga aman dirender apa adanya di sini.
    --}}
    <div class="prose-desa">{!! $berita->isi !!}</div>

    @if ($lainnya->isNotEmpty())
        <section class="mt-14 border-t border-slate-200 pt-10">
            <h2 class="mb-5 text-lg font-semibold text-slate-900">Berita lainnya</h2>
            <div class="grid gap-5 sm:grid-cols-3">
                @foreach ($lainnya as $lain)
                    <a href="{{ route('publik.berita.show', $lain) }}"
                       class="group overflow-hidden rounded-xl border border-slate-200 transition hover:border-slate-300">
                        <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                            <img src="{{ $lain->urlSampulKecil() }}" alt="{{ $lain->judul }}" loading="lazy"
                                 class="h-full w-full object-cover transition group-hover:scale-105">
                        </div>
                        <div class="p-3">
                            <p class="line-clamp-2 text-sm font-medium text-slate-900 group-hover:text-emerald-700">
                                {{ $lain->judul }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ $lain->tanggal_terbit?->translatedFormat('d M Y') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="mt-10">
        <a href="{{ route('publik.berita.index') }}" class="text-sm text-slate-500 hover:text-emerald-700">
            &larr; Kembali ke daftar berita
        </a>
    </div>
</article>
@endsection
