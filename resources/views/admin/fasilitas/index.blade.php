@extends('admin.layouts.app')

@section('judul', 'Fasilitas Umum')

@section('konten')
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">
            <span class="font-semibold text-slate-900">{{ $daftar->total() }}</span> fasilitas terdaftar
        </p>
        <div class="flex items-center gap-2">
            <a href="{{ route('publik.fasilitas') }}" target="_blank"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Lihat Halaman Publik
            </a>
            <a href="{{ route('admin.fasilitas.create') }}"
               class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                Tambah Fasilitas
            </a>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="min-w-48 flex-1">
            <label for="q" class="mb-1.5 block text-sm font-medium text-slate-700">Cari nama</label>
            <input id="q" type="search" name="q" value="{{ $kata }}" placeholder="mis. SDN, Posyandu…"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm
                          focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
        </div>
        <div class="w-56">
            <x-admin.select name="kategori" label="Kategori" :value="$kategori?->value"
                            :pilihan="\App\Enums\KategoriFasilitas::pilihan()" kosong="Semua kategori" />
        </div>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
            Terapkan
        </button>
        @if ($kategori || $kata)
            <a href="{{ route('admin.fasilitas.index') }}"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Bersihkan
            </a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @if ($daftar->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-slate-900">Belum ada fasilitas</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    Catat sekolah, puskesmas, tempat ibadah, balai desa, dan lapangan yang dimiliki desa.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Fasilitas</th>
                            <th class="px-4 py-3 font-semibold">Kategori</th>
                            <th class="px-4 py-3 font-semibold">Lokasi</th>
                            <th class="px-4 py-3 font-semibold">Tampil</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($daftar as $fasilitas)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $fasilitas->urlFotoKecil() }}" alt=""
                                             class="h-12 w-12 rounded-lg bg-slate-100 object-cover">
                                        <p class="font-medium text-slate-900">{{ $fasilitas->nama }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $fasilitas->kategori->warna() }}">
                                        {{ $fasilitas->kategori->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $fasilitas->lokasiTerbaca() ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.fasilitas.tampil', $fasilitas) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-full px-2.5 py-1 text-xs font-medium transition
                                                       {{ $fasilitas->tampil
                                                           ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                                                           : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                            {{ $fasilitas->tampil ? 'Tampil' : 'Sembunyi' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.fasilitas.edit', $fasilitas) }}"
                                           class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">Ubah</a>
                                        <form method="POST" action="{{ route('admin.fasilitas.destroy', $fasilitas) }}"
                                              onsubmit="return confirm('Hapus fasilitas ini? Fotonya ikut terhapus.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-md px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">Hapus</button>
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
