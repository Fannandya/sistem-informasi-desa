<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('judul', 'Panel Admin') — {{ $desa->nama_desa }}</title>
    @if (filled($desa->logo))
        <link rel="icon" href="{{ $desa->urlLogo() }}" type="image/webp">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Serif:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dialog.js'])
</head>
<body class="h-full bg-background text-on-background overflow-hidden flex" x-data="{ sidebarTerbuka: false }">

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col -translate-x-full bg-surface-container-low text-primary border-r border-outline-variant transition-transform lg:translate-x-0 lg:relative lg:flex"
           :class="sidebarTerbuka && 'translate-x-0'">

        <div class="px-6 py-4 flex flex-col items-start mb-2 mt-2">
            <div class="flex items-center space-x-3 mb-2">
                @if (filled($desa->logo))
                    <img src="{{ $desa->urlLogo() }}" alt="Logo {{ $desa->nama_desa }}"
                         class="h-10 w-10 shrink-0 rounded-full border border-outline-variant object-cover">
                @else
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary font-bold text-white border border-outline-variant">
                        {{ mb_strtoupper(mb_substr($desa->nama_desa, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-primary leading-tight truncate">{{ $desa->nama_desa }}</h2>
                    <p class="text-xs text-on-surface-variant truncate">Panel Admin</p>
                </div>
            </div>
        </div>

        <nav class="scrollbar-gelap flex-1 space-y-1 overflow-y-auto overscroll-contain px-4 pb-4">
            @php
                // User requested hover sidebar green like footer (bg-primary)
                $tautanAktif = 'flex items-center space-x-3 px-3 py-2.5 bg-primary text-on-primary font-bold rounded-xl scale-[0.98] duration-100 shadow-sm';
                $tautanBiasa = 'flex items-center space-x-3 px-3 py-2.5 text-on-surface-variant hover:bg-primary hover:text-on-primary transition-all rounded-xl';
            @endphp

            <a href="{{ route('admin.dashboard') }}"
               class="{{ request()->routeIs('admin.dashboard') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.dashboard') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>dashboard</span>
                <span class="text-sm">Dashboard</span>
            </a>

            <p class="px-3 pt-5 pb-2 text-xs font-semibold tracking-wider text-on-surface-variant uppercase">Kependudukan</p>

            <a href="{{ route('admin.penduduk.index') }}"
               class="{{ request()->routeIs('admin.penduduk.index', 'admin.penduduk.create', 'admin.penduduk.edit') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.penduduk.index', 'admin.penduduk.create', 'admin.penduduk.edit') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>groups</span>
                <span class="text-sm">Data Warga</span>
            </a>

            <a href="{{ route('admin.penduduk.arsip') }}"
               class="{{ request()->routeIs('admin.penduduk.arsip') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.penduduk.arsip') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>archive</span>
                <span class="text-sm">Arsip Warga</span>
            </a>

            <a href="{{ route('admin.rekap-kk.index') }}"
               class="{{ request()->routeIs('admin.rekap-kk.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.rekap-kk.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>folder_shared</span>
                <span class="text-sm">Rekap KK</span>
            </a>

            <a href="{{ route('admin.wilayah.index') }}"
               class="{{ request()->routeIs('admin.wilayah.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.wilayah.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>map</span>
                <span class="text-sm">Wilayah</span>
            </a>

            <a href="{{ route('admin.pekerjaan.index') }}"
               class="{{ request()->routeIs('admin.pekerjaan.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.pekerjaan.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>work</span>
                <span class="text-sm">Pekerjaan</span>
            </a>

            <a href="{{ route('admin.ref-kategori-potensi.index') }}"
               class="{{ request()->routeIs('admin.ref-kategori-potensi.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.ref-kategori-potensi.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>category</span>
                <span class="text-sm">Kategori Potensi</span>
            </a>

            <a href="{{ route('admin.ref-kategori-fasilitas.index') }}"
               class="{{ request()->routeIs('admin.ref-kategori-fasilitas.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.ref-kategori-fasilitas.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>category</span>
                <span class="text-sm">Kategori Fasilitas</span>
            </a>

            <p class="px-3 pt-5 pb-2 text-xs font-semibold tracking-wider text-on-surface-variant uppercase">Desa</p>

            <a href="{{ route('admin.pengurus.index') }}"
               class="{{ request()->routeIs('admin.pengurus.*', 'admin.organisasi.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.pengurus.*', 'admin.organisasi.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>account_tree</span>
                <span class="text-sm">Struktur Organisasi</span>
            </a>

            <a href="{{ route('admin.organisasi.index') }}"
               class="{{ request()->routeIs('admin.organisasi.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.organisasi.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>corporate_fare</span>
                <span class="text-sm">Kelola Organisasi</span>
            </a>

            <a href="{{ route('admin.potensi.index') }}"
               class="{{ request()->routeIs('admin.potensi.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.potensi.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>landscape</span>
                <span class="text-sm">Potensi Desa</span>
            </a>

            <a href="{{ route('admin.fasilitas.index') }}"
               class="{{ request()->routeIs('admin.fasilitas.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.fasilitas.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>local_convenience_store</span>
                <span class="text-sm">Fasilitas Umum</span>
            </a>

            <a href="{{ route('admin.prestasi.index') }}"
               class="{{ request()->routeIs('admin.prestasi.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.prestasi.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>emoji_events</span>
                <span class="text-sm">Prestasi Desa</span>
            </a>

            <a href="{{ route('admin.berita.index') }}"
               class="{{ request()->routeIs('admin.berita.*', 'admin.kategori-berita.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.berita.*', 'admin.kategori-berita.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>article</span>
                <span class="text-sm">Berita Desa</span>
            </a>

            <a href="{{ route('admin.acara.index') }}"
               class="{{ request()->routeIs('admin.acara.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.acara.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>photo_library</span>
                <span class="text-sm">Galeri Acara</span>
            </a>

            <a href="{{ route('admin.carousel.index') }}"
               class="{{ request()->routeIs('admin.carousel.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.carousel.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>view_carousel</span>
                <span class="text-sm">Carousel Beranda</span>
            </a>

            <a href="{{ route('admin.teks-tampilan.index') }}"
               class="{{ request()->routeIs('admin.teks-tampilan.*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.teks-tampilan.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>text_fields</span>
                <span class="text-sm">Teks Tampilan</span>
            </a>

            <a href="{{ route('admin.pengaturan') }}"
               class="{{ request()->routeIs('admin.pengaturan*') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.pengaturan*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>settings</span>
                <span class="text-sm">Pengaturan Desa</span>
            </a>

            <p class="px-3 pt-5 pb-2 text-xs font-semibold tracking-wider text-on-surface-variant uppercase">Akun</p>

            <a href="{{ route('admin.kata-sandi') }}"
               class="{{ request()->routeIs('admin.kata-sandi') ? $tautanAktif : $tautanBiasa }}">
                <span class="material-symbols-outlined" {!! request()->routeIs('admin.kata-sandi') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>password</span>
                <span class="text-sm">Ganti Kata Sandi</span>
            </a>

            <div class="mt-8 pb-4">
                <a href="{{ url('/') }}" target="_blank"
                   class="flex items-center justify-center w-full bg-primary-container text-on-primary font-medium text-sm py-2 rounded-xl mb-4 hover:brightness-110 transition-all">
                    Buka Portal Publik
                </a>
                
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center space-x-3 px-3 py-2 w-full text-error hover:bg-error-container hover:text-on-error-container transition-all rounded-xl">
                        <span class="material-symbols-outlined">logout</span>
                        <span class="text-sm font-medium">Keluar</span>
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    {{-- Latar gelap saat sidebar terbuka di layar kecil --}}
    <div x-show="sidebarTerbuka" x-cloak @click="sidebarTerbuka = false"
         class="fixed inset-0 z-30 bg-surface-container-low/50 lg:hidden"></div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Bilah atas (Admin TopNavBar) --}}
        <header class="bg-surface-container-lowest text-primary docked full-width top-0 sticky border-b border-outline-variant flex justify-between items-center w-full px-6 h-14 z-20">
            <div class="flex items-center flex-1">
                <button type="button" @click="sidebarTerbuka = !sidebarTerbuka"
                        class="mr-4 rounded-xl p-1 text-on-surface-variant hover:bg-surface-container lg:hidden">
                    <span class="material-symbols-outlined text-[24px]">menu</span>
                </button>
                <h1 class="text-xl font-bold text-primary mr-8 truncate hidden md:block">@yield('judul', 'Dashboard Admin')</h1>
                
                <!-- Search Bar (on_left) -->
                <div class="relative w-64 max-w-sm hidden sm:block">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                    <input class="w-full bg-surface-container-low border border-outline-variant rounded-full py-1.5 pl-10 pr-4 text-sm focus:outline-none focus:border-tertiary focus:ring-2 focus:ring-tertiary/20 transition-all text-on-surface" placeholder="Cari data..." type="text">
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-medium text-on-surface">{{ auth('admin')->user()->nama }}</p>
                </div>
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-container text-sm font-semibold text-on-primary-container">
                    {{ auth('admin')->user()->inisial() }}
                </div>
            </div>
        </header>

        <!-- Main Scrollable Content -->
        <main class="flex-1 overflow-y-auto p-6 md:p-10 bg-surface">
            <x-admin.notifikasi />

            @yield('konten')
        </main>
    </div>

    <x-admin.pusat-dialog />

    @stack('skrip')
</body>
</html>
