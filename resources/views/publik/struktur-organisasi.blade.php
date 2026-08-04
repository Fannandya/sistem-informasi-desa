@extends('publik.layouts.app')

@section('judul', 'Struktur Organisasi')
@section('deskripsi', 'Susunan pengurus ' . $desa->nama_desa . ' beserta jabatan dan kontak resminya.')

@section('konten')
<div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">

    <nav class="mb-6 text-sm text-slate-500">
        <a href="{{ route('beranda') }}" class="hover:text-emerald-700">Beranda</a>
        <span class="mx-1.5">/</span>
        <span class="text-slate-700">Struktur Organisasi</span>
    </nav>

    <header class="mb-10 max-w-2xl">
        <h1 class="text-3xl font-semibold text-slate-900">Struktur Organisasi</h1>
        <p class="mt-2 text-slate-600">
            Susunan pengurus {{ $desa->nama_desa }} beserta jabatan dan kontak resminya.
            Silakan hubungi pengurus sesuai keperluan Anda.
        </p>
    </header>

    @forelse ($kelompok as $namaOrganisasi => $daftarPengurus)
        <section class="mb-12">
            <div class="mb-5 flex items-baseline gap-3">
                <h2 class="text-xl font-semibold text-slate-900">{{ $namaOrganisasi }}</h2>
                <span class="text-sm text-slate-400">{{ $daftarPengurus->count() }} orang</span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($daftarPengurus as $pengurus)
                    <article class="flex gap-4 rounded-2xl border border-slate-200 p-4 transition hover:border-slate-300 hover:shadow-sm">
                        <img src="{{ $pengurus->urlFotoKecil() }}"
                             alt="Foto {{ $pengurus->nama }}"
                             loading="lazy"
                             class="h-20 w-20 shrink-0 rounded-xl bg-slate-100 object-cover">

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-slate-900">{{ $pengurus->nama }}</p>
                            <p class="text-sm text-emerald-700">{{ $pengurus->jabatan }}</p>

                            @if ($pengurus->keteranganWilayah())
                                <p class="mt-0.5 text-xs text-slate-500">{{ $pengurus->keteranganWilayah() }}</p>
                            @endif

                            @if (filled($pengurus->nomor_hp))
                                <div class="mt-2.5 flex flex-wrap gap-2">
                                    <a href="tel:{{ preg_replace('/\D/', '', $pengurus->nomor_hp) }}"
                                       class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 transition hover:bg-slate-200">
                                        {{ $pengurus->nomor_hp }}
                                    </a>
                                    @if ($pengurus->nomorWhatsapp())
                                        <a href="https://wa.me/{{ $pengurus->nomorWhatsapp() }}"
                                           target="_blank" rel="noopener noreferrer"
                                           class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100">
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
        <div class="rounded-2xl border border-dashed border-slate-300 px-6 py-20 text-center">
            <p class="font-medium text-slate-900">Data pengurus belum tersedia</p>
            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                Susunan pengurus desa akan ditampilkan di sini setelah dilengkapi oleh
                aparat desa.
            </p>
        </div>
    @endforelse
</div>
@endsection
