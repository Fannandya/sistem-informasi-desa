@extends('publik.layouts.app')

@section('judul', $potensi->nama)
@section('deskripsi', $potensi->ringkasan() ?: $potensi->nama . ' — potensi ' . $desa->nama_desa)

@section('konten')
<article class="mx-auto max-w-4xl px-4 py-12 sm:px-6">

    <nav class="mb-6 text-sm text-slate-500">
        <a href="{{ route('beranda') }}" class="hover:text-emerald-700">Beranda</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('publik.potensi.index') }}" class="hover:text-emerald-700">Potensi Desa</a>
        <span class="mx-1.5">/</span>
        <span class="text-slate-700">{{ $potensi->nama }}</span>
    </nav>

    <header class="mb-6">
        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $potensi->kategori->warna() }}">
            {{ $potensi->kategori->label() }}
        </span>
        <h1 class="mt-3 text-3xl font-semibold text-slate-900">{{ $potensi->nama }}</h1>
        @if ($potensi->deskripsi_singkat)
            <p class="mt-2 text-lg text-slate-600">{{ $potensi->deskripsi_singkat }}</p>
        @endif
    </header>

    <div class="overflow-hidden rounded-2xl bg-slate-100">
        <img src="{{ $potensi->urlFoto() }}" alt="{{ $potensi->nama }}"
             class="max-h-[28rem] w-full object-cover">
    </div>

    @if (filled($potensi->deskripsi))
        <div class="mt-8 max-w-2xl leading-relaxed whitespace-pre-line text-slate-700">{{ $potensi->deskripsi }}</div>
    @endif

    @if (filled($potensi->kontak))
        <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Kontak</p>
            <p class="mt-1.5 text-slate-800">{{ $potensi->kontak }}</p>
        </div>
    @endif

    @if ($lainnya->isNotEmpty())
        <section class="mt-14 border-t border-slate-200 pt-10">
            <h2 class="mb-5 text-lg font-semibold text-slate-900">
                {{ $potensi->kategori->label() }} lainnya
            </h2>
            <div class="grid gap-5 sm:grid-cols-3">
                @foreach ($lainnya as $lain)
                    <a href="{{ route('publik.potensi.show', $lain) }}"
                       class="group overflow-hidden rounded-xl border border-slate-200 transition hover:border-slate-300">
                        <div class="aspect-[4/3] overflow-hidden bg-slate-100">
                            <img src="{{ $lain->urlFotoKecil() }}" alt="{{ $lain->nama }}" loading="lazy"
                                 class="h-full w-full object-cover transition group-hover:scale-105">
                        </div>
                        <p class="p-3 text-sm font-medium text-slate-900 group-hover:text-emerald-700">
                            {{ $lain->nama }}
                        </p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="mt-10">
        <a href="{{ route('publik.potensi.index') }}"
           class="text-sm text-slate-500 hover:text-emerald-700">&larr; Kembali ke daftar potensi</a>
    </div>
</article>
@endsection
