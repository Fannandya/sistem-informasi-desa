@extends('publik.layouts.app')

@section('judul', $acara->judul)
@section('deskripsi', $acara->deskripsiMeta())

@section('konten')
<article class="mx-auto max-w-5xl px-4 py-12 sm:px-6"
         x-data="lightbox(@js($acara->foto->map(fn ($f) => ['url' => $f->url(), 'keterangan' => $f->keterangan])->values()))">

    <nav class="mb-6 text-sm text-slate-500">
        <a href="{{ route('beranda') }}" class="hover:text-emerald-700">Beranda</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('publik.galeri.index') }}" class="hover:text-emerald-700">Galeri</a>
        <span class="mx-1.5">/</span>
        <span class="text-slate-700">{{ Str::limit($acara->judul, 40) }}</span>
    </nav>

    <header class="mb-8 max-w-2xl">
        <h1 class="text-3xl font-semibold text-slate-900">{{ $acara->judul }}</h1>
        <p class="mt-2 text-sm text-slate-500">
            {{ $acara->tanggal_acara->translatedFormat('l, d F Y') }}
            @if (filled($acara->lokasi))
                · {{ $acara->lokasi }}
            @endif
            · {{ $acara->foto->count() }} foto
        </p>
        @if (filled($acara->narasi))
            <p class="mt-4 leading-relaxed whitespace-pre-line text-slate-700">{{ $acara->narasi }}</p>
        @endif
    </header>

    @if ($acara->foto->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 px-6 py-16 text-center">
            <p class="text-sm text-slate-500">Dokumentasi foto untuk kegiatan ini belum tersedia.</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($acara->foto as $i => $foto)
                <button type="button" @click="buka({{ $i }})"
                        class="group aspect-square overflow-hidden rounded-xl bg-slate-100">
                    <img src="{{ $foto->urlKecil() }}" alt="{{ $foto->keterangan ?? $acara->judul }}"
                         loading="lazy"
                         class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                </button>
            @endforeach
        </div>
    @endif

    @if ($lainnya->isNotEmpty())
        <section class="mt-14 border-t border-slate-200 pt-10">
            <h2 class="mb-5 text-lg font-semibold text-slate-900">Kegiatan lainnya</h2>
            <div class="grid gap-5 sm:grid-cols-3">
                @foreach ($lainnya as $lain)
                    <a href="{{ route('publik.galeri.show', $lain) }}"
                       class="group overflow-hidden rounded-xl border border-slate-200 transition hover:border-slate-300">
                        <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                            <img src="{{ $lain->urlSampulKecil() }}" alt="{{ $lain->judul }}" loading="lazy"
                                 class="h-full w-full object-cover transition group-hover:scale-105">
                        </div>
                        <div class="p-3">
                            <p class="line-clamp-2 text-sm font-medium text-slate-900 group-hover:text-emerald-700">
                                {{ $lain->judul }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">{{ $lain->tanggal_acara->translatedFormat('d M Y') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="mt-10">
        <a href="{{ route('publik.galeri.index') }}" class="text-sm text-slate-500 hover:text-emerald-700">
            &larr; Kembali ke galeri
        </a>
    </div>

    {{-- Lightbox --}}
    <template x-teleport="body">
        <div x-show="terbuka" x-cloak
             @keydown.escape.window="tutup()"
             @keydown.arrow-left.window="sebelumnya()"
             @keydown.arrow-right.window="berikutnya()"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/95 p-4">

            <button type="button" @click="tutup()" aria-label="Tutup"
                    class="absolute top-4 right-4 rounded-lg p-2 text-white/70 hover:bg-white/10 hover:text-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <button type="button" @click="sebelumnya()" aria-label="Sebelumnya" x-show="foto.length > 1"
                    class="absolute left-2 rounded-lg p-3 text-white/70 hover:bg-white/10 hover:text-white sm:left-6">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <div class="flex max-h-full max-w-5xl flex-col items-center">
                <img :src="foto[indeks]?.url" :alt="foto[indeks]?.keterangan ?? ''"
                     class="max-h-[80vh] max-w-full rounded-lg object-contain">
                <p class="mt-3 text-center text-sm text-white/80" x-show="foto[indeks]?.keterangan"
                   x-text="foto[indeks]?.keterangan"></p>
                <p class="mt-1 text-xs text-white/50" x-text="`${indeks + 1} / ${foto.length}`"></p>
            </div>

            <button type="button" @click="berikutnya()" aria-label="Berikutnya" x-show="foto.length > 1"
                    class="absolute right-2 rounded-lg p-3 text-white/70 hover:bg-white/10 hover:text-white sm:right-6">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </template>
</article>

@push('skrip')
<script>
    function lightbox(foto) {
        return {
            foto,
            terbuka: false,
            indeks: 0,

            buka(i) {
                this.indeks = i;
                this.terbuka = true;
                // Mencegah halaman di belakang ikut tergulir saat lightbox
                // dibuka di ponsel.
                document.body.style.overflow = 'hidden';
            },
            tutup() {
                this.terbuka = false;
                document.body.style.overflow = '';
            },
            sebelumnya() {
                if (this.terbuka) this.indeks = (this.indeks - 1 + this.foto.length) % this.foto.length;
            },
            berikutnya() {
                if (this.terbuka) this.indeks = (this.indeks + 1) % this.foto.length;
            },
        };
    }
</script>
@endpush
@endsection
