@extends('publik.layouts.app')

@section('judul', 'Beranda')
@section('deskripsi', $desa->deskripsiMeta())

@php
    use App\Support\Teks;

    // Setiap bagian hanya dirender bila datanya ada, supaya tidak ada judul
    // bagian yang menggantung tanpa isi (PRD fitur 14).
    $seksi = fn ($kondisi) => $kondisi;
@endphp

@section('konten')

{{-- ============================== HERO ============================== --}}
<section class="relative overflow-hidden bg-on-surface">
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
                {{ Teks::ambil('beranda.hero.sapaan') }}<br>{{ $desa->nama_desa }}
            </h1>

            @if (filled($desa->meta_description) || filled($desa->visi))
                <p class="mt-5 text-lg leading-relaxed text-slate-200">
                    {{ Str::limit($desa->meta_description ?: $desa->visi, Teks::angka('kuota.beranda.limit_sapaan')) }}
                </p>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('publik.profil') }}"
                   class="rounded-[12px] bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary-fixed/200">
                    {{ Teks::ambil('beranda.hero.cta_profil') }}
                </a>
                <a href="#potensi"
                   class="rounded-[12px] border border-white/30 px-5 py-3 text-sm font-semibold text-white transition hover:bg-surface-container-lowest/10">
                    {{ Teks::ambil('beranda.hero.cta_potensi') }}
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================ STATISTIK ============================ --}}
<section id="statistik" class="border-b border-outline-variant bg-surface-container-low py-14">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                [Teks::ambil('beranda.statistik.label_penduduk'), number_format($statistik['jumlah_penduduk'], 0, ',', '.'), Teks::ambil('beranda.statistik.satuan_jiwa')],
                [Teks::ambil('beranda.statistik.label_kk'), $statistik['jumlah_kk'] !== null ? number_format($statistik['jumlah_kk'], 0, ',', '.') : null, Teks::ambil('beranda.statistik.satuan_keluarga')],
                [Teks::ambil('beranda.statistik.label_laki'), number_format($statistik['laki'], 0, ',', '.'), Teks::ambil('beranda.statistik.satuan_jiwa')],
                [Teks::ambil('beranda.statistik.label_perempuan'), number_format($statistik['perempuan'], 0, ',', '.'), Teks::ambil('beranda.statistik.satuan_jiwa')],
            ] as [$label, $nilai, $satuan])
                @if ($nilai !== null)
                    <div class="text-center">
                        <p class="text-3xl font-semibold text-on-surface sm:text-4xl">{{ $nilai }}</p>
                        <p class="mt-1 text-sm font-medium text-on-surface">{{ $label }}</p>
                        <p class="text-xs text-on-surface-variant">{{ $satuan }}</p>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('publik.statistik') }}"
               class="text-sm font-medium text-primary hover:text-primary-container">
                {{ Teks::ambil('beranda.statistik.tautan') }}
            </a>
        </div>
    </div>
</section>

{{-- ========================== SEKILAS DESA ========================== --}}
@if (filled($desa->visi) || filled($desa->sejarah))
    <section id="profil" class="py-16">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-2">
            <div>
                <h2 class="text-2xl font-semibold text-on-surface">{{ Teks::ambil('beranda.sekilas.prefix') }} {{ $desa->nama_desa }}</h2>
                <p class="mt-4 leading-relaxed text-on-surface-variant">
                    {{ Str::limit(strip_tags($desa->sejarah ?: $desa->visi), Teks::angka('kuota.beranda.limit_sekilas')) }}
                </p>
                <a href="{{ route('publik.profil') }}"
                   class="mt-6 inline-block rounded-[12px] border border-outline-variant px-5 py-2.5 text-sm font-medium text-on-surface transition hover:bg-surface-container-low">
                    {{ Teks::ambil('beranda.sekilas.cta') }}
                </a>
            </div>

            @if ($desa->misiSebagaiDaftar())
                <div class="rounded-[12px] bg-surface-container-low p-6">
                    <h3 class="mb-4 text-sm font-semibold tracking-wider text-on-surface-variant uppercase">{{ Teks::ambil('beranda.sekilas.judul_misi') }}</h3>
                    <ol class="list-decimal space-y-2 pl-5 text-sm text-on-surface">
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
    <section id="potensi" class="border-t border-outline-variant py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-on-surface">{{ Teks::ambil('beranda.potensi.judul') }}</h2>
                    <p class="mt-1.5 text-on-surface-variant">{{ Teks::ambil('beranda.potensi.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.potensi.index') }}"
                   class="text-sm font-medium text-primary hover:text-primary-container">{{ Teks::ambil('beranda.potensi.tautan') }}</a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($potensi as $item)
                    <a href="{{ route('publik.potensi.show', $item) }}"
                       class="group overflow-hidden rounded-[12px] border border-outline-variant transition hover:border-outline-variant hover:shadow-md">
                        <div class="aspect-[4/3] overflow-hidden bg-slate-100">
                            <img src="{{ $item->urlFotoKecil() }}" alt="{{ $item->nama }}" loading="lazy"
                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        </div>
                        <div class="p-5">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $item->kategori->warna() }}">
                                {{ $item->kategori->label() }}
                            </span>
                            <h3 class="mt-3 font-semibold text-on-surface group-hover:text-primary">{{ $item->nama }}</h3>
                            <p class="mt-1.5 line-clamp-2 text-sm text-on-surface-variant">{{ $item->ringkasan() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ============================= BERITA ============================= --}}
@if ($beritaUtama)
    <section id="berita" class="border-t border-outline-variant bg-surface-container-low py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-on-surface">{{ Teks::ambil('beranda.berita.judul') }}</h2>
                    <p class="mt-1.5 text-on-surface-variant">{{ Teks::ambil('beranda.berita.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.berita.index') }}"
                   class="text-sm font-medium text-primary hover:text-primary-container">{{ Teks::ambil('beranda.berita.tautan') }}</a>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <a href="{{ route('publik.berita.show', $beritaUtama) }}"
                   class="group overflow-hidden rounded-[12px] bg-surface-container-lowest transition hover:shadow-md">
                    <div class="aspect-[16/9] overflow-hidden bg-slate-100">
                        <img src="{{ $beritaUtama->urlSampul() }}" alt="{{ $beritaUtama->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    </div>
                    <div class="p-6">
                        @if ($beritaUtama->kategori)
                            <span class="rounded-full bg-primary-fixed/20 px-2.5 py-1 text-xs font-medium text-primary">
                                {{ $beritaUtama->kategori->nama }}
                            </span>
                        @endif
                        <h3 class="mt-3 text-xl font-semibold text-on-surface group-hover:text-primary">
                            {{ $beritaUtama->judul }}
                        </h3>
                        <p class="mt-2 line-clamp-2 text-sm text-on-surface-variant">{{ $beritaUtama->ringkasanTampil() }}</p>
                        <p class="mt-3 text-xs text-on-surface-variant">
                            {{ $beritaUtama->tanggal_terbit?->translatedFormat('d F Y') }}
                        </p>
                    </div>
                </a>

                <div class="space-y-4">
                    @foreach ($berita->where('id', '!=', $beritaUtama->id)->take(Teks::angka('kuota.beranda.berita_samping')) as $item)
                        <a href="{{ route('publik.berita.show', $item) }}"
                           class="group flex gap-4 rounded-[12px] bg-surface-container-lowest p-4 transition hover:shadow-md">
                            <img src="{{ $item->urlSampulKecil() }}" alt="" loading="lazy"
                                 class="h-20 w-28 shrink-0 rounded-[12px] bg-slate-100 object-cover">
                            <div class="min-w-0">
                                <h3 class="line-clamp-2 font-medium text-on-surface group-hover:text-primary">
                                    {{ $item->judul }}
                                </h3>
                                <p class="mt-1 text-xs text-on-surface-variant">
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
    <section id="galeri" class="border-t border-outline-variant py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-on-surface">{{ Teks::ambil('beranda.galeri.judul') }}</h2>
                    <p class="mt-1.5 text-on-surface-variant">{{ Teks::ambil('beranda.galeri.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.galeri.index') }}"
                   class="text-sm font-medium text-primary hover:text-primary-container">{{ Teks::ambil('beranda.galeri.tautan') }}</a>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                @foreach ($acara as $item)
                    <a href="{{ route('publik.galeri.show', $item) }}"
                       class="group relative aspect-[4/3] overflow-hidden rounded-[12px] bg-slate-100">
                        <img src="{{ $item->urlSampulKecil() }}" alt="{{ $item->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-900/85 to-transparent p-4">
                            <p class="line-clamp-2 text-sm font-medium text-white">{{ $item->judul }}</p>
                            <p class="mt-0.5 text-xs text-white/70">
                                {{ $item->tanggal_acara->translatedFormat('d M Y') }}
                                @if ($item->foto_count > 1) · {{ $item->foto_count }} {{ Teks::ambil('beranda.galeri.suffix_foto') }} @endif
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
    <section class="border-t border-outline-variant bg-surface-container-low py-16">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-2">

            @if ($fasilitas->isNotEmpty())
                <div>
                    <div class="mb-5 flex items-end justify-between gap-3">
                        <h2 class="text-2xl font-semibold text-on-surface">{{ Teks::ambil('beranda.fasilitas.judul') }}</h2>
                        <a href="{{ route('publik.fasilitas') }}"
                           class="text-sm font-medium text-primary hover:text-primary-container">{{ Teks::ambil('beranda.tautan_lihat_semua') }}</a>
                    </div>
                    <div class="space-y-3">
                        @foreach ($fasilitas as $item)
                            <div class="flex items-center gap-4 rounded-[12px] bg-surface-container-lowest p-4">
                                <img src="{{ $item->urlFotoKecil() }}" alt="" loading="lazy"
                                     class="h-14 w-14 shrink-0 rounded-[12px] bg-slate-100 object-cover">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-on-surface">{{ $item->nama }}</p>
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
                        <h2 class="text-2xl font-semibold text-on-surface">{{ Teks::ambil('beranda.prestasi.judul') }}</h2>
                        <a href="{{ route('publik.prestasi') }}"
                           class="text-sm font-medium text-primary hover:text-primary-container">{{ Teks::ambil('beranda.tautan_lihat_semua') }}</a>
                    </div>
                    <div class="space-y-3">
                        @foreach ($prestasi as $item)
                            <div class="rounded-[12px] bg-surface-container-lowest p-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-on-surface tabular-nums">{{ $item->tahun }}</span>
                                    @if ($item->tingkat)
                                        <span class="rounded-full px-2 py-0.5 text-xs {{ $item->tingkat->warna() }}">
                                            {{ $item->tingkat->label() }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-1 font-medium text-on-surface">{{ $item->judul }}</p>
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
    <section id="pengurus" class="border-t border-outline-variant py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-on-surface">{{ Teks::ambil('beranda.pengurus.judul') }}</h2>
                    <p class="mt-1.5 text-on-surface-variant">{{ Teks::ambil('beranda.pengurus.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.struktur') }}"
                   class="text-sm font-medium text-primary hover:text-primary-container">{{ Teks::ambil('beranda.pengurus.tautan') }}</a>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($pengurus as $orang)
                    <div class="rounded-[12px] border border-outline-variant p-5 text-center">
                        <img src="{{ $orang->urlFotoKecil() }}" alt="Foto {{ $orang->nama }}" loading="lazy"
                             class="mx-auto h-24 w-24 rounded-full bg-slate-100 object-cover">
                        <p class="mt-3 font-semibold text-on-surface">{{ $orang->nama }}</p>
                        <p class="text-sm text-primary">{{ $orang->jabatan }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ============================= KONTAK ============================= --}}
@if ($desa->punyaKontak() || filled($desa->peta_embed))
    <section id="kontak" class="border-t border-outline-variant bg-surface-container-low py-16">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-2">
            <div>
                <h2 class="text-2xl font-semibold text-on-surface">{{ Teks::ambil('beranda.kontak.judul') }}</h2>
                <p class="mt-2 text-on-surface-variant">
                    {{ Teks::ambil('beranda.kontak.kalimat', ['{nama}' => $desa->nama_desa]) }}
                </p>

                <dl class="mt-6 space-y-3 text-sm">
                    @foreach ([
                        Teks::ambil('beranda.kontak.label_alamat') => $desa->alamat_kantor,
                        Teks::ambil('beranda.kontak.label_telepon') => $desa->telepon_kantor,
                        Teks::ambil('beranda.kontak.label_email') => $desa->email,
                    ] as $label => $nilai)
                        @if (filled($nilai))
                            <div class="flex gap-3">
                                <dt class="w-20 shrink-0 text-on-surface-variant">{{ $label }}</dt>
                                <dd class="text-slate-800">{{ $nilai }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>

            @if (filled($desa->peta_embed))
                <div class="overflow-hidden rounded-[12px] ring-1 ring-outline-variant">
                    <iframe src="{{ $desa->peta_embed }}" class="h-72 w-full" style="border:0"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            title="Peta lokasi {{ $desa->nama_desa }}"></iframe>
                </div>
            @endif
        </div>
    </section>
@endif

@endsection
