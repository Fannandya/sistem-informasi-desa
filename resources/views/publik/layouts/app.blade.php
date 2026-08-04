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
    <link href="https://fonts.googleapis.com/css2?family=Literata:wght@400;500;600;700&family=Nunito+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-white text-slate-800">

    {{-- ------------------------------ Kop ------------------------------ --}}
    <header x-data="{ menu: false }"
            class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6">

            <a href="{{ route('beranda') }}" class="flex min-w-0 items-center gap-3">
                @if (filled($desa->logo))
                    <img src="{{ $desa->urlLogo() }}" alt="Logo {{ $desa->nama_desa }}"
                         class="h-10 w-10 shrink-0 rounded-lg object-cover">
                @else
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-600 font-bold text-white">
                        {{ mb_strtoupper(mb_substr($desa->nama_desa, 0, 1)) }}
                    </div>
                @endif
                <span class="min-w-0">
                    <span class="block truncate font-semibold text-slate-900">{{ $desa->nama_desa }}</span>
                    @if ($desa->wilayahLengkap())
                        <span class="block truncate text-xs text-slate-500">{{ $desa->wilayahLengkap() }}</span>
                    @endif
                </span>
            </a>

            <nav class="ml-auto hidden items-center gap-1 text-sm lg:flex">
                @foreach (\App\Support\MenuPublik::tautan() as $tautan)
                    <a href="{{ $tautan['url'] }}"
                       class="rounded-lg px-3 py-2 transition
                              {{ $tautan['aktif'] ? 'bg-emerald-50 font-medium text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        {{ $tautan['label'] }}
                    </a>
                @endforeach
            </nav>

            <button type="button" @click="menu = !menu"
                    class="ml-auto rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden"
                    aria-label="Buka menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path x-show="!menu" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="menu" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <nav x-show="menu" x-cloak class="border-t border-slate-200 bg-white lg:hidden">
            <div class="mx-auto max-w-6xl px-4 py-2 sm:px-6">
                @foreach (\App\Support\MenuPublik::tautan() as $tautan)
                    <a href="{{ $tautan['url'] }}"
                       class="block rounded-lg px-3 py-2.5 text-sm transition
                              {{ $tautan['aktif'] ? 'bg-emerald-50 font-medium text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}">
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
    <footer class="mt-16 border-t border-slate-200 bg-slate-50">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3">

            <div>
                <p class="font-semibold text-slate-900">{{ $desa->nama_desa }}</p>
                @if ($desa->wilayahLengkap())
                    <p class="mt-1 text-sm text-slate-600">{{ $desa->wilayahLengkap() }}</p>
                @endif
                @if (filled($desa->alamat_kantor))
                    <p class="mt-3 text-sm text-slate-600">{{ $desa->alamat_kantor }}</p>
                @endif
            </div>

            @if ($desa->punyaKontak())
                <div>
                    <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">{{ \App\Support\Teks::ambil('footer.kolom_kontak') }}</p>
                    <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                        @if (filled($desa->telepon_kantor))
                            <li><a href="tel:{{ preg_replace('/\D/', '', $desa->telepon_kantor) }}"
                                   class="hover:text-emerald-700">{{ $desa->telepon_kantor }}</a></li>
                        @endif
                        @if (filled($desa->email))
                            <li><a href="mailto:{{ $desa->email }}" class="hover:text-emerald-700">{{ $desa->email }}</a></li>
                        @endif
                        @if (filled($desa->website))
                            <li><a href="{{ $desa->website }}" class="hover:text-emerald-700">{{ $desa->website }}</a></li>
                        @endif
                    </ul>

                    @if ($desa->medsosTerisi())
                        <div class="mt-4 flex gap-3 text-sm">
                            @foreach ($desa->medsosTerisi() as $nama => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                   class="capitalize text-slate-500 underline hover:text-emerald-700">{{ $nama }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div>
                <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">{{ \App\Support\Teks::ambil('footer.kolom_halaman') }}</p>
                <ul class="mt-3 space-y-1.5 text-sm">
                    @foreach (\App\Support\MenuPublik::tautan() as $tautan)
                        <li><a href="{{ $tautan['url'] }}" class="text-slate-600 hover:text-emerald-700">{{ $tautan['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="border-t border-slate-200 px-4 py-5 text-center text-xs text-slate-500 sm:px-6">
            &copy; {{ now()->year }} {{ $desa->nama_desa }}. {{ \App\Support\Teks::ambil('footer.pengelola') }}
        </div>
    </footer>

    @stack('skrip')
</body>
</html>
