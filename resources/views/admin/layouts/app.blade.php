<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('judul', 'Panel Admin') — {{ $desa->nama_desa }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Literata:wght@400;500;600;700&family=Nunito+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dialog.js'])
</head>
<body class="h-full bg-surface text-on-surface" x-data="{ sidebarTerbuka: false }">

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col -translate-x-full bg-surface-container-low text-on-surface transition-transform lg:translate-x-0"
           :class="sidebarTerbuka && 'translate-x-0'">

        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-outline-variant px-5">
            @if (filled($desa->logo))
                <img src="{{ $desa->urlLogo() }}" alt="Logo {{ $desa->nama_desa }}"
                     class="h-9 w-9 shrink-0 rounded-[12px] object-cover">
            @else
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[12px] bg-primary font-bold text-white">
                    {{ mb_strtoupper(mb_substr($desa->nama_desa, 0, 1)) }}
                </div>
            @endif
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white">{{ $desa->nama_desa }}</p>
                <p class="text-xs text-on-surface-variant">Panel Admin</p>
            </div>
        </div>

        <nav class="scrollbar-gelap flex-1 space-y-1 overflow-y-auto overscroll-contain px-3 py-4 text-sm">
            @php
                $tautanAktif = 'flex items-center gap-3 rounded-[12px] bg-surface-container px-3 py-2.5 font-medium text-white';
                $tautanBiasa = 'flex items-center gap-3 rounded-[12px] px-3 py-2.5 transition hover:bg-surface-container hover:text-white';
            @endphp

            <a href="{{ route('admin.dashboard') }}"
               class="{{ request()->routeIs('admin.dashboard') ? $tautanAktif : $tautanBiasa }}">
                Dashboard
            </a>

            <p class="px-3 pt-5 pb-2 text-xs font-semibold tracking-wider text-on-surface-variant uppercase">Kependudukan</p>

            <a href="{{ route('admin.penduduk.index') }}"
               class="{{ request()->routeIs('admin.penduduk.index', 'admin.penduduk.create', 'admin.penduduk.edit') ? $tautanAktif : $tautanBiasa }}">
                Data Warga
            </a>

            <a href="{{ route('admin.penduduk.arsip') }}"
               class="{{ request()->routeIs('admin.penduduk.arsip') ? $tautanAktif : $tautanBiasa }}">
                Arsip Warga
            </a>

            <a href="{{ route('admin.rekap-kk.index') }}"
               class="{{ request()->routeIs('admin.rekap-kk.*') ? $tautanAktif : $tautanBiasa }}">
                Rekap KK
            </a>

            <a href="{{ route('admin.wilayah.index') }}"
               class="{{ request()->routeIs('admin.wilayah.*') ? $tautanAktif : $tautanBiasa }}">
                Wilayah
            </a>

            <a href="{{ route('admin.pekerjaan.index') }}"
               class="{{ request()->routeIs('admin.pekerjaan.*') ? $tautanAktif : $tautanBiasa }}">
                Pekerjaan
            </a>

            {{-- Menu konten publik ditambahkan saat fitur terkait dikerjakan (08–13). --}}
            <p class="px-3 pt-5 pb-2 text-xs font-semibold tracking-wider text-on-surface-variant uppercase">Desa</p>

            <a href="{{ route('admin.pengurus.index') }}"
               class="{{ request()->routeIs('admin.pengurus.*', 'admin.organisasi.*') ? $tautanAktif : $tautanBiasa }}">
                Struktur Organisasi
            </a>

            <a href="{{ route('admin.organisasi.index') }}"
               class="{{ request()->routeIs('admin.organisasi.*') ? $tautanAktif : $tautanBiasa }}">
                Kelola Organisasi
            </a>

            <a href="{{ route('admin.potensi.index') }}"
               class="{{ request()->routeIs('admin.potensi.*') ? $tautanAktif : $tautanBiasa }}">
                Potensi Desa
            </a>

            <a href="{{ route('admin.fasilitas.index') }}"
               class="{{ request()->routeIs('admin.fasilitas.*') ? $tautanAktif : $tautanBiasa }}">
                Fasilitas Umum
            </a>

            <a href="{{ route('admin.prestasi.index') }}"
               class="{{ request()->routeIs('admin.prestasi.*') ? $tautanAktif : $tautanBiasa }}">
                Prestasi Desa
            </a>

            <a href="{{ route('admin.berita.index') }}"
               class="{{ request()->routeIs('admin.berita.*', 'admin.kategori-berita.*') ? $tautanAktif : $tautanBiasa }}">
                Berita Desa
            </a>

            <a href="{{ route('admin.acara.index') }}"
               class="{{ request()->routeIs('admin.acara.*') ? $tautanAktif : $tautanBiasa }}">
                Galeri Acara
            </a>

            <a href="{{ route('admin.carousel.index') }}"
               class="{{ request()->routeIs('admin.carousel.*') ? $tautanAktif : $tautanBiasa }}">
                Carousel Beranda
            </a>

            <a href="{{ route('admin.teks-tampilan.index') }}"
               class="{{ request()->routeIs('admin.teks-tampilan.*') ? $tautanAktif : $tautanBiasa }}">
                Teks Tampilan
            </a>

            <a href="{{ route('admin.pengaturan') }}"
               class="{{ request()->routeIs('admin.pengaturan*') ? $tautanAktif : $tautanBiasa }}">
                Pengaturan Desa
            </a>

            <p class="px-3 pt-5 pb-2 text-xs font-semibold tracking-wider text-on-surface-variant uppercase">Akun</p>

            <a href="{{ route('admin.kata-sandi') }}"
               class="{{ request()->routeIs('admin.kata-sandi') ? $tautanAktif : $tautanBiasa }}">
                Ganti Kata Sandi
            </a>
        </nav>
    </aside>

    {{-- Latar gelap saat sidebar terbuka di layar kecil --}}
    <div x-show="sidebarTerbuka" x-cloak @click="sidebarTerbuka = false"
         class="fixed inset-0 z-30 bg-surface-container-low/50 lg:hidden"></div>

    <div class="lg:pl-64">

        {{-- Bilah atas --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 sm:px-6">
            <button type="button" @click="sidebarTerbuka = !sidebarTerbuka"
                    class="rounded-[12px] p-2 text-on-surface-variant hover:bg-surface lg:hidden"
                    aria-label="Buka menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <h1 class="flex-1 truncate text-base font-semibold text-slate-900">@yield('judul', 'Dashboard')</h1>

            <div class="flex items-center gap-3">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-medium text-slate-900">{{ auth('admin')->user()->nama }}</p>
                    <p class="text-xs text-on-surface-variant">{{ auth('admin')->user()->email }}</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-700">
                    {{ auth('admin')->user()->inisial() }}
                </div>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-[12px] border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700
                                   transition hover:bg-slate-50">
                        Keluar
                    </button>
                </form>
            </div>
        </header>

        <main class="p-4 sm:p-6">
            <x-admin.notifikasi />

            @yield('konten')
        </main>
    </div>

    <x-admin.pusat-dialog />

    @stack('skrip')
</body>
</html>
