@extends('publik.layouts.app')

@section('judul', 'Beranda')
@section('deskripsi', $desa->deskripsiMeta())

@php
    // Setiap bagian hanya dirender bila datanya ada, supaya tidak ada judul
    // bagian yang menggantung tanpa isi (PRD fitur 14).
    $seksi = fn ($kondisi) => $kondisi;
@endphp

@section('konten')

{{-- ============================== HERO ============================== --}}
<section class="relative overflow-hidden bg-slate-900">
    @if (filled($desa->foto_sampul))
        <img src="{{ $desa->urlSampul() }}" alt=""
             class="absolute inset-0 h-full w-full object-cover opacity-45">
    @endif

    <div class="relative mx-auto max-w-6xl px-4 py-24 sm:px-6 sm:py-32">
        <div class="max-w-2xl">
            @if ($desa->wilayahLengkap())
                <p class="text-sm font-medium tracking-wider text-emerald-300 uppercase">
                    {{ $desa->wilayahLengkap() }}
                </p>
            @endif

            <h1 class="mt-3 text-4xl leading-tight font-semibold text-white sm:text-5xl">
                Selamat Datang di<br>{{ $desa->nama_desa }}
            </h1>

            @if (filled($desa->meta_description) || filled($desa->visi))
                <p class="mt-5 text-lg leading-relaxed text-slate-200">
                    {{ Str::limit($desa->meta_description ?: $desa->visi, 200) }}
                </p>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('publik.profil') }}"
                   class="rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500">
                    Kenali Desa Kami
                </a>
                <a href="#potensi"
                   class="rounded-lg border border-white/30 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                    Lihat Potensi Desa
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================ STATISTIK ============================ --}}
<section id="statistik" class="border-b border-slate-200 bg-slate-50 py-14">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Jumlah Penduduk', number_format($statistik['jumlah_penduduk'], 0, ',', '.'), 'jiwa'],
                ['Kepala Keluarga', $statistik['jumlah_kk'] !== null ? number_format($statistik['jumlah_kk'], 0, ',', '.') : null, 'keluarga'],
                ['Laki-laki', number_format($statistik['laki'], 0, ',', '.'), 'jiwa'],
                ['Perempuan', number_format($statistik['perempuan'], 0, ',', '.'), 'jiwa'],
            ] as [$label, $nilai, $satuan])
                @if ($nilai !== null)
                    <div class="text-center">
                        <p class="text-3xl font-semibold text-slate-900 sm:text-4xl">{{ $nilai }}</p>
                        <p class="mt-1 text-sm font-medium text-slate-700">{{ $label }}</p>
                        <p class="text-xs text-slate-400">{{ $satuan }}</p>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('publik.statistik') }}"
               class="text-sm font-medium text-emerald-700 hover:text-emerald-800">
                Lihat Statistik Lengkap &rarr;
            </a>
        </div>
    </div>
</section>

{{-- ========================== SEKILAS DESA ========================== --}}
@if (filled($desa->visi) || filled($desa->sejarah))
    <section id="profil" class="py-16">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-2">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Sekilas {{ $desa->nama_desa }}</h2>
                <p class="mt-4 leading-relaxed text-slate-600">
                    {{ Str::limit(strip_tags($desa->sejarah ?: $desa->visi), 480) }}
                </p>
                <a href="{{ route('publik.profil') }}"
                   class="mt-6 inline-block rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    Lihat Profil Lengkap
                </a>
            </div>

            @if ($desa->misiSebagaiDaftar())
                <div class="rounded-2xl bg-slate-50 p-6">
                    <h3 class="mb-4 text-sm font-semibold tracking-wider text-slate-500 uppercase">Misi Desa</h3>
                    <ol class="list-decimal space-y-2 pl-5 text-sm text-slate-700">
                        @foreach (array_slice($desa->misiSebagaiDaftar(), 0, 4) as $misi)
                            <li>{{ $misi }}</li>
                        @endforeach
                    </ol>
                </div>
            @endif
        </div>
    </section>
@endif

{{-- ============================ POTENSI ============================ --}}
@if ($potensi->isNotEmpty())
    <section id="potensi" class="border-t border-slate-200 py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Potensi Unggulan</h2>
                    <p class="mt-1.5 text-slate-600">UMKM, hasil bumi, wisata, dan kerajinan desa.</p>
                </div>
                <a href="{{ route('publik.potensi.index') }}"
                   class="text-sm font-medium text-emerald-700 hover:text-emerald-800">Lihat Semua Potensi &rarr;</a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($potensi as $item)
                    <a href="{{ route('publik.potensi.show', $item) }}"
                       class="group overflow-hidden rounded-2xl border border-slate-200 transition hover:border-slate-300 hover:shadow-md">
                        <div class="aspect-[4/3] overflow-hidden bg-slate-100">
                            <img src="{{ $item->urlFotoKecil() }}" alt="{{ $item->nama }}" loading="lazy"
                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        </div>
                        <div class="p-5">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $item->kategori->warna() }}">
                                {{ $item->kategori->label() }}
                            </span>
                            <h3 class="mt-3 font-semibold text-slate-900 group-hover:text-emerald-700">{{ $item->nama }}</h3>
                            <p class="mt-1.5 line-clamp-2 text-sm text-slate-600">{{ $item->ringkasan() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ============================= BERITA ============================= --}}
@if ($beritaUtama)
    <section id="berita" class="border-t border-slate-200 bg-slate-50 py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Berita Terbaru</h2>
                    <p class="mt-1.5 text-slate-600">Kabar dan pengumuman dari pemerintah desa.</p>
                </div>
                <a href="{{ route('publik.berita.index') }}"
                   class="text-sm font-medium text-emerald-700 hover:text-emerald-800">Lihat Berita Lebih Lengkap &rarr;</a>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <a href="{{ route('publik.berita.show', $beritaUtama) }}"
                   class="group overflow-hidden rounded-2xl bg-white transition hover:shadow-md">
                    <div class="aspect-[16/9] overflow-hidden bg-slate-100">
                        <img src="{{ $beritaUtama->urlSampul() }}" alt="{{ $beritaUtama->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    </div>
                    <div class="p-6">
                        @if ($beritaUtama->kategori)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                {{ $beritaUtama->kategori->nama }}
                            </span>
                        @endif
                        <h3 class="mt-3 text-xl font-semibold text-slate-900 group-hover:text-emerald-700">
                            {{ $beritaUtama->judul }}
                        </h3>
                        <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $beritaUtama->ringkasanTampil() }}</p>
                        <p class="mt-3 text-xs text-slate-400">
                            {{ $beritaUtama->tanggal_terbit?->translatedFormat('d F Y') }}
                        </p>
                    </div>
                </a>

                <div class="space-y-4">
                    @foreach ($berita->where('id', '!=', $beritaUtama->id)->take(3) as $item)
                        <a href="{{ route('publik.berita.show', $item) }}"
                           class="group flex gap-4 rounded-2xl bg-white p-4 transition hover:shadow-md">
                            <img src="{{ $item->urlSampulKecil() }}" alt="" loading="lazy"
                                 class="h-20 w-28 shrink-0 rounded-xl bg-slate-100 object-cover">
                            <div class="min-w-0">
                                <h3 class="line-clamp-2 font-medium text-slate-900 group-hover:text-emerald-700">
                                    {{ $item->judul }}
                                </h3>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $item->tanggal_terbit?->translatedFormat('d F Y') }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

{{-- ============================= GALERI ============================= --}}
@if ($acara->isNotEmpty())
    <section id="galeri" class="border-t border-slate-200 py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Galeri Kegiatan</h2>
                    <p class="mt-1.5 text-slate-600">Dokumentasi acara dan kegiatan warga.</p>
                </div>
                <a href="{{ route('publik.galeri.index') }}"
                   class="text-sm font-medium text-emerald-700 hover:text-emerald-800">Lihat Galeri Lengkap &rarr;</a>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                @foreach ($acara as $item)
                    <a href="{{ route('publik.galeri.show', $item) }}"
                       class="group relative aspect-[4/3] overflow-hidden rounded-2xl bg-slate-100">
                        <img src="{{ $item->urlSampulKecil() }}" alt="{{ $item->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-900/85 to-transparent p-4">
                            <p class="line-clamp-2 text-sm font-medium text-white">{{ $item->judul }}</p>
                            <p class="mt-0.5 text-xs text-white/70">
                                {{ $item->tanggal_acara->translatedFormat('d M Y') }}
                                @if ($item->foto_count > 1) · {{ $item->foto_count }} foto @endif
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ===================== FASILITAS & PRESTASI ===================== --}}
@if ($fasilitas->isNotEmpty() || $prestasi->isNotEmpty())
    <section class="border-t border-slate-200 bg-slate-50 py-16">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-2">

            @if ($fasilitas->isNotEmpty())
                <div>
                    <div class="mb-5 flex items-end justify-between gap-3">
                        <h2 class="text-2xl font-semibold text-slate-900">Fasilitas Desa</h2>
                        <a href="{{ route('publik.fasilitas') }}"
                           class="text-sm font-medium text-emerald-700 hover:text-emerald-800">Lihat Semua &rarr;</a>
                    </div>
                    <div class="space-y-3">
                        @foreach ($fasilitas as $item)
                            <div class="flex items-center gap-4 rounded-xl bg-white p-4">
                                <img src="{{ $item->urlFotoKecil() }}" alt="" loading="lazy"
                                     class="h-14 w-14 shrink-0 rounded-lg bg-slate-100 object-cover">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-slate-900">{{ $item->nama }}</p>
                                    <span class="rounded-full px-2 py-0.5 text-xs {{ $item->kategori->warna() }}">
                                        {{ $item->kategori->label() }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($prestasi->isNotEmpty())
                <div>
                    <div class="mb-5 flex items-end justify-between gap-3">
                        <h2 class="text-2xl font-semibold text-slate-900">Prestasi Desa</h2>
                        <a href="{{ route('publik.prestasi') }}"
                           class="text-sm font-medium text-emerald-700 hover:text-emerald-800">Lihat Semua &rarr;</a>
                    </div>
                    <div class="space-y-3">
                        @foreach ($prestasi as $item)
                            <div class="rounded-xl bg-white p-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-slate-900 tabular-nums">{{ $item->tahun }}</span>
                                    @if ($item->tingkat)
                                        <span class="rounded-full px-2 py-0.5 text-xs {{ $item->tingkat->warna() }}">
                                            {{ $item->tingkat->label() }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1 font-medium text-slate-900">{{ $item->judul }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif

{{-- ============================ PENGURUS ============================ --}}
@if ($pengurus->isNotEmpty())
    <section id="pengurus" class="border-t border-slate-200 py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Struktur Organisasi</h2>
                    <p class="mt-1.5 text-slate-600">Aparat desa yang melayani warga.</p>
                </div>
                <a href="{{ route('publik.struktur') }}"
                   class="text-sm font-medium text-emerald-700 hover:text-emerald-800">Lihat Struktur Lengkap &rarr;</a>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($pengurus as $orang)
                    <div class="rounded-2xl border border-slate-200 p-5 text-center">
                        <img src="{{ $orang->urlFotoKecil() }}" alt="Foto {{ $orang->nama }}" loading="lazy"
                             class="mx-auto h-24 w-24 rounded-full bg-slate-100 object-cover">
                        <p class="mt-3 font-semibold text-slate-900">{{ $orang->nama }}</p>
                        <p class="text-sm text-emerald-700">{{ $orang->jabatan }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ============================= KONTAK ============================= --}}
@if ($desa->punyaKontak() || filled($desa->peta_embed))
    <section id="kontak" class="border-t border-slate-200 bg-slate-50 py-16">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-2">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Hubungi Kami</h2>
                <p class="mt-2 text-slate-600">
                    Kantor {{ $desa->nama_desa }} melayani warga pada hari kerja.
                </p>

                <dl class="mt-6 space-y-3 text-sm">
                    @foreach ([
                        'Alamat' => $desa->alamat_kantor,
                        'Telepon' => $desa->telepon_kantor,
                        'Email' => $desa->email,
                    ] as $label => $nilai)
                        @if (filled($nilai))
                            <div class="flex gap-3">
                                <dt class="w-20 shrink-0 text-slate-500">{{ $label }}</dt>
                                <dd class="text-slate-800">{{ $nilai }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>

            @if (filled($desa->peta_embed))
                <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200">
                    <iframe src="{{ $desa->peta_embed }}" class="h-72 w-full" style="border:0"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            title="Peta lokasi {{ $desa->nama_desa }}"></iframe>
                </div>
            @endif
        </div>
    </section>
@endif

@endsection
