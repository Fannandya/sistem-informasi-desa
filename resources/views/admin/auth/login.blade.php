<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk Panel Admin — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 text-slate-800">

    <main class="flex min-h-full items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm">

            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-600 text-2xl font-bold text-white">
                    D
                </div>
                <h1 class="text-xl font-semibold text-slate-900">Panel Admin Desa</h1>
                <p class="mt-1 text-sm text-slate-500">{{ config('app.name') }}</p>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.proses') }}"
                  class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                @csrf

                @error('email')
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $message }}
                    </div>
                @enderror

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" required autofocus autocomplete="username"
                           value="{{ old('email') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm
                                  focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Kata Sandi</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm
                                  focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1"
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    Ingat saya di perangkat ini
                </label>

                <button type="submit"
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                               transition hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500/40 focus:outline-none">
                    Masuk
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-slate-500">
                Lupa kata sandi? Hubungi pengelola sistem —
                pemulihan akun dilakukan dari server, bukan lewat halaman ini.
            </p>

            <p class="mt-4 text-center text-sm">
                <a href="{{ route('beranda') }}" class="text-slate-500 underline hover:text-slate-700">
                    Kembali ke halaman desa
                </a>
            </p>
        </div>
    </main>

</body>
</html>
