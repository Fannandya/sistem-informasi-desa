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

    {{-- ------------------------- Kelola organisasi ------------------------- --}}
    <div x-data="{ buka: {{ $organisasiList->isEmpty() ? 'true' : 'false' }} }"
         class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">

        <button type="button" @click="buka = !buka"
                class="flex w-full items-center justify-between px-5 py-4 text-left">
            <span>
                <span class="block text-sm font-semibold text-slate-900">Kelompok Organisasi</span>
                <span class="block text-xs text-slate-500">
                    {{ $organisasiList->count() }} kelompok · urutannya menentukan susunan di halaman publik
                </span>
            </span>
            <svg class="h-4 w-4 text-slate-400 transition" :class="buka && 'rotate-180'"
                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="buka" x-cloak class="border-t border-slate-200 p-5">
            <form method="POST" action="{{ route('admin.organisasi.store') }}"
                  class="mb-5 flex flex-wrap items-end gap-3">
                @csrf
                <div class="min-w-48 flex-1">
                    <label class="mb-1 block text-xs text-slate-500">Nama Organisasi</label>
                    <input name="nama" required placeholder="mis. Karang Taruna"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="w-28">
                    <label class="mb-1 block text-xs text-slate-500">Urutan</label>
                    <input name="urutan" type="number" value="0"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                    Tambah
                </button>
            </form>

            @if ($organisasiList->isEmpty())
                <p class="text-sm text-slate-500">
                    Belum ada kelompok organisasi. Tambahkan minimal satu sebelum mencatat pengurus.
                </p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($organisasiList as $organisasi)
                        <div x-data="{ ubah: false }" class="flex flex-wrap items-center gap-3 py-2.5">
                            <span class="w-10 text-xs text-slate-400" x-show="!ubah">#{{ $organisasi->urutan }}</span>
                            <span class="flex-1 text-sm font-medium text-slate-900" x-show="!ubah">
                                {{ $organisasi->nama }}
                            </span>
                            <span class="text-xs text-slate-500" x-show="!ubah">
                                {{ $organisasi->pengurus_count }} pengurus
                            </span>
                            <div class="flex items-center gap-1" x-show="!ubah">
                                <button type="button" @click="ubah = true"
                                        class="rounded-md px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-200">
                                    Ubah
                                </button>
                                @if ($organisasi->pengurus_count > 0)
                                    <span class="cursor-not-allowed px-2.5 py-1 text-xs text-slate-300"
                                          title="Masih dipakai {{ $organisasi->pengurus_count }} pengurus">Hapus</span>
                                @else
                                    <form method="POST" action="{{ route('admin.organisasi.destroy', $organisasi) }}"
                                          onsubmit="return confirm('Hapus organisasi {{ $organisasi->nama }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="rounded-md px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.organisasi.update', $organisasi) }}"
                                  class="flex w-full flex-wrap items-end gap-3" x-show="ubah" x-cloak>
                                @csrf @method('PUT')
                                <div class="min-w-40 flex-1">
                                    <input name="nama" value="{{ $organisasi->nama }}" required
                                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                </div>
                                <div class="w-24">
                                    <input name="urutan" type="number" value="{{ $organisasi->urutan }}"
                                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                </div>
                                <button type="submit"
                                        class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                                    Simpan
                                </button>
                                <button type="button" @click="ubah = false"
                                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    Batal
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
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
