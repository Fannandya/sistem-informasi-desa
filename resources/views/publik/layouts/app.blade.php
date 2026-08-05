{{--
    Kerangka halaman publik.

    Dibuat di fitur 08 karena halaman Struktur Organisasi membutuhkannya, dan
    diperluas pada fitur 14 menjadi navigasi penuh beserta landing page.
--}}
<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('judul', $desa->nama_desa) — {{ $desa->nama_desa }}</title>
    <meta name="description" content="@yield('deskripsi', $desa->deskripsiMeta())">

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('judul', $desa->nama_desa)">
    <meta property="og:description" content="@yield('deskripsi', $desa->deskripsiMeta())">
    <meta property="og:url" content="{{ url()->current() }}">
    @if (filled($desa->foto_sampul))
        <meta property="og:image" content="{{ $desa->urlSampul() }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Literata:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-surface-container-lowest text-slate-800">

    {{-- ------------------------------ Kop ------------------------------ --}}
    <header x-data="{ menu: false }" class="bg-surface-container-lowest shadow-sm z-50 top-0 sticky">
        <div class="flex justify-between items-center w-full px-6 max-w-7xl mx-auto h-16">
            <a href="{{ route('beranda') }}" class="flex items-center gap-3">
                @if (filled($desa->logo))
                    <img src="{{ $desa->urlLogo() }}" alt="Logo {{ $desa->nama_desa }}"
                         class="h-10 w-10 shrink-0 rounded-lg object-cover">
                @else
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary font-bold text-white">
                        {{ mb_strtoupper(mb_substr($desa->nama_desa, 0, 1)) }}
                    </div>
                @endif
                <div class="font-headline-md text-xl font-bold text-primary hidden sm:block">{{ $desa->nama_desa }}</div>
            </a>
            
            <nav class="hidden md:flex space-x-8">
                @foreach (\App\Support\MenuPublik::tautan() as $tautan)
                    <a href="{{ $tautan['url'] }}"
                       class="font-label-md text-sm transition-colors {{ $tautan['aktif'] ? 'text-primary border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' }}">
                        {{ $tautan['label'] }}
                    </a>
                @endforeach
            </nav>
            
            
            <button type="button" @click="menu = !menu" class="md:hidden text-on-surface">
                <span class="material-symbols-outlined" x-show="!menu">menu</span>
                <span class="material-symbols-outlined" x-show="menu" x-cloak>close</span>
            </button>
        </div>

        <nav x-show="menu" x-cloak class="border-t border-outline-variant bg-surface-container-lowest md:hidden">
            <div class="mx-auto max-w-7xl px-4 py-2 sm:px-6">
                @foreach (\App\Support\MenuPublik::tautan() as $tautan)
                    <a href="{{ $tautan['url'] }}"
                       class="block rounded-lg px-3 py-2.5 text-sm transition {{ $tautan['aktif'] ? 'bg-primary-fixed/20 font-medium text-primary' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                        {{ $tautan['label'] }}
                    </a>
                @endforeach

            </div>
        </nav>
    </header>

    <main class="flex-1">
        @yield('konten')
    </main>

    {{-- ----------------------------- Footer ----------------------------- --}}
    <footer class="@yield('footer_margin', 'mt-16') bg-primary dark:bg-primary-container w-full">
        <div class="py-12 md:py-16 px-6 grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-10 max-w-7xl mx-auto">
            {{-- Identitas Desa --}}
            <div>
                <div class="font-headline-md text-2xl font-bold text-on-primary mb-4">{{ $desa->nama_desa }}</div>
                @if ($desa->wilayahLengkap())
                    <p class="font-body-md text-sm md:text-base text-on-primary/80 mb-2">{{ $desa->wilayahLengkap() }}</p>
                @endif
                @if (filled($desa->alamat_kantor))
                    <p class="font-body-md text-sm md:text-base text-on-primary/80 mb-4">{{ $desa->alamat_kantor }}</p>
                @endif
                <p class="font-body-md text-sm md:text-base text-on-primary/80 mb-4">{{ \App\Support\Teks::ambil('footer.pengelola') }}</p>
                <p class="font-label-sm text-xs text-on-primary/80">&copy; {{ now()->year }} {{ $desa->nama_desa }}. Hak Cipta Dilindungi.</p>
            </div>

            {{-- Kontak & Menu --}}
            <div class="flex flex-col space-y-6">
                {{-- Kontak --}}
                <div>
                    <h3 class="font-headline-md text-lg md:text-xl text-on-primary mb-3 md:mb-4">{{ \App\Support\Teks::ambil('footer.kolom_kontak') }}</h3>
                    <div class="space-y-2">
                        @if (filled($desa->telepon_kantor))
                            <a href="tel:{{ preg_replace('/\D/', '', $desa->telepon_kantor) }}" class="font-label-sm text-sm text-on-primary/90 hover:text-white transition-colors block">{{ $desa->telepon_kantor }}</a>
                        @endif
                        @if (filled($desa->email))
                            <a href="mailto:{{ $desa->email }}" class="font-label-sm text-sm text-on-primary/90 hover:text-white transition-colors block">{{ $desa->email }}</a>
                        @endif
                    </div>
                </div>

                {{-- Tautan Cepat (accordion mobile) --}}
                <div x-data="{ buka: false }">
                    <button type="button" @click="buka = !buka" class="md:hidden w-full flex items-center justify-between mb-3 font-headline-md text-lg text-on-primary hover:text-white transition-colors">
                        <span>{{ \App\Support\Teks::ambil('footer.kolom_tautan_cepat') }}</span>
                        <span class="material-symbols-outlined transition-transform" :class="{ 'rotate-180': buka }">expand_more</span>
                    </button>
                    <h3 class="hidden md:block font-headline-md text-lg md:text-xl text-on-primary mb-3 md:mb-4">{{ \App\Support\Teks::ambil('footer.kolom_halaman') }}</h3>
                    <div x-show="buka || window.innerWidth >= 768" x-cloak class="space-y-2 flex flex-col">
                        @foreach (\App\Support\MenuPublik::tautan() as $tautan)
                            <a href="{{ $tautan['url'] }}" class="font-label-sm text-sm text-on-primary/80 hover:text-white transition-colors">{{ $tautan['label'] }}</a>
                        @endforeach
                    </div>
                </div>

                {{-- Media Sosial --}}
                @if ($desa->medsosTerisi())
                    <div>
                        <h3 class="font-label-md text-xs uppercase tracking-widest text-on-primary/70 mb-3">Media Sosial</h3>
                        <div class="flex gap-3">
                            @foreach ($desa->medsosTerisi() as $nama => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                   class="w-10 h-10 rounded-lg bg-on-primary/15 hover:bg-on-primary/25 transition-colors flex items-center justify-center text-on-primary"
                                   title="{{ ucfirst($nama) }}">
                                    {{-- SVG inline per platform --}}
                                    @switch(strtolower($nama))
                                        @case('facebook')
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                        @break
                                        @case('instagram')
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.266.069 1.646.069 4.85 0 3.204-.012 3.584-.07 4.85-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.015-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1 1 12.324 0 6.162 6.162 0 0 1-12.324 0zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm4.965-10.322a1.44 1.44 0 1 1 2.881.001 1.44 1.44 0 0 1-2.881-.001z"/>
                                            </svg>
                                        @break
                                        @case('youtube')
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                            </svg>
                                        @break
                                        @case('whatsapp')
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-4.782 1.176l-.309.15-3.227-.847.86 3.126.194.309a9.866 9.866 0 001.516 4.383 9.884 9.884 0 004.718 2.751l.315.02.316-.024a9.884 9.884 0 004.832-1.256l.316-.19 3.327.883-.868-3.335.123-.317a9.884 9.884 0 00-1.464-4.511 9.884 9.884 0 00-4.732-2.768l-.313-.02-.317.024zM12 0C5.383 0 0 5.383 0 12s5.383 12 12 12 12-5.383 12-12S18.617 0 12 0z"/>
                                            </svg>
                                        @break
                                        @case('tiktok')
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.1 1.75 2.9 2.9 0 1 1 2.6-4.53v-3.5a6.56 6.56 0 0 0-6.77 6.68c0 3.67 2.97 6.68 6.65 6.68 3.66 0 6.69-2.97 6.88-6.75.07-.75.12-1.73 0-3.08a6.51 6.51 0 0 0-1.02-3.52z"/>
                                            </svg>
                                        @break
                                        @default
                                            <span class="material-symbols-outlined text-base">share</span>
                                    @endswitch
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Peta / Google Maps --}}
            <div>
                @if (filled($desa->peta_embed))
                    <div class="h-48 bg-surface-container-highest rounded-lg overflow-hidden relative">
                        <iframe src="{{ $desa->peta_embed }}" class="absolute inset-0 w-full h-full" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @else
                    <div class="h-48 bg-surface-container-highest rounded-lg flex items-center justify-center text-on-surface-variant">
                        <div class="text-center">
                            <span class="material-symbols-outlined text-4xl block mb-2 opacity-50">location_on</span>
                            <p class="font-label-md text-xs">Lokasi belum diatur</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </footer>

    @stack('skrip')
</body>
</html>
