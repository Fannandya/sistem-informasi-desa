@extends('admin.layouts.app')

@section('judul', 'Struktur Organisasi')

@section('konten')
<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">
            <span class="font-semibold text-slate-900">{{ $daftar->total() }}</span> pengurus terdaftar
        </p>
        <div class="flex items-center gap-2">
            <a href="{{ route('publik.struktur') }}" target="_blank"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Lihat Halaman Publik
            </a>
            <a href="{{ route('admin.pengurus.create') }}"
               class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Tambah Pengurus
            </a>
        </div>
    </div>

    {{-- Organisasi dikelola di halamannya sendiri agar halaman ini tetap
         fokus pada pengurus. --}}
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-200">
        <div>
            <p class="text-sm font-semibold text-slate-900">Kelompok Organisasi</p>
            <p class="mt-0.5 text-xs text-slate-500">
                {{ $organisasiList->count() }} kelompok terdaftar · urutannya menentukan susunan di halaman publik
            </p>
        </div>
        <a href="{{ route('admin.organisasi.index') }}"
           class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
            Kelola Organisasi
        </a>
    </div>

    {{-- --------------------------- Daftar pengurus --------------------------- --}}
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="w-64">
            <x-admin.select name="organisasi_id" label="Saring per organisasi" :value="$organisasiId"
                            :pilihan="$organisasiList->pluck('nama', 'id')" kosong="Semua organisasi" />
        </div>
        <button type="submit"
                class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
            Terapkan
        </button>
        @if ($organisasiId)
            <a href="{{ route('admin.pengurus.index') }}"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Bersihkan
            </a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @if ($daftar->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-slate-900">Belum ada pengurus</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    Tambahkan Kepala Desa, perangkat desa, ketua RT/RW, dan pengurus organisasi lain
                    agar warga tahu kepada siapa harus menghubungi.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Pengurus</th>
                            <th class="px-4 py-3 font-semibold">Organisasi</th>
                            <th class="px-4 py-3 font-semibold">Kontak</th>
                            <th class="px-4 py-3 font-semibold">Urutan</th>
                            <th class="px-4 py-3 font-semibold">Tampil</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($daftar as $pengurus)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $pengurus->urlFotoKecil() }}" alt=""
                                             class="h-10 w-10 rounded-lg bg-slate-100 object-cover">
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-slate-900">{{ $pengurus->nama }}</p>
                                            <p class="truncate text-xs text-slate-500">{{ $pengurus->jabatan }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $pengurus->organisasi->nama }}
                                    @if ($pengurus->keteranganWilayah())
                                        <span class="block text-xs text-slate-400">{{ $pengurus->keteranganWilayah() }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ $pengurus->nomor_hp ?: '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1">
                                        <span class="w-6 text-slate-500 tabular-nums">{{ $pengurus->urutan }}</span>
                                        @foreach (['naik' => '&uarr;', 'turun' => '&darr;'] as $arah => $ikon)
                                            <form method="POST" action="{{ route('admin.pengurus.geser', $pengurus) }}">
                                                @csrf
                                                <input type="hidden" name="arah" value="{{ $arah }}">
                                                <button type="submit" title="Geser {{ $arah }}"
                                                        class="rounded px-1.5 py-0.5 text-slate-400 hover:bg-slate-200 hover:text-slate-700">
                                                    {!! $ikon !!}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.pengurus.tampil', $pengurus) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-full px-2.5 py-1 text-xs font-medium transition
                                                       {{ $pengurus->tampil
                                                           ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                                                           : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                            {{ $pengurus->tampil ? 'Tampil' : 'Sembunyi' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.pengurus.edit', $pengurus) }}"
                                           class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('admin.pengurus.destroy', $pengurus) }}"
                                              onsubmit="return confirm('Hapus {{ $pengurus->nama }} secara permanen? Fotonya ikut terhapus.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-md px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($daftar->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">{{ $daftar->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection
