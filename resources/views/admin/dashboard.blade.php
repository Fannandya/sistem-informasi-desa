@extends('admin.layouts.app')

@section('judul', 'Dashboard')

@section('konten')
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-lg font-semibold text-slate-900">
            Selamat datang, {{ auth('admin')->user()->nama }}.
        </h2>
        <p class="mt-2 max-w-2xl text-sm text-slate-600">
            Kartu ringkasan dan grafik demografi akan muncul di halaman ini setelah
            <strong>fitur 06 — Statistik Warga</strong> dikerjakan. Fitur itu membutuhkan
            data penduduk (fitur 04) dan rekap KK (fitur 05) lebih dulu.
        </p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (['Total Warga', 'Jumlah KK', 'Kelahiran', 'Kematian'] as $label)
                <div class="rounded-xl border border-dashed border-slate-300 p-4">
                    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-300">—</p>
                    <p class="mt-1 text-xs text-slate-400">menunggu fitur 06</p>
                </div>
            @endforeach
        </div>
    </div>
@endsection
