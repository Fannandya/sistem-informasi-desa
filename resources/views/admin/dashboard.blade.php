@extends('admin.layouts.app')

@section('judul', 'Dashboard')

@section('konten')
<div class="space-y-6">

    <div>
        <h2 class="text-lg font-semibold text-slate-900">
            Selamat datang, {{ auth('admin')->user()->nama }}.
        </h2>
        <p class="mt-1 text-sm text-slate-600">Ringkasan kondisi kependudukan {{ $desa->nama_desa }}.</p>
    </div>

    {{-- ------------------------ Kartu ringkasan ------------------------ --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-slate-500">Total Warga</p>
            <p class="mt-1 text-3xl font-semibold text-slate-900">
                {{ number_format($totalWarga, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">warga aktif saat ini</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-slate-500">Jumlah KK</p>

            @if ($kk->adaData())
                <p class="mt-1 text-3xl font-semibold text-slate-900">
                    {{ number_format($kk->total(), 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-slate-400">data tahun {{ $kk->tahunAcuan() }}</p>

                @if (! $kk->lengkap())
                    {{-- PRD 2.9: penanda ini hanya muncul di panel admin,
                         tidak ditampilkan ke publik. --}}
                    <a href="{{ route('admin.rekap-kk.index', ['tahun' => $kk->tahunAcuan()]) }}"
                       class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1
                              text-xs font-medium text-amber-800 transition hover:bg-amber-200">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        {{ $kk->jumlahBelumDiisi() }} RT belum diisi
                    </a>
                @endif
            @else
                <p class="mt-1 text-3xl font-semibold text-slate-300">—</p>
                <a href="{{ route('admin.rekap-kk.index') }}"
                   class="mt-1 inline-block text-xs text-emerald-700 underline hover:text-emerald-800">
                    Isi rekap KK
                </a>
            @endif
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-slate-500">Kelahiran</p>
            <p class="mt-1 text-3xl font-semibold text-slate-900">
                {{ number_format($kelahiran, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">tahun {{ $tahun }}</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-slate-500">Kematian</p>
            <p class="mt-1 text-3xl font-semibold text-slate-900">
                {{ number_format($kematian, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">tahun {{ $tahun }}</p>
        </div>
    </div>

    @if ($kk->rataJiwaPerKk() !== null)
        <p class="text-sm text-slate-600">
            Rata-rata <span class="font-semibold text-slate-900">{{ number_format($kk->rataJiwaPerKk(), 2, ',', '.') }}</span>
            jiwa per keluarga.
        </p>
    @endif

    {{-- ------------------------- Grafik (fitur 06) ------------------------- --}}
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white/50 px-6 py-12 text-center">
        <p class="font-medium text-slate-700">Grafik demografi menyusul</p>
        <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
            Sebaran umur, rasio gender, mata pencaharian, sebaran wilayah, dan tren
            kelahiran–kematian akan muncul di sini pada fitur 06.
        </p>
    </div>
</div>
@endsection
