@extends('admin.layouts.app')

@section('judul', 'Galeri Acara')

@section('konten')
<div class="space-y-5">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">
            <span class="font-semibold text-slate-900">{{ $daftar->total() }}</span> dokumentasi kegiatan
        </p>
        <div class="flex items-center gap-2">
            <a href="{{ route('publik.galeri.index') }}" target="_blank"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Lihat Halaman Publik
            </a>
            <a href="{{ route('admin.acara.create') }}"
               class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                Tulis Dokumentasi
            </a>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="w-48">
            <x-admin.select name="status" label="Status" :value="$status"
                            :pilihan="['draf' => 'Draf', 'terbit' => 'Terbit']" kosong="Semua status" />
        </div>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
            Terapkan
        </button>
    </form>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @if ($daftar->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-slate-900">Belum ada dokumentasi kegiatan</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    Abadikan kerja bakti, musyawarah, perayaan, dan kegiatan lain agar warga
                    bisa melihatnya kembali.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Kegiatan</th>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 font-semibold">Foto</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($daftar as $acara)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $acara->urlSampulKecil() }}" alt=""
                                             class="h-12 w-16 rounded-lg bg-slate-100 object-cover">
                                        <div class="min-w-0">
                                            <p class="font-medium text-slate-900">{{ $acara->judul }}</p>
                                            @if (filled($acara->lokasi))
                                                <p class="truncate text-xs text-slate-500">{{ $acara->lokasi }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ $acara->tanggal_acara->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="{{ $acara->foto_count === 0 ? 'text-amber-600' : 'text-slate-600' }}">
                                        {{ $acara->foto_count }} foto
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.acara.status', $acara) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-full px-2.5 py-1 text-xs font-medium transition
                                                       {{ $acara->sudahTerbit()
                                                           ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                                                           : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                            {{ $acara->sudahTerbit() ? 'Terbit' : 'Draf' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($acara->sudahTerbit())
                                            <a href="{{ route('publik.galeri.show', $acara) }}" target="_blank"
                                               class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-500 hover:bg-slate-200">Lihat</a>
                                        @endif
                                        <a href="{{ route('admin.acara.edit', $acara) }}"
                                           class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">Ubah</a>
                                        <form method="POST" action="{{ route('admin.acara.destroy', $acara) }}"
                                              onsubmit="return confirm('Hapus dokumentasi ini beserta {{ $acara->foto_count }} fotonya?')">
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
