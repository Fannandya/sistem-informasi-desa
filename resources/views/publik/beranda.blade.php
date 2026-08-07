@extends('publik.layouts.app')

@section('judul', 'Beranda')
@section('deskripsi', $desa->deskripsiMeta())
@section('footer_margin', 'mt-0')

@php
    use App\Support\Teks;

    // Setiap bagian hanya dirender bila datanya ada, supaya tidak ada judul
    // bagian yang menggantung tanpa isi (PRD fitur 14).
    $seksi = fn ($kondisi) => $kondisi;
@endphp

@section('konten')

{{-- ============================== HERO ============================== --}}
{{-- -mt-16 menarik hero naik ke belakang header (h-16) yang transparan di
     beranda, supaya gambar hero benar-benar terlihat di balik navbar. --}}
<section class="relative -mt-16 w-full h-[600px] md:h-[819px] flex items-center justify-center overflow-hidden bg-on-surface"
         style="--hero-text-color: {{ $desa->hero_text_color ?? '#ffffff' }};"
         x-data="carousel()" x-init="init()">
    {{-- Carousel images --}}
    <div class="absolute inset-0 overflow-hidden">
        @php $layanan = app(\App\Services\LayananUnggahGambar::class); @endphp
        @forelse ($carousel as $item)
            <img src="{{ $layanan->url($item->path) }}"
                 alt="{{ $item->judul }}"
                 :class="{ 'slide-aktif': currentIndex === {{ $loop->index }} }"
                 class="slide-carousel absolute inset-0 h-full w-full object-cover">
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
            }, 5000);
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
@if (filled($desa->visi) || filled($desa->sejarah) || filled($desa->foto_sampul))
    <section id="profil" data-fade-in class="pt-20 md:pt-28 pb-12 md:pb-16 px-6 md:px-12 bg-surface">
        <div class="max-w-5xl mx-auto">
            <div class="text-center mb-10">
                <h2 class="font-headline-lg text-3xl font-bold text-primary mb-4">{{ Teks::ambil('beranda.sekilas.prefix') }} {{ $desa->nama_desa }}</h2>
                <div class="w-16 h-1 bg-secondary rounded-full mx-auto mb-6"></div>
                @if (filled($desa->sejarah))
                    <p class="font-body-md text-base text-on-surface-variant leading-relaxed max-w-3xl mx-auto">
                        {{ Str::limit(strip_tags($desa->sejarah), Teks::angka('kuota.beranda.limit_sekilas')) }}
                    </p>
                @endif
            </div>

            @if (filled($desa->foto_sampul))
                <div class="rounded-[12px] overflow-hidden shadow-[0px_10px_30px_rgba(0,0,0,0.1)] border border-outline-variant/30 mb-6">
                    <img src="{{ $desa->urlSampul() }}" alt="{{ $desa->nama_desa }}" loading="lazy"
                         class="w-full h-64 md:h-96 object-cover">
                </div>
            @endif

            @if (filled($desa->visi))
                <div class="rounded-[12px] bg-primary-container/20 border border-primary/20 p-6 md:p-8 mb-8">
                    <h3 class="mb-3 text-xs font-semibold tracking-wider text-primary uppercase">{{ Teks::ambil('beranda.sekilas.judul_visi') }}</h3>
                    <p class="font-body-md text-base md:text-lg text-on-surface italic leading-relaxed">&ldquo;{{ $desa->visi }}&rdquo;</p>
                </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @if (filled($desa->tahun_berdiri))
                    <div class="text-center rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest p-5">
                        <div class="text-2xl font-bold text-primary">{{ $desa->tahun_berdiri }}</div>
                        <div class="mt-1 text-xs font-medium text-on-surface-variant">{{ Teks::ambil('beranda.sekilas.label_tahun_berdiri') }}</div>
                    </div>
                @endif
                <div class="text-center rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest p-5">
                    <div class="text-2xl font-bold text-primary">{{ $statistik['jumlah_dusun'] }} {{ Teks::ambil('beranda.sekilas.satuan_dusun') }}</div>
                    <div class="mt-1 text-xs font-medium text-on-surface-variant">{{ Teks::ambil('beranda.sekilas.label_jumlah_dusun') }}</div>
                </div>
                <div class="text-center rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest p-5">
                    <div class="text-2xl font-bold text-primary">{{ $desa->kecamatan ?? '—' }}</div>
                    <div class="mt-1 text-xs font-medium text-on-surface-variant">{{ Teks::ambil('beranda.sekilas.label_kecamatan') }}</div>
                </div>
                @if (filled($desa->kode_pos))
                    <div class="text-center rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest p-5">
                        <div class="text-2xl font-bold text-primary">{{ $desa->kode_pos }}</div>
                        <div class="mt-1 text-xs font-medium text-on-surface-variant">{{ Teks::ambil('beranda.sekilas.label_kode_pos') }}</div>
                    </div>
                @endif
            </div>

            <div class="text-center mt-10">
                <a href="{{ route('publik.profil') }}"
                   class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-label-md text-sm hover:bg-primary hover:text-on-primary transition-colors inline-flex items-center gap-2">
                    {{ Teks::ambil('beranda.sekilas.cta') }}
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </div>
    </section>
@endif

{{-- ============================ STATISTIK ============================ --}}
<section id="statistik" data-fade-in class="py-12 md:py-16 px-6 md:px-12 bg-surface-container-lowest">
    <div class="max-w-7xl mx-auto">
        <div class="mb-10">
            <h2 class="font-headline-lg text-3xl font-bold text-primary mb-2">{{ Teks::ambil('beranda.statistik.label_penduduk') }}</h2>
            <p class="text-xs text-on-surface-variant font-medium md:hidden">{{ Teks::ambil('beranda.statistik.hint_geser') }}</p>
        </div>
        <div class="overflow-x-auto snap-x snap-mandatory scrollbar-hide md:overflow-visible md:grid md:grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 md:gap-6 flex gap-4">
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
                    <div class="snap-start shrink-0 w-[160px] md:w-full bg-surface-container-lowest/80 text-on-surface border border-outline-variant/30 backdrop-blur-md shadow-[0px_4px_20px_rgba(0,0,0,0.05)] p-6 rounded-[12px] text-center hover:-translate-y-2 transition-transform duration-300">
                        <div class="w-12 h-12 mx-auto {{ $stat['color'] }} rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-2xl">{{ $stat['icon'] }}</span>
                        </div>
                        <div class="font-display-lg text-3xl font-bold mb-1">{{ $stat['nilai'] }}</div>
                        <div class="font-label-md text-xs">{{ $stat['label'] }}</div>
                    </div>
                @endif
            @endforeach
        </div>
        <div class="mt-8 md:mt-10 text-center">
            <a href="{{ route('publik.statistik') }}"
               class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-label-md text-sm hover:bg-primary hover:text-on-primary transition-colors inline-flex items-center gap-2">
                {{ Teks::ambil('beranda.statistik.tautan') }}
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>
</section>

{{-- ============================ POTENSI ============================ --}}
@if ($potensi->isNotEmpty())
    <section id="potensi" data-fade-in class="py-12 md:py-16 px-6 md:px-12 bg-surface border-t border-outline-variant/30">
        <div class="max-w-7xl mx-auto">
            <div class="mb-8 md:mb-10 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="font-headline-lg text-3xl font-bold text-primary">{{ Teks::ambil('beranda.potensi.judul') }}</h2>
                    <p class="mt-2 text-on-surface-variant">{{ Teks::ambil('beranda.potensi.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.potensi.index') }}"
                   class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-label-md text-sm hover:bg-primary hover:text-on-primary transition-colors inline-flex items-center gap-2">
                    {{ Teks::ambil('beranda.potensi.tautan') }}
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="overflow-x-auto snap-x snap-mandatory scrollbar-hide md:overflow-visible md:grid md:gap-6 md:grid-cols-2 lg:grid-cols-3 flex gap-4">
                @foreach ($potensi as $item)
                    <a href="{{ route('publik.potensi.show', $item) }}"
                       class="snap-start shrink-0 w-[240px] md:w-full group overflow-hidden rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest shadow-[0px_4px_20px_rgba(0,0,0,0.03)] transition-all hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] hover:-translate-y-1">
                        <div class="aspect-[4/3] overflow-hidden bg-surface-container-highest">
                            <img src="{{ $item->urlFotoKecil() }}" alt="{{ $item->nama }}" loading="lazy"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                        </div>
                        <div class="p-6">
                            <span class="badge-kategori">
                                {{ $item->kategori?->nama ?? 'Lainnya' }}
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
    <section id="berita" data-fade-in class="py-12 md:py-16 px-6 md:px-12 bg-surface-container-lowest border-t border-outline-variant/30">
        <div class="max-w-7xl mx-auto">
            <div class="mb-8 md:mb-10 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="font-headline-lg text-3xl font-bold text-primary">{{ Teks::ambil('beranda.berita.judul') }}</h2>
                    <p class="mt-2 text-on-surface-variant">{{ Teks::ambil('beranda.berita.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.berita.index') }}"
                   class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-label-md text-sm hover:bg-primary hover:text-on-primary transition-colors inline-flex items-center gap-2">
                    {{ Teks::ambil('beranda.berita.tautan') }}
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="overflow-x-auto snap-x snap-mandatory scrollbar-hide md:overflow-visible md:grid md:gap-8 md:grid-cols-1 lg:grid-cols-2 flex gap-4">
                <a href="{{ route('publik.berita.show', $beritaUtama) }}"
                   class="snap-start shrink-0 w-[300px] md:w-full group overflow-hidden rounded-[12px] bg-surface border border-outline-variant/30 shadow-[0px_4px_20px_rgba(0,0,0,0.03)] transition-all hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] hover:-translate-y-1">
                    <div class="aspect-[16/9] overflow-hidden bg-surface-container-highest">
                        <img src="{{ $beritaUtama->urlSampul() }}" alt="{{ $beritaUtama->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                    </div>
                    <div class="p-6 md:p-8">
                        @if ($beritaUtama->kategori)
                            <span class="badge-kategori">
                                {{ $beritaUtama->kategori->nama }}
                            </span>
                        @endif
                        <h3 class="mt-3 md:mt-4 text-lg md:text-2xl font-bold text-on-surface group-hover:text-primary transition-colors leading-snug">
                            {{ $beritaUtama->judul }}
                        </h3>
                        <p class="mt-2 md:mt-3 line-clamp-2 text-sm md:text-base text-on-surface-variant leading-relaxed">{{ $beritaUtama->ringkasanTampil() }}</p>
                        <div class="mt-4 md:mt-5 flex items-center text-xs text-on-surface-variant font-medium">
                            <span class="material-symbols-outlined text-[16px] mr-1">calendar_today</span>
                            {{ $beritaUtama->tanggal_terbit?->translatedFormat('d F Y') }}
                        </div>
                    </div>
                </a>

                <div class="snap-start shrink-0 w-[300px] md:w-full md:space-y-4 flex flex-col gap-4">
                    @foreach ($berita->where('id', '!=', $beritaUtama->id)->take(Teks::angka('kuota.beranda.berita_samping')) as $item)
                        <a href="{{ route('publik.berita.show', $item) }}"
                           class="group flex flex-col sm:flex-row gap-4 md:gap-5 rounded-[12px] border border-outline-variant/30 bg-surface p-4 shadow-[0px_4px_20px_rgba(0,0,0,0.03)] transition-all hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] hover:-translate-x-1">
                            <div class="h-32 sm:h-28 w-full sm:w-36 shrink-0 overflow-hidden rounded-lg bg-surface-container-highest">
                                <img src="{{ $item->urlSampulKecil() }}" alt="" loading="lazy"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                            </div>
                            <div class="min-w-0 flex flex-col justify-center py-1">
                                <h3 class="line-clamp-2 text-base md:text-lg font-bold text-on-surface group-hover:text-primary transition-colors leading-tight">
                                    {{ $item->judul }}
                                </h3>
                                <div class="mt-2 md:mt-3 flex items-center text-xs text-on-surface-variant font-medium">
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
    <section id="galeri" data-fade-in class="py-12 md:py-16 px-6 md:px-12 bg-surface border-t border-outline-variant/30">
        <div class="max-w-7xl mx-auto">
            <div class="mb-8 md:mb-10 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="font-headline-lg text-3xl font-bold text-primary">{{ Teks::ambil('beranda.galeri.judul') }}</h2>
                    <p class="mt-2 text-on-surface-variant">{{ Teks::ambil('beranda.galeri.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.galeri.index') }}"
                   class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-label-md text-sm hover:bg-primary hover:text-on-primary transition-colors inline-flex items-center gap-2">
                    {{ Teks::ambil('beranda.galeri.tautan') }}
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="overflow-x-auto snap-x snap-mandatory scrollbar-hide md:overflow-visible md:grid md:gap-6 md:grid-cols-2 lg:grid-cols-3 flex gap-4">
                @foreach ($acara as $item)
                    <a href="{{ route('publik.galeri.show', $item) }}"
                       class="snap-start shrink-0 w-[220px] md:w-full group relative aspect-[4/3] overflow-hidden rounded-[12px] bg-surface-container-highest shadow-[0px_4px_20px_rgba(0,0,0,0.05)]">
                        <img src="{{ $item->urlSampulKecil() }}" alt="{{ $item->judul }}" loading="lazy"
                             class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-inverse-surface/90 to-transparent pt-12 pb-5 px-5">
                            <p class="line-clamp-2 text-sm md:text-lg font-bold text-inverse-on-surface">{{ $item->judul }}</p>
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
    <section data-fade-in class="py-12 md:py-16 px-6 md:px-12 bg-surface-container-lowest border-t border-outline-variant/30">
        <div class="max-w-7xl mx-auto grid gap-8 md:gap-12 lg:grid-cols-2">

            @if ($fasilitas->isNotEmpty())
                <div x-data="{ buka: false }" class="lg:block">
                    <button type="button" @click="buka = !buka" class="lg:hidden w-full mb-6 flex items-center justify-between p-4 rounded-[12px] border border-outline-variant/30 bg-surface shadow-[0px_4px_15px_rgba(0,0,0,0.02)] hover:border-primary/50 transition-colors">
                        <h2 class="font-headline-lg text-2xl font-bold text-primary text-left">{{ Teks::ambil('beranda.fasilitas.label_toggle') }}</h2>
                        <span class="material-symbols-outlined text-primary transition-transform" :class="{ 'rotate-180': buka }">expand_more</span>
                    </button>
                    <h2 class="hidden lg:block font-headline-lg text-3xl font-bold text-primary mb-8">{{ Teks::ambil('beranda.fasilitas.judul') }}</h2>
                    <div x-show="buka || window.innerWidth >= 1024" x-cloak class="space-y-4">
                        <div class="flex justify-end mb-4 lg:hidden">
                            <a href="{{ route('publik.fasilitas') }}"
                               class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-label-md text-sm hover:bg-primary hover:text-on-primary transition-colors inline-flex items-center gap-2">
                                {{ Teks::ambil('beranda.tautan_lihat_semua') }}
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                        @foreach ($fasilitas as $item)
                            <div class="flex items-center gap-5 rounded-[12px] border border-outline-variant/30 bg-surface p-4 shadow-[0px_4px_15px_rgba(0,0,0,0.02)] transition-colors hover:border-primary/50">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-surface-container-highest">
                                    <img src="{{ $item->urlFotoKecil() }}" alt="" loading="lazy"
                                         class="h-full w-full object-cover">
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-lg font-bold text-on-surface">{{ $item->nama }}</p>
                                    <span class="mt-1 inline-block rounded-full bg-primary-fixed px-3 py-1 text-xs font-medium tracking-wide text-on-primary-fixed-variant">
                                        {{ $item->kategori->label() }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                        <div class="hidden lg:flex justify-end">
                            <a href="{{ route('publik.fasilitas') }}"
                               class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-label-md text-sm hover:bg-primary hover:text-on-primary transition-colors inline-flex items-center gap-2">
                                {{ Teks::ambil('beranda.tautan_lihat_semua') }}
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if ($prestasi->isNotEmpty())
                <div x-data="{ buka: false }" class="lg:block">
                    <button type="button" @click="buka = !buka" class="lg:hidden w-full mb-6 flex items-center justify-between p-4 rounded-[12px] border border-outline-variant/30 bg-surface shadow-[0px_4px_15px_rgba(0,0,0,0.02)] hover:border-primary/50 transition-colors">
                        <h2 class="font-headline-lg text-2xl font-bold text-primary text-left">{{ Teks::ambil('beranda.prestasi.label_toggle') }}</h2>
                        <span class="material-symbols-outlined text-primary transition-transform" :class="{ 'rotate-180': buka }">expand_more</span>
                    </button>
                    <h2 class="hidden lg:block font-headline-lg text-3xl font-bold text-primary mb-8">{{ Teks::ambil('beranda.prestasi.judul') }}</h2>
                    <div x-show="buka || window.innerWidth >= 1024" x-cloak class="space-y-4">
                        <div class="flex justify-end mb-4 lg:hidden">
                            <a href="{{ route('publik.prestasi') }}"
                               class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-label-md text-sm hover:bg-primary hover:text-on-primary transition-colors inline-flex items-center gap-2">
                                {{ Teks::ambil('beranda.tautan_lihat_semua') }}
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                        @foreach ($prestasi as $item)
                            <div class="rounded-[12px] border border-outline-variant/30 bg-surface p-5 shadow-[0px_4px_15px_rgba(0,0,0,0.02)] transition-colors hover:bg-surface-container-low">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="font-bold text-on-surface-variant tabular-nums">{{ $item->tahun }}</span>
                                    @if ($item->tingkat)
                                        <span class="rounded-full bg-primary-fixed px-3 py-1 text-xs font-medium tracking-wide text-on-primary-fixed-variant">
                                            {{ $item->tingkat->label() }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-2 text-lg font-bold text-on-surface leading-snug">{{ $item->judul }}</p>
                            </div>
                        @endforeach
                        <div class="hidden lg:flex justify-end">
                            <a href="{{ route('publik.prestasi') }}"
                               class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-label-md text-sm hover:bg-primary hover:text-on-primary transition-colors inline-flex items-center gap-2">
                                {{ Teks::ambil('beranda.tautan_lihat_semua') }}
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif

{{-- ============================ PENGURUS ============================ --}}
@if ($pengurus->isNotEmpty())
    <section id="pengurus" data-fade-in class="pt-12 md:pt-16 pb-12 md:pb-16 px-6 md:px-12 bg-surface border-t border-outline-variant/30">
        <div class="max-w-7xl mx-auto">
            <div class="mb-8 md:mb-10 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="font-headline-lg text-3xl font-bold text-primary">{{ Teks::ambil('beranda.pengurus.judul') }}</h2>
                    <p class="mt-2 text-on-surface-variant">{{ Teks::ambil('beranda.pengurus.subjudul') }}</p>
                </div>
                <a href="{{ route('publik.struktur') }}"
                   class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-label-md text-sm hover:bg-primary hover:text-on-primary transition-colors inline-flex items-center gap-2">
                    {{ Teks::ambil('beranda.pengurus.tautan') }}
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="overflow-x-auto snap-x snap-mandatory scrollbar-hide md:overflow-visible md:grid md:gap-6 md:grid-cols-2 lg:grid-cols-4 flex gap-4">
                @foreach ($pengurus as $orang)
                    <div class="snap-start shrink-0 w-[180px] md:w-full rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest p-6 text-center shadow-[0px_4px_20px_rgba(0,0,0,0.03)] hover:-translate-y-1 transition-transform">
                        <div class="mx-auto h-24 w-24 md:h-28 md:w-28 overflow-hidden rounded-full border-4 border-surface p-1 shadow-sm">
                            <img src="{{ $orang->urlFotoKecil() }}" alt="Foto {{ $orang->nama }}" loading="lazy"
                                 class="h-full w-full rounded-full bg-surface-container-highest object-cover">
                        </div>
                        <p class="mt-3 md:mt-4 text-base md:text-lg font-bold text-on-surface">{{ $orang->nama }}</p>
                        <p class="mt-1 text-xs md:text-sm font-medium text-primary">{{ $orang->jabatan }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@endsection
