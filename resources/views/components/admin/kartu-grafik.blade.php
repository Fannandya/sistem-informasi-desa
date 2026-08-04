@props([
    'judul',
    'keterangan' => null,
    'kanvas',
    'tinggi' => 'h-72',
])

{{--
    Wadah grafik dengan tiga keadaan: sedang memuat, kosong, dan gagal.

    Grafik yang gagal memuat tanpa penjelasan terlihat sama persis dengan
    grafik yang datanya memang nol — admin tidak bisa membedakan "belum ada
    warga" dari "sistemnya bermasalah".
--}}

<div data-grafik {{ $attributes->merge(['class' => 'rounded-[12px] bg-surface-container-lowest p-5 shadow-sm ring-1 ring-outline-variant']) }}>
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-on-surface">{{ $judul }}</h2>
            @if ($keterangan)
                <p class="mt-0.5 text-xs text-on-surface-variant">{{ $keterangan }}</p>
            @endif
        </div>
        {{ $aksi ?? '' }}
    </div>

    <div class="relative {{ $tinggi }}">
        <canvas id="{{ $kanvas }}" class="hidden"></canvas>

        <div data-status="muat" class="absolute inset-0 flex items-center justify-center">
            <div class="flex items-center gap-2 text-sm text-on-surface-variant">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/>
                </svg>
                Memuat…
            </div>
        </div>

        <div data-status="kosong" class="absolute inset-0 hidden flex-col items-center justify-center text-center">
            <p class="text-sm font-medium text-on-surface">Belum ada data</p>
            <p class="mt-1 max-w-xs text-xs text-on-surface-variant">
                Grafik ini akan terisi sendiri setelah data warga ditambahkan.
            </p>
        </div>

        <div data-status="gagal" class="absolute inset-0 hidden flex-col items-center justify-center text-center">
            <p class="text-sm font-medium text-on-tertiary-container">Gagal memuat grafik</p>
            <p class="mt-1 max-w-xs text-xs text-on-surface-variant">
                Muat ulang halaman. Bila masih gagal, hubungi pengelola sistem.
            </p>
        </div>
    </div>

    {{ $slot }}
</div>
