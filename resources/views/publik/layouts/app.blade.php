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
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Serif:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
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
            
            <a href="{{ url('/admin') }}" class="bg-primary-container text-on-primary-container px-4 py-2 rounded-lg font-label-md text-sm hover:bg-surface-tint hover:text-on-primary transition-colors hidden md:block">
                Masuk Portal
            </a>
            
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
                <a href="{{ url('/admin') }}" class="block mt-2 rounded-lg px-3 py-2.5 text-sm font-medium text-on-primary-container bg-primary-container text-center">
                    Masuk Portal
                </a>
            </div>
        </nav>
    </header>

    <main class="flex-1">
        @yield('konten')
    </main>

    {{-- ----------------------------- Footer ----------------------------- --}}
    <footer class="mt-16 bg-primary dark:bg-primary-container w-full">
        <div class="py-16 px-6 grid grid-cols-1 md:grid-cols-3 gap-10 max-w-7xl mx-auto">
            <div>
                <div class="font-headline-md text-2xl font-bold text-on-primary mb-6">{{ $desa->nama_desa }}</div>
                @if ($desa->wilayahLengkap())
                    <p class="font-body-md text-base text-on-primary/80 mb-2">{{ $desa->wilayahLengkap() }}</p>
                @endif
                @if (filled($desa->alamat_kantor))
                    <p class="font-body-md text-base text-on-primary/80 mb-4">{{ $desa->alamat_kantor }}</p>
                @endif
                <p class="font-body-md text-base text-on-primary/80 mb-4">{{ \App\Support\Teks::ambil('footer.pengelola') }}</p>
                <p class="font-label-sm text-xs text-on-primary/80">&copy; {{ now()->year }} {{ $desa->nama_desa }}. Hak Cipta Dilindungi.</p>
            </div>
            
            <div class="flex flex-col space-y-4">
                <h3 class="font-headline-md text-xl text-on-primary mb-4">{{ \App\Support\Teks::ambil('footer.kolom_kontak') }}</h3>
                @if (filled($desa->telepon_kantor))
                    <a href="tel:{{ preg_replace('/\D/', '', $desa->telepon_kantor) }}" class="font-label-sm text-sm text-on-primary underline hover:text-white transition-colors">{{ $desa->telepon_kantor }}</a>
                @endif
                @if (filled($desa->email))
                    <a href="mailto:{{ $desa->email }}" class="font-label-sm text-sm text-on-primary underline hover:text-white transition-colors">{{ $desa->email }}</a>
                @endif
                
                @if ($desa->medsosTerisi())
                    <div class="mt-4 flex gap-3 text-sm">
                        @foreach ($desa->medsosTerisi() as $nama => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                               class="capitalize text-on-primary/80 underline hover:text-white transition-colors">{{ $nama }}</a>
                        @endforeach
                    </div>
                @endif
                
                <h3 class="font-headline-md text-xl text-on-primary mt-6 mb-4">{{ \App\Support\Teks::ambil('footer.kolom_halaman') }}</h3>
                <div class="flex flex-col space-y-2">
                    @foreach (\App\Support\MenuPublik::tautan() as $tautan)
                        <a href="{{ $tautan['url'] }}" class="font-label-sm text-sm text-on-primary/80 hover:text-white transition-colors">{{ $tautan['label'] }}</a>
                    @endforeach
                </div>
            </div>
            
            <div class="h-48 bg-surface-container-highest rounded-lg overflow-hidden relative">
                @if (filled($desa->peta_embed))
                    <iframe src="{{ $desa->peta_embed }}" class="absolute inset-0 w-full h-full" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                @else
                    <div class="absolute inset-0 flex items-center justify-center text-on-surface-variant font-label-md text-sm bg-surface-variant/50">
                        [Peta Desa Belum Diatur]
                    </div>
                @endif
            </div>
        </div>
    </footer>

    @stack('skrip')
</body>
</html>
