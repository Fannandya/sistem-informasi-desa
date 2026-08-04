@extends('admin.layouts.app')

@section('judul', 'Data Warga')

@section('konten')
<div class="space-y-5">

    {{-- Kepala halaman --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-slate-600">
                <span class="font-semibold text-slate-900">{{ number_format($daftar->total(), 0, ',', '.') }}</span>
                warga aktif{{ $pencarian->adaFilter() ? ' cocok dengan saringan' : '' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <x-admin.tombol-ekspor :pencarian="$pencarian" :ada-saringan="$pencarian->adaFilter()" />

            <a href="{{ route('admin.penduduk.impor') }}"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Impor
            </a>
            <a href="{{ route('admin.penduduk.create') }}"
               class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Tambah Warga
            </a>
        </div>
    </div>

    {{-- Saringan --}}
    <form method="GET" action="{{ route('admin.penduduk.index') }}"
          x-data="{ lanjutan: {{ $pencarian->adaFilter() ? 'true' : 'false' }} }"
          class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">

        <div class="flex flex-wrap gap-3">
            <div class="min-w-56 flex-1">
                <input type="search" name="q" value="{{ $pencarian->nilai('q') }}"
                       placeholder="Cari nama warga…"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm
                              focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
            </div>

            <button type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800">
                Cari
            </button>

            <button type="button" @click="lanjutan = !lanjutan"
                    class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                <span x-text="lanjutan ? 'Sembunyikan saringan' : 'Saringan lainnya'"></span>
            </button>
        </div>

        <div x-show="lanjutan" x-cloak class="mt-4 grid gap-4 border-t border-slate-200 pt-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-admin.select name="dusun" label="Dusun" :value="$pencarian->nilai('dusun')"
                            :pilihan="$dusunList->mapWithKeys(fn ($d) => [$d => $d])" kosong="Semua dusun" />

            <x-admin.field name="rw" label="RW" :value="$pencarian->nilai('rw')" placeholder="mis. 001" />
            <x-admin.field name="rt" label="RT" :value="$pencarian->nilai('rt')" placeholder="mis. 002" />

            <x-admin.select name="jenis_kelamin" label="Jenis Kelamin" :value="$pencarian->nilai('jenis_kelamin')"
                            :pilihan="\App\Enums\JenisKelamin::pilihan()" kosong="Semua" />

            <x-admin.select name="kelompok_umur" label="Kelompok Umur" :value="$pencarian->nilai('kelompok_umur')"
                            :pilihan="\App\Enums\KelompokUmur::pilihan()" kosong="Semua umur" />

            <x-admin.select name="pekerjaan_id" label="Pekerjaan" :value="$pencarian->nilai('pekerjaan_id')"
                            :pilihan="$pekerjaanList->pluck('nama', 'id')" kosong="Semua pekerjaan" />

            <x-admin.select name="pendidikan" label="Pendidikan" :value="$pencarian->nilai('pendidikan')"
                            :pilihan="\App\Enums\Pendidikan::pilihan()" kosong="Semua jenjang" />

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800">
                    Terapkan
                </button>
                @if ($pencarian->adaFilter())
                    <a href="{{ route('admin.penduduk.index') }}"
                       class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        Bersihkan
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Chip saringan aktif — tiap chip bisa dilepas satu per satu --}}
    @if ($pencarian->adaFilter())
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm text-slate-500">Saringan aktif:</span>
            @foreach ($pencarian->aktif() as $chip)
                <a href="{{ route('admin.penduduk.index', $chip['sisa']) }}"
                   class="group inline-flex items-center gap-1.5 rounded-full bg-slate-200 py-1 pr-2 pl-3 text-xs
                          font-medium text-slate-700 transition hover:bg-slate-300"
                   title="Hapus saringan ini">
                    {{ $chip['label'] }}
                    <svg class="h-3.5 w-3.5 text-slate-500 group-hover:text-slate-800" fill="none"
                         stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Tabel --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @if ($daftar->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-slate-900">
                    {{ $pencarian->adaFilter() ? 'Tidak ada warga yang cocok' : 'Belum ada data warga' }}
                </p>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    {{ $pencarian->adaFilter()
                        ? 'Coba longgarkan saringannya, atau bersihkan semuanya untuk melihat seluruh warga.'
                        : 'Tambahkan warga satu per satu, atau impor sekaligus dari berkas Excel.' }}
                </p>
                <div class="mt-5">
                    @if ($pencarian->adaFilter())
                        <a href="{{ route('admin.penduduk.index') }}"
                           class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Bersihkan saringan
                        </a>
                    @else
                        <a href="{{ route('admin.penduduk.create') }}"
                           class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                            Tambah Warga
                        </a>
                        <a href="{{ route('admin.penduduk.impor') }}"
                           class="ml-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Impor dari Excel
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">Umur</th>
                            <th class="px-4 py-3 font-semibold">L/P</th>
                            <th class="px-4 py-3 font-semibold">Wilayah</th>
                            <th class="px-4 py-3 font-semibold">Pekerjaan</th>
                            <th class="px-4 py-3 font-semibold">Pendidikan</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($daftar as $warga)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $warga->nama_lengkap }}</td>
                                <td class="px-4 py-3 whitespace-nowrap {{ $warga->umur() === null ? 'text-slate-400 italic' : 'text-slate-600' }}">
                                    {{ $warga->umurTerbaca() }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $warga->jenis_kelamin->singkat() }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ $warga->wilayah->dusun }}
                                    <span class="text-slate-400">· {{ $warga->wilayah->labelSingkat() }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $warga->pekerjaan?->nama ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $warga->pendidikan?->label() ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.penduduk.edit', $warga) }}"
                                           class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-200">
                                            Ubah
                                        </a>
                                        <x-admin.dialog-arsip :warga="$warga" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($daftar->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $daftar->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
