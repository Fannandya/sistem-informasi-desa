{{--
    Kerangka halaman publik.

    Dibuat di fitur 08 karena halaman Struktur Organisasi membutuhkannya, dan
    diperluas pada fitur 14 menjadi navigasi penuh beserta landing page.
    Diredesain untuk tampilan profesional dan bersih.
--}}
<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('judul', $desa->nama_desa) — {{ $desa->nama_desa }}</title>
    <meta name="description" content="@yield('deskripsi', $desa->deskripsiMeta())">

    @if (filled($desa->logo))
        <link rel="icon" href="{{ $desa->urlLogo() }}" type="image/webp">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('judul', $desa->nama_desa)">
    <meta property="og:description" content="@yield('deskripsi', $desa->deskripsiMeta())">
    <meta property="og:url" content="{{ url()->current() }}">
    @if (filled($desa->foto_sampul))
        <meta property="og:image" content="{{ $desa->urlSampul() }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Literata:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&family=Nunito+Sans:wght@400;500;600;700;800&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-surface text-on-surface selection:bg-primary/20 selection:text-on-primary-fixed-variant">

    {{-- ------------------------------ Header ------------------------------ --}}
    <header
        x-data="{
            menu: false,
            scrolled: false,
            init() {
                this.scrolled = window.scrollY > 20;
                window.addEventListener('scroll', () => {
                    this.scrolled = window.scrollY > 20;
                }, { passive: true });
            }
        }"
        :class="scrolled ? 'py-3 sm:py-4' : 'py-5 sm:py-6'"
        class="fixed top-0 inset-x-0 z-50 transition-all duration-500 ease-out">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            {{-- Nav Pill --}}
            <div 
                :class="scrolled 
                    ? 'bg-surface-container-lowest/30 backdrop-blur-xl backdrop-saturate-[1.8] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-t border-t-white/80 border-b border-b-outline-variant/20 border-x border-x-outline-variant/20' 
                    : 'bg-transparent border border-transparent'"
                class="flex items-center justify-between w-full rounded-2xl px-4 py-3 transition-all duration-500">
                
                {{-- Brand --}}
                <a href="{{ route('beranda') }}" class="flex items-center gap-3.5 group outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-xl">
                    @if (filled($desa->logo))
                        <img src="{{ $desa->urlLogo() }}" alt="Logo {{ $desa->nama_desa }}" class="w-10 h-10 rounded-xl object-cover ring-1 ring-outline-variant/50 group-hover:ring-primary/50 transition-all duration-300">
                    @else
                        <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-primary text-white font-bold font-headline-md shadow-sm">
                            {{ mb_strtoupper(mb_substr($desa->nama_desa, 0, 1)) }}
                        </div>
                    @endif
                    <div :class="scrolled ? 'text-on-surface' : 'text-white'" class="font-headline-md text-lg font-bold tracking-tight transition-colors duration-300 truncate max-w-[160px] sm:max-w-none">
                        {{ $desa->nama_desa }}
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden md:flex items-center gap-1.5">
                    @foreach (\App\Support\MenuPublik::tautan() as $tautan)
                        <a href="{{ $tautan['url'] }}"
                           data-aktif="{{ $tautan['aktif'] ? '1' : '0' }}"
                           :class="scrolled
                               ? ($el.dataset.aktif === '1' ? 'text-primary bg-primary/10' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low')
                               : ($el.dataset.aktif === '1' ? 'text-white bg-white/20' : 'text-white/80 hover:text-white hover:bg-white/10')"
                           class="font-label-md text-sm px-4 py-2 rounded-xl transition-all duration-200 outline-none focus-visible:ring-2 focus-visible:ring-primary">
                            {{ $tautan['label'] }}
                        </a>
                    @endforeach
                </nav>

                {{-- Mobile Toggle --}}
                <button type="button" @click="menu = !menu"
                        :class="scrolled ? 'text-on-surface hover:bg-surface-container-low' : 'text-white hover:bg-white/10'"
                        class="md:hidden p-2 -mr-2 rounded-xl transition-colors duration-300 outline-none focus-visible:ring-2 focus-visible:ring-primary flex items-center justify-center relative w-10 h-10"
                        aria-label="Toggle menu">
                    <span class="material-symbols-outlined absolute transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
                          :class="menu ? 'opacity-0 rotate-90 scale-50' : 'opacity-100 rotate-0 scale-100'">menu</span>
                    <span class="material-symbols-outlined absolute transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
                          :class="menu ? 'opacity-100 rotate-0 scale-100' : 'opacity-0 -rotate-90 scale-50'">close</span>
                </button>
            </div>

            {{-- Mobile Nav Dropdown --}}
            <div x-show="menu" x-cloak
                 @click.away="menu = false"
                 x-transition:enter="transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
                 x-transition:enter-start="opacity-0 -translate-y-8 scale-95 blur-md"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-none"
                 x-transition:leave="transition-all duration-300 ease-in"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100 blur-none"
                 x-transition:leave-end="opacity-0 -translate-y-4 scale-95 blur-sm"
                 class="absolute top-full mt-3 left-4 right-4 bg-surface-container-lowest/70 backdrop-blur-3xl backdrop-saturate-[2] border-t border-t-white/80 border-b border-b-outline-variant/20 border-x border-x-outline-variant/20 rounded-2xl shadow-[0_30px_60px_rgb(0,0,0,0.12)] overflow-hidden md:hidden origin-top">
                <div class="p-2 flex flex-col gap-1">
                    @foreach (\App\Support\MenuPublik::tautan() as $tautan)
                        <a href="{{ $tautan['url'] }}"
                           class="px-4 py-3 rounded-xl font-label-md text-sm transition-colors {{ $tautan['aktif'] ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
                            {{ $tautan['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </header>

    {{-- ------------------------------ Main ------------------------------ --}}
    <main class="flex-1 @yield('main_padding', 'pt-28 md:pt-36')">
        @yield('konten')
    </main>

    {{-- ----------------------------- Footer ----------------------------- --}}
    <footer class="@yield('footer_margin', 'mt-24') border-t border-outline-variant/30 bg-surface-container-lowest pt-16">
        <div class="max-w-7xl mx-auto px-6 pb-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8">
                
                {{-- Col 1: Brand & Identitas (4 cols) --}}
                <div class="lg:col-span-4 space-y-6">
                    <a href="{{ route('beranda') }}" class="flex items-center gap-3 outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-xl w-fit">
                        @if (filled($desa->logo))
                            <img src="{{ $desa->urlLogo() }}" alt="Logo {{ $desa->nama_desa }}" class="w-10 h-10 rounded-xl object-cover ring-1 ring-outline-variant/50">
                        @else
                            <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-primary text-white font-bold font-headline-md shadow-sm">
                                {{ mb_strtoupper(mb_substr($desa->nama_desa, 0, 1)) }}
                            </div>
                        @endif
                        <span class="font-headline-md font-semibold text-on-surface text-xl tracking-tight">{{ $desa->nama_desa }}</span>
                    </a>
                    <div class="space-y-3 font-body-md text-on-surface-variant text-sm leading-relaxed max-w-sm">
                        @if ($desa->wilayahLengkap())
                            <p>{{ $desa->wilayahLengkap() }}</p>
                        @endif
                        @if (filled($desa->alamat_kantor))
                            <p class="flex items-start gap-2.5">
                                <span class="material-symbols-outlined text-[18px] shrink-0 mt-0.5 text-on-surface-variant/60">location_on</span>
                                <span>{{ $desa->alamat_kantor }}</span>
                            </p>
                        @endif
                        <p class="pt-2 text-on-surface-variant/70">{{ \App\Support\Teks::ambil('footer.pengelola') }}</p>
                    </div>
                </div>

                {{-- Col 2: Halaman & Kontak (5 cols) --}}
                <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <div>
                        <h3 class="font-headline-md font-semibold text-on-surface mb-5">{{ \App\Support\Teks::ambil('footer.kolom_halaman') }}</h3>
                        <ul class="space-y-3">
                            @foreach (\App\Support\MenuPublik::tautan() as $tautan)
                                <li>
                                    <a href="{{ $tautan['url'] }}" class="font-label-md text-sm text-on-surface-variant hover:text-primary transition-colors flex items-center gap-2 group w-fit outline-none focus-visible:ring-2 focus-visible:ring-primary rounded">
                                        <span class="w-1.5 h-1.5 rounded-full bg-outline-variant/50 group-hover:bg-primary transition-colors"></span>
                                        {{ $tautan['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-headline-md font-semibold text-on-surface mb-5">{{ \App\Support\Teks::ambil('footer.kolom_kontak') }}</h3>
                        <ul class="space-y-4">
                            @if (filled($desa->telepon_kantor))
                                <li>
                                    <a href="tel:{{ preg_replace('/\D/', '', $desa->telepon_kantor) }}" class="flex items-center gap-3 font-label-md text-sm text-on-surface-variant hover:text-primary transition-colors w-fit outline-none focus-visible:ring-2 focus-visible:ring-primary rounded">
                                        <span class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[18px]">call</span>
                                        </span>
                                        {{ $desa->telepon_kantor }}
                                    </a>
                                </li>
                            @endif
                            @if (filled($desa->email))
                                <li>
                                    <a href="mailto:{{ $desa->email }}" class="flex items-center gap-3 font-label-md text-sm text-on-surface-variant hover:text-primary transition-colors w-fit outline-none focus-visible:ring-2 focus-visible:ring-primary rounded">
                                        <span class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[18px]">mail</span>
                                        </span>
                                        <span class="truncate">{{ $desa->email }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                {{-- Col 3: Sosial (3 cols) --}}
                <div class="lg:col-span-3 flex flex-col h-full">
                    @if ($desa->medsosTerisi())
                        <h3 class="font-headline-md font-semibold text-on-surface mb-5">Media Sosial</h3>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($desa->medsosTerisi() as $nama => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                   class="w-9 h-9 rounded-full bg-surface-container hover:bg-primary text-on-surface-variant hover:text-white transition-all duration-300 flex items-center justify-center outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                   title="{{ ucfirst($nama) }}">
                                    @switch(strtolower($nama))
                                        @case('facebook')
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        @break
                                        @case('instagram')
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.266.069 1.646.069 4.85 0 3.204-.012 3.584-.07 4.85-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.015-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1 1 12.324 0 6.162 6.162 0 0 1-12.324 0zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm4.965-10.322a1.44 1.44 0 1 1 2.881.001 1.44 1.44 0 0 1-2.881-.001z"/></svg>
                                        @break
                                        @case('youtube')
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                        @break
                                        @case('whatsapp')
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-4.782 1.176l-.309.15-3.227-.847.86 3.126.194.309a9.866 9.866 0 001.516 4.383 9.884 9.884 0 004.718 2.751l.315.02.316-.024a9.884 9.884 0 004.832-1.256l.316-.19 3.327.883-.868-3.335.123-.317a9.884 9.884 0 00-1.464-4.511 9.884 9.884 0 00-4.732-2.768l-.313-.02-.317.024zM12 0C5.383 0 0 5.383 0 12s5.383 12 12 12 12-5.383 12-12S18.617 0 12 0z"/></svg>
                                        @break
                                        @case('tiktok')
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.1 1.75 2.9 2.9 0 1 1 2.6-4.53v-3.5a6.56 6.56 0 0 0-6.77 6.68c0 3.67 2.97 6.68 6.65 6.68 3.66 0 6.69-2.97 6.88-6.75.07-.75.12-1.73 0-3.08a6.51 6.51 0 0 0-1.02-3.52z"/></svg>
                                        @break
                                        @default
                                            <span class="material-symbols-outlined text-sm">share</span>
                                    @endswitch
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- Full Width Map Section --}}
        <div class="w-full h-72 sm:h-80 md:h-[400px] bg-surface-container border-y border-outline-variant/30 relative">
            @if (filled($desa->peta_embed))
                <iframe src="{{ $desa->peta_embed }}" class="absolute inset-0 w-full h-full grayscale-[20%] hover:grayscale-0 transition-all duration-700" loading="lazy" title="Peta lokasi {{ $desa->nama_desa }}"></iframe>
            @else
                <div class="absolute inset-0 flex flex-col items-center justify-center text-on-surface-variant/50">
                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">map</span>
                    <span class="font-body-md text-sm">Peta belum diatur</span>
                </div>
            @endif
        </div>

        {{-- Bottom Copyright --}}
        <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="font-label-sm text-xs text-on-surface-variant/70">&copy; {{ now()->year }} {{ $desa->nama_desa }}. Hak Cipta Dilindungi.</p>
            <p class="font-label-sm text-xs text-on-surface-variant/50 flex items-center gap-1.5">
                Sistem Informasi Desa
            </p>
        </div>
    </footer>

    @stack('skrip')
</body>
</html>
