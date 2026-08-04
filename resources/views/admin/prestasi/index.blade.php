@extends('admin.layouts.app')

@section('judul', 'Prestasi Desa')

@section('konten')
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">
            <span class="font-semibold text-slate-900">{{ $daftar->total() }}</span> prestasi tercatat
        </p>
        <div class="flex items-center gap-2">
            <a href="{{ route('publik.prestasi') }}" target="_blank"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Lihat Halaman Publik
            </a>
            <a href="{{ route('admin.prestasi.create') }}"
               class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                Tambah Prestasi
            </a>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="w-56">
            <x-admin.select name="tingkat" label="Saring tingkat" :value="$tingkat?->value"
                            :pilihan="\App\Enums\TingkatPrestasi::pilihan()" kosong="Semua tingkat" />
        </div>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
            Terapkan
        </button>
        @if ($tingkat)
            <a href="{{ route('admin.prestasi.index') }}"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Bersihkan
            </a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @if ($daftar->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-slate-900">Belum ada prestasi tercatat</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    Catat penghargaan, program unggulan, dan inovasi yang pernah diraih desa.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Prestasi</th>
                            <th class="px-4 py-3 font-semibold">Tahun</th>
                            <th class="px-4 py-3 font-semibold">Tingkat</th>
                            <th class="px-4 py-3 font-semibold">Tampil</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($daftar as $prestasi)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $prestasi->urlFotoKecil() }}" alt=""
                                             class="h-12 w-12 rounded-lg bg-slate-100 object-cover">
                                        <div class="min-w-0">
                                            <p class="font-medium text-slate-900">{{ $prestasi->judul }}</p>
                                            @if (filled($prestasi->penyelenggara))
                                                <p class="truncate text-xs text-slate-500">{{ $prestasi->penyelenggara }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 tabular-nums">{{ $prestasi->tahun }}</td>
                                <td class="px-4 py-3">
                                    @if ($prestasi->tingkat)
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $prestasi->tingkat->warna() }}">
                                            {{ $prestasi->tingkat->label() }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.prestasi.tampil', $prestasi) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-full px-2.5 py-1 text-xs font-medium transition
                                                       {{ $prestasi->tampil
                                                           ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                                                           : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                            {{ $prestasi->tampil ? 'Tampil' : 'Sembunyi' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.prestasi.edit', $prestasi) }}"
                                           class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">Ubah</a>
                                        <form method="POST" action="{{ route('admin.prestasi.destroy', $prestasi) }}"
                                              data-konfirmasi data-bahaya
                                              data-judul="Hapus prestasi?"
                                              data-pesan="{{ $prestasi->judul }} ({{ $prestasi->tahun }}) akan dihapus permanen beserta fotonya."
                                              data-tombol="Hapus Permanen">
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
