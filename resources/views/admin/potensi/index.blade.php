@extends('admin.layouts.app')

@section('judul', 'Potensi Desa')

@section('konten')
<div class="space-y-5">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">
            <span class="font-semibold text-slate-900">{{ $daftar->total() }}</span> potensi terdaftar
        </p>
        <div class="flex items-center gap-2">
            <a href="{{ route('publik.potensi.index') }}" target="_blank"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Lihat Halaman Publik
            </a>
            <a href="{{ route('admin.potensi.create') }}"
               class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                Tambah Potensi
            </a>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="w-56">
            <x-admin.select name="kategori" label="Saring kategori" :value="$kategori?->value"
                            :pilihan="\App\Enums\KategoriPotensi::pilihan()" kosong="Semua kategori" />
        </div>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
            Terapkan
        </button>
        @if ($kategori)
            <a href="{{ route('admin.potensi.index') }}"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Bersihkan
            </a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @if ($daftar->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-slate-900">Belum ada potensi desa</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    Catat UMKM, hasil bumi, tempat wisata, dan kerajinan yang dimiliki desa
                    agar dikenal masyarakat luas.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Potensi</th>
                            <th class="px-4 py-3 font-semibold">Kategori</th>
                            <th class="px-4 py-3 font-semibold">Urutan</th>
                            <th class="px-4 py-3 font-semibold">Tampil</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($daftar as $potensi)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $potensi->urlFotoKecil() }}" alt=""
                                             class="h-12 w-12 rounded-lg bg-slate-100 object-cover">
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-slate-900">{{ $potensi->nama }}</p>
                                            <p class="truncate text-xs text-slate-400">/potensi/{{ $potensi->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $potensi->kategori->warna() }}">
                                        {{ $potensi->kategori->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500 tabular-nums">{{ $potensi->urutan }}</td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.potensi.tampil', $potensi) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-full px-2.5 py-1 text-xs font-medium transition
                                                       {{ $potensi->tampil
                                                           ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                                                           : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                            {{ $potensi->tampil ? 'Tampil' : 'Sembunyi' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.potensi.edit', $potensi) }}"
                                           class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('admin.potensi.destroy', $potensi) }}"
                                              data-konfirmasi data-bahaya
                                              data-judul="Hapus potensi desa?"
                                              data-pesan="{{ $potensi->nama }} akan dihapus permanen beserta fotonya, dan halaman detailnya di situs desa ikut hilang."
                                              data-catatan="Untuk sekadar menyembunyikannya dari halaman publik, gunakan tombol status Tampil."
                                              data-tombol="Hapus Permanen">
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
