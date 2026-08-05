@extends('publik.layouts.app')

@section('judul', 'Struktur Organisasi')
@section('deskripsi', 'Susunan pengurus ' . $desa->nama_desa . ' beserta jabatan dan kontak resminya.')

@section('konten')
<div class="mx-auto max-w-7xl px-6 md:px-12 py-12 md:py-16">

    <nav class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('beranda') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="mx-1.5">/</span>
        <span class="text-on-surface">Struktur Organisasi</span>
    </nav>

    <header class="mb-10 max-w-2xl">
        <h1 class="text-3xl font-semibold text-on-surface">Struktur Organisasi</h1>
        <p class="mt-2 text-on-surface-variant">
            Susunan pengurus {{ $desa->nama_desa }} beserta jabatan dan kontak resminya.
            Silakan hubungi pengurus sesuai keperluan Anda.
        </p>
    </header>

    @forelse ($kelompok as $namaOrganisasi => $daftarPengurus)
        <section class="mb-12">
            <div class="mb-5 flex items-baseline gap-3">
                <h2 class="text-xl font-semibold text-on-surface">{{ $namaOrganisasi }}</h2>
                <span class="text-sm text-on-surface-variant">{{ $daftarPengurus->count() }} orang</span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($daftarPengurus as $pengurus)
                    <article class="flex gap-4 rounded-[12px] border border-outline-variant/30 p-4 transition hover:border-primary/50 hover:shadow-md">
                        <img src="{{ $pengurus->urlFotoKecil() }}"
                             alt="Foto {{ $pengurus->nama }}"
                             loading="lazy"
                             class="h-20 w-20 shrink-0 rounded-[12px] bg-surface-container-lowest object-cover">

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-on-surface">{{ $pengurus->nama }}</p>
                            <p class="text-sm text-primary">{{ $pengurus->jabatan }}</p>

                            @if ($pengurus->keteranganWilayah())
                                <p class="mt-0.5 text-xs text-on-surface-variant">{{ $pengurus->keteranganWilayah() }}</p>
                            @endif

                            @if (filled($pengurus->nomor_hp))
                                <div class="mt-2.5 flex flex-wrap gap-2">
                                    <a href="tel:{{ preg_replace('/\D/', '', $pengurus->nomor_hp) }}"
                                       class="rounded-[12px] bg-surface-container-lowest px-2.5 py-1 text-xs font-medium text-on-surface transition hover:bg-surface-container-low">
                                        {{ $pengurus->nomor_hp }}
                                    </a>
                                    @if ($pengurus->nomorWhatsapp())
                                        <a href="https://wa.me/{{ $pengurus->nomorWhatsapp() }}"
                                           target="_blank" rel="noopener noreferrer"
                                           class="rounded-[12px] bg-primary-container px-2.5 py-1 text-xs font-medium text-primary transition hover:bg-primary-container">
                                            WhatsApp
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <div class="rounded-[12px] border border-dashed border-slate-300 px-6 py-20 text-center">
            <p class="font-medium text-on-surface">Data pengurus belum tersedia</p>
            <p class="mx-auto mt-1 max-w-md text-sm text-on-surface-variant">
                Susunan pengurus desa akan ditampilkan di sini setelah dilengkapi oleh
                aparat desa.
            </p>
        </div>
    @endforelse
</div>
@endsection
