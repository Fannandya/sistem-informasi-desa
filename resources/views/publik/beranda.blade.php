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
<section class="relative w-full h-[600px] md:h-[819px] flex items-center justify-center overflow-hidden bg-on-surface"
         x-data="carousel()" x-init="init()">
    {{-- Carousel images --}}
    <div class="absolute inset-0 overflow-hidden">
        @php $layanan = app(\App\Services\LayananUnggahGambar::class); @endphp
        @forelse ($carousel as $item)
            <img :src="@js($layanan->url($item->path))"
                 alt="{{ $item->judul }}"
                 x-show="currentIndex === {{ $loop->index }}"
                 class="h-full w-full object-cover transition-opacity duration-1000">
        @empty
            @if (filled($desa->foto_sampul))
                <img src="{{ $desa->urlSampul() }}" alt=""
                     class="h-full w-full object-cover">
            @endif
        @endforelse
    </div>
    <div class="absolute inset-0 bg-primary/60 mix-blend-multiply"></div>

    {{-- Navigation indicators --}}
    @if ($carousel->isNotEmpty())
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex gap-2">
            @foreach ($carousel as $item)
                <button type="button"
                        @click="goToSlide({{ $loop->index }})"
                        :class="currentIndex === {{ $loop->index }} ? 'w-8 bg-white' : 'w-2 bg-white/50'"
                        class="h-2 rounded-full transition-all duration-300"
                        :aria-label="'Slide ' + {{ $loop->index + 1 }}">
                </button>
            @endforeach
        </div>
    @endif

    <div class="relative z-10 text-center px-6 max-w-4xl mx-auto">
        @if ($desa->wilayahLengkap())
            <p class="text-sm font-medium tracking-wider text-on-primary/80 uppercase mb-4 drop-shadow">
                {{ $desa->wilayahLengkap() }}
            </p>
        @endif

        <h1 class="font-display-lg text-4xl md:text-5xl font-bold text-on-primary mb-6 drop-shadow-md leading-tight">
            {{ Teks::ambil('beranda.hero.sapaan') }}<br>{{ $desa->nama_desa }}
        </h1>

        @if (filled($desa->meta_description) || filled($desa->visi))
            <p class="font-body-lg text-lg text-on-primary/90 mb-10 drop-shadow max-w-2xl mx-auto leading-relaxed">
                {{ Str::limit($desa->meta_description ?: $desa->visi, Teks::angka('kuota.beranda.limit_sapaan')) }}
            </p>
        @endif

        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('publik.profil') }}"
               class="bg-secondary text-on-secondary px-8 py-3 rounded-lg font-label-md text-sm hover:bg-surface-tint transition-all shadow-lg hover:-translate-y-1">
                {{ Teks::ambil('beranda.hero.cta_profil') }}
            </a>
            <a href="#potensi"
               class="border-2 border-on-primary/50 text-on-primary px-8 py-3 rounded-lg font-label-md text-sm hover:bg-on-primary/10 transition-all shadow-lg hover:-translate-y-1 backdrop-blur-sm">
                {{ Teks::ambil('beranda.hero.cta_potensi') }}
            </a>
        </div>
    </div>
</section>

@push('skrip')
<script>
function carousel() {
    return {
        currentIndex: 0,
        autoplayInterval: null,
        totalSlides: {{ $carousel->count() ?? 0 }},

        init() {
            if (this.totalSlides > 1) {
                this.startAutoplay();
                // Clear and restart autoplay on user interaction
                this.$el.addEventListener('click', () => {
                    clearInterval(this.autoplayInterval);
                    this.startAutoplay();
                });
            }
        },

        startAutoplay() {
            this.autoplayInterval = setInterval(() => {
                this.currentIndex = (this.currentIndex + 1) % this.totalSlides;
            }, 4000); // 4 seconds
        },

        goToSlide(index) {
            this.currentIndex = index;
            clearInterval(this.autoplayInterval);
            this.startAutoplay();
        }
    };
}
</script>
@endpush

{{-- ========================== SEKILAS DESA ========================== --}}
@if (filled($desa->visi) || filled($desa->sejarah))
    <section id="profil" class="py-16 px-6 md:px-12 bg-surface">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
            <div>
                <h2 class="font-headline-lg text-3xl font-bold text-primary mb-4">{{ Teks::ambil('beranda.sekilas.prefix') }} {{ $desa->nama_desa }}</h2>
                <div class="w-16 h-1 bg-secondary rounded-full mb-6"></div>
                <p class="font-body-md text-base text-on-surface-variant mb-8 leading-relaxed">
                    {{ Str::limit(strip_tags($desa->sejarah ?: $desa->visi), Teks::angka('kuota.beranda.limit_sekilas')) }}
                </p>
                <a href="{{ route('publik.profil') }}"
                   class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-label-md text-sm hover:bg-primary hover:text-on-primary transition-colors inline-flex items-center gap-2">
                    {{ Teks::ambil('beranda.sekilas.cta') }}
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            @if ($desa->misiSebagaiDaftar())
                <div class="relative rounded-xl overflow-hidden shadow-[0px_10px_30px_rgba(0,0,0,0.1)] bg-surface-container-lowest p-8 border border-outline-variant/30">
                    <h3 class="mb-4 text-sm font-semibold tracking-wider text-primary uppercase">{{ Teks::ambil('beranda.sekilas.judul_misi') }}</h3>
                    <ol class="list-decimal space-y-3 pl-5 text-sm text-on-surface-variant leading-relaxed">
                        @foreach (array_slice($desa->misiSebagaiDaftar(), 0, 4) as $misi)
                            <li>{{ $misi }}</li>
                        @endforeach
                    </ol>
                </div>
            @else
                <div class="relative h-96 rounded-xl overflow-hidden shadow-[0px_10px_30px_rgba(0,0,0,0.1)] bg-primary-container/10 flex items-center justify-center border border-outline-variant/30">
                    <span class="material-symbols-outlined text-6xl text-primary/30">landscape</span>
                </div>
            @endif
        </div>
    </section>
@endif

{{-- ============================ STATISTIK ============================ --}}
<section id="statistik" class="py-16 px-6 md:px-12 bg-surface-container-lowest">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="font-headline-lg text-3xl font-bold text-primary mb-4">Data Demografi</h2>
            <p class="font-body-md text-base text-on-surface-variant">Gambaran umum kependudukan desa secara aktual.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $statData = [
                    ['icon' => 'group', 'color' => 'bg-primary-fixed text-on-primary-fixed', 'label' => Teks::ambil('beranda.statistik.label_penduduk'), 'nilai' => number_format($statistik['jumlah_penduduk'], 0, ',', '.'), 'satuan' => Teks::ambil('beranda.statistik.satuan_jiwa')],
                    ['icon' => 'family_restroom', 'color' => 'bg-secondary-fixed text-on-secondary-fixed', 'label' => Teks::ambil('beranda.statistik.label_kk'), 'nilai' => $statistik['jumlah_kk'] !== null ? number_format($statistik['jumlah_kk'], 0, ',', '.') : null, 'satuan' => Teks::ambil('beranda.statistik.satuan_keluarga')],
                    ['icon' => 'man', 'color' => 'bg-tertiary-fixed text-on-tertiary-fixed', 'label' => Teks::ambil('beranda.statistik.label_laki'), 'nilai' => number_format($statistik['laki'], 0, ',', '.'), 'satuan' => Teks::ambil('beranda.statistik.satuan_jiwa')],
                    ['icon' => 'woman', 'color' => 'bg-error-container text-on-error-container', 'label' => Teks::ambil('beranda.statistik.label_perempuan'), 'nilai' => number_format($statistik['perempuan'], 0, ',', '.'), 'satuan' => Teks::ambil('beranda.statistik.satuan_jiwa')],
                ];
            @endphp
            @foreach ($statData as $stat)
                @if ($stat['nilai'] !== null)
                    <div class="bg-surface-container-lowest/80 backdrop-blur-md border border-outline-variant/30 shadow-[0px_4px_20px_rgba(0,0,0,0.05)] p-8 rounded-xl text-center hover:-translate-y-2 transition-transform duration-300">
                        <div class="w-16 h-16 mx-auto {{ $stat['color'] }} rounded-full flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-3xl">{{ $stat['icon'] }}</span>
                        </div>
                        <div class="font-display-lg text-4xl font-bold text-primary mb-2">{{ $stat['nilai'] }}</div>
                        <div class="font-label-md text-sm text-on-surface-variant">{{ $stat['label'] }}</div>
                    </div>
                @endif
            @endforeach
        </div>
        <div class="mt-10 text-center">
            <a href="{{ route('publik.statistik') }}"
               class="text-sm font-medium text-primary underline hover:text-primary-container transition-colors">
                {{ Teks::ambil('beranda.statistik.tautan') }}
            </a>
        </div>
    </div>
</section>

{{-- ============================ POTENSI ============================ --}}
@if ($potensi->isNotEmpty())
    <section id="potensi" class="py-16 px-6 md:px-12 bg-surface border-t border-outline-variant/30">
        <div class="max-w-7xl mx-auto">
            <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="font-headline-lg text-3xl font-bold text-primary">{{ Teks::ambil('beranda.potensi.judul') }}</h2>
                    <p class="mt-2 text-on-surface-variant">{{ Teks::ambil('beranda.potensi.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.potensi.index') }}"
                   class="text-sm font-medium text-primary hover:text-primary-container underline transition-colors">{{ Teks::ambil('beranda.potensi.tautan') }}</a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($potensi as $item)
                    <a href="{{ route('publik.potensi.show', $item) }}"
                       class="group overflow-hidden rounded-xl border border-outline-variant/30 bg-surface-container-lowest shadow-[0px_4px_20px_rgba(0,0,0,0.03)] transition-all hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] hover:-translate-y-1">
                        <div class="aspect-[4/3] overflow-hidden bg-surface-container-highest">
                            <img src="{{ $item->urlFotoKecil() }}" alt="{{ $item->nama }}" loading="lazy"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                        </div>
                        <div class="p-6">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item->kategori->warna() }}">
                                {{ $item->kategori->label() }}
                            </span>
                            <h3 class="mt-4 text-xl font-bold text-on-surface group-hover:text-primary transition-colors">{{ $item->nama }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm text-on-surface-variant">{{ $item->ringkasan() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ============================= BERITA ============================= --}}
@if ($beritaUtama)
    <section id="berita" class="py-16 px-6 md:px-12 bg-surface-container-lowest border-t border-outline-variant/30">
        <div class="max-w-7xl mx-auto">
            <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="font-headline-lg text-3xl font-bold text-primary">{{ Teks::ambil('beranda.berita.judul') }}</h2>
                    <p class="mt-2 text-on-surface-variant">{{ Teks::ambil('beranda.berita.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.berita.index') }}"
                   class="text-sm font-medium text-primary hover:text-primary-container underline transition-colors">{{ Teks::ambil('beranda.berita.tautan') }}</a>
            </div>

            <div class="grid gap-8 lg:grid-cols-2">
                <a href="{{ route('publik.berita.show', $beritaUtama) }}"
                   class="group overflow-hidden rounded-xl bg-surface border border-outline-variant/30 shadow-[0px_4px_20px_rgba(0,0,0,0.03)] transition-all hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] hover:-translate-y-1">
                    <div class="aspect-[16/9] overflow-hidden bg-surface-container-highest">
                        <img src="{{ $beritaUtama->urlSampul() }}" alt="{{ $beritaUtama->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                    </div>
                    <div class="p-8">
                        @if ($beritaUtama->kategori)
                            <span class="rounded-full bg-primary-container text-on-primary-container px-3 py-1 text-xs font-semibold">
                                {{ $beritaUtama->kategori->nama }}
                            </span>
                        @endif
                        <h3 class="mt-4 text-2xl font-bold text-on-surface group-hover:text-primary transition-colors leading-snug">
                            {{ $beritaUtama->judul }}
                        </h3>
                        <p class="mt-3 line-clamp-2 text-base text-on-surface-variant leading-relaxed">{{ $beritaUtama->ringkasanTampil() }}</p>
                        <div class="mt-5 flex items-center text-xs text-on-surface-variant font-medium">
                            <span class="material-symbols-outlined text-[16px] mr-1">calendar_today</span>
                            {{ $beritaUtama->tanggal_terbit?->translatedFormat('d F Y') }}
                        </div>
                    </div>
                </a>

                <div class="space-y-4">
                    @foreach ($berita->where('id', '!=', $beritaUtama->id)->take(Teks::angka('kuota.beranda.berita_samping')) as $item)
                        <a href="{{ route('publik.berita.show', $item) }}"
                           class="group flex flex-col sm:flex-row gap-5 rounded-xl border border-outline-variant/30 bg-surface p-4 shadow-[0px_4px_20px_rgba(0,0,0,0.03)] transition-all hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] hover:-translate-x-1">
                            <div class="h-32 sm:h-28 w-full sm:w-36 shrink-0 overflow-hidden rounded-lg bg-surface-container-highest">
                                <img src="{{ $item->urlSampulKecil() }}" alt="" loading="lazy"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                            </div>
                            <div class="min-w-0 flex flex-col justify-center py-1">
                                <h3 class="line-clamp-2 text-lg font-bold text-on-surface group-hover:text-primary transition-colors leading-tight">
                                    {{ $item->judul }}
                                </h3>
                                <div class="mt-3 flex items-center text-xs text-on-surface-variant font-medium">
                                    <span class="material-symbols-outlined text-[14px] mr-1">calendar_today</span>
                                    {{ $item->tanggal_terbit?->translatedFormat('d F Y') }}
                                </div>
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
    <section id="galeri" class="py-16 px-6 md:px-12 bg-surface border-t border-outline-variant/30">
        <div class="max-w-7xl mx-auto">
            <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="font-headline-lg text-3xl font-bold text-primary">{{ Teks::ambil('beranda.galeri.judul') }}</h2>
                    <p class="mt-2 text-on-surface-variant">{{ Teks::ambil('beranda.galeri.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.galeri.index') }}"
                   class="text-sm font-medium text-primary hover:text-primary-container underline transition-colors">{{ Teks::ambil('beranda.galeri.tautan') }}</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($acara as $item)
                    <a href="{{ route('publik.galeri.show', $item) }}"
                       class="group relative aspect-[4/3] overflow-hidden rounded-xl bg-surface-container-highest shadow-[0px_4px_20px_rgba(0,0,0,0.05)]">
                        <img src="{{ $item->urlSampulKecil() }}" alt="{{ $item->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-inverse-surface/90 to-transparent pt-12 pb-5 px-5">
                            <p class="line-clamp-2 text-lg font-bold text-inverse-on-surface">{{ $item->judul }}</p>
                            <p class="mt-1 flex items-center text-xs text-inverse-on-surface/80 font-medium">
                                <span class="material-symbols-outlined text-[14px] mr-1">event</span>
                                {{ $item->tanggal_acara->translatedFormat('d M Y') }}
                                @if ($item->foto_count > 1) 
                                    <span class="mx-2">•</span>
                                    <span class="material-symbols-outlined text-[14px] mr-1">photo_library</span>
                                    {{ $item->foto_count }} {{ Teks::ambil('beranda.galeri.suffix_foto') }} 
                                @endif
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
    <section class="py-16 px-6 md:px-12 bg-surface-container-lowest border-t border-outline-variant/30">
        <div class="max-w-7xl mx-auto grid gap-12 lg:grid-cols-2">

            @if ($fasilitas->isNotEmpty())
                <div>
                    <div class="mb-8 flex items-end justify-between gap-4">
                        <h2 class="font-headline-lg text-3xl font-bold text-primary">{{ Teks::ambil('beranda.fasilitas.judul') }}</h2>
                        <a href="{{ route('publik.fasilitas') }}"
                           class="text-sm font-medium text-primary hover:text-primary-container underline">{{ Teks::ambil('beranda.tautan_lihat_semua') }}</a>
                    </div>
                    <div class="space-y-4">
                        @foreach ($fasilitas as $item)
                            <div class="flex items-center gap-5 rounded-xl border border-outline-variant/30 bg-surface p-4 shadow-[0px_4px_15px_rgba(0,0,0,0.02)] transition-colors hover:border-primary/50">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-surface-container-highest">
                                    <img src="{{ $item->urlFotoKecil() }}" alt="" loading="lazy"
                                         class="h-full w-full object-cover">
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-lg font-bold text-on-surface">{{ $item->nama }}</p>
                                    <span class="mt-1 inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $item->kategori->warna() }}">
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
                    <div class="mb-8 flex items-end justify-between gap-4">
                        <h2 class="font-headline-lg text-3xl font-bold text-primary">{{ Teks::ambil('beranda.prestasi.judul') }}</h2>
                        <a href="{{ route('publik.prestasi') }}"
                           class="text-sm font-medium text-primary hover:text-primary-container underline">{{ Teks::ambil('beranda.tautan_lihat_semua') }}</a>
                    </div>
                    <div class="space-y-4">
                        @foreach ($prestasi as $item)
                            <div class="rounded-xl border border-outline-variant/30 bg-surface p-5 shadow-[0px_4px_15px_rgba(0,0,0,0.02)] border-l-4 border-l-tertiary transition-colors hover:bg-surface-container-low">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="font-bold text-tertiary tabular-nums">{{ $item->tahun }}</span>
                                    @if ($item->tingkat)
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $item->tingkat->warna() }}">
                                            {{ $item->tingkat->label() }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-2 text-lg font-bold text-on-surface leading-snug">{{ $item->judul }}</p>
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
    <section id="pengurus" class="py-16 px-6 md:px-12 bg-surface border-t border-outline-variant/30">
        <div class="max-w-7xl mx-auto">
            <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="font-headline-lg text-3xl font-bold text-primary">{{ Teks::ambil('beranda.pengurus.judul') }}</h2>
                    <p class="mt-2 text-on-surface-variant">{{ Teks::ambil('beranda.pengurus.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.struktur') }}"
                   class="text-sm font-medium text-primary hover:text-primary-container underline">{{ Teks::ambil('beranda.pengurus.tautan') }}</a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($pengurus as $orang)
                    <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-6 text-center shadow-[0px_4px_20px_rgba(0,0,0,0.03)] hover:-translate-y-1 transition-transform">
                        <div class="mx-auto h-28 w-28 overflow-hidden rounded-full border-4 border-surface p-1 shadow-sm">
                            <img src="{{ $orang->urlFotoKecil() }}" alt="Foto {{ $orang->nama }}" loading="lazy"
                                 class="h-full w-full rounded-full bg-surface-container-highest object-cover">
                        </div>
                        <p class="mt-4 text-lg font-bold text-on-surface">{{ $orang->nama }}</p>
                        <p class="mt-1 text-sm font-medium text-primary">{{ $orang->jabatan }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@endsection
