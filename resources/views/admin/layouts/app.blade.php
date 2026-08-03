<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('judul', 'Panel Admin') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 text-slate-800" x-data="{ sidebarTerbuka: false }">

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full bg-slate-900 text-slate-300 transition-transform lg:translate-x-0"
           :class="sidebarTerbuka && 'translate-x-0'">

        <div class="flex h-16 items-center gap-3 border-b border-slate-800 px-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 font-bold text-white">D</div>
            <span class="truncate font-semibold text-white">Panel Admin</span>
        </div>

        <nav class="space-y-1 px-3 py-4 text-sm">
            @php
                $tautanAktif = 'flex items-center gap-3 rounded-lg bg-slate-800 px-3 py-2.5 font-medium text-white';
                $tautanBiasa = 'flex items-center gap-3 rounded-lg px-3 py-2.5 transition hover:bg-slate-800 hover:text-white';
            @endphp

            <a href="{{ route('admin.dashboard') }}"
               class="{{ request()->routeIs('admin.dashboard') ? $tautanAktif : $tautanBiasa }}">
                Dashboard
            </a>

            {{-- Menu berikut ditambahkan saat fitur terkait dikerjakan (04–13). --}}
            <p class="px-3 pt-5 pb-2 text-xs font-semibold tracking-wider text-slate-500 uppercase">Akun</p>

            <a href="{{ route('admin.kata-sandi') }}"
               class="{{ request()->routeIs('admin.kata-sandi') ? $tautanAktif : $tautanBiasa }}">
                Ganti Kata Sandi
            </a>
        </nav>
    </aside>

    {{-- Latar gelap saat sidebar terbuka di layar kecil --}}
    <div x-show="sidebarTerbuka" x-cloak @click="sidebarTerbuka = false"
         class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

    <div class="lg:pl-64">

        {{-- Bilah atas --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 sm:px-6">
            <button type="button" @click="sidebarTerbuka = !sidebarTerbuka"
                    class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden"
                    aria-label="Buka menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <h1 class="flex-1 truncate text-base font-semibold text-slate-900">@yield('judul', 'Dashboard')</h1>

            <div class="flex items-center gap-3">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-medium text-slate-900">{{ auth('admin')->user()->nama }}</p>
                    <p class="text-xs text-slate-500">{{ auth('admin')->user()->email }}</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-700">
                    {{ auth('admin')->user()->inisial() }}
                </div>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700
                                   transition hover:bg-slate-50">
                        Keluar
                    </button>
                </form>
            </div>
        </header>

        <main class="p-4 sm:p-6">
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @yield('konten')
        </main>
    </div>

</body>
</html>
