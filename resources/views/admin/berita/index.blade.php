@extends('admin.layouts.app')

@section('judul', 'Berita Desa')

@section('konten')
<div class="space-y-5">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">
            <span class="font-semibold text-slate-900">{{ $daftar->total() }}</span> berita
        </p>
        <div class="flex items-center gap-2">
            <a href="{{ route('publik.berita.index') }}" target="_blank"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Lihat Halaman Publik
            </a>
            <a href="{{ route('admin.berita.create') }}"
               class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                Tulis Berita
            </a>
        </div>
    </div>

    {{-- Kelola kategori --}}
    <div x-data="{ buka: {{ $kategoriList->isEmpty() ? 'true' : 'false' }} }"
         class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <button type="button" @click="buka = !buka" class="flex w-full items-center justify-between px-5 py-4 text-left">
            <span>
                <span class="block text-sm font-semibold text-slate-900">Kategori Berita</span>
                <span class="block text-xs text-slate-500">{{ $kategoriList->count() }} kategori</span>
            </span>
            <svg class="h-4 w-4 text-slate-400 transition" :class="buka && 'rotate-180'"
                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="buka" x-cloak class="border-t border-slate-200 p-5">
            <form method="POST" action="{{ route('admin.kategori-berita.store') }}" class="mb-4 flex flex-wrap items-end gap-3">
                @csrf
                <div class="min-w-48 flex-1">
                    <label class="mb-1 block text-xs text-slate-500">Nama Kategori</label>
                    <input name="nama" required placeholder="mis. Pengumuman"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                    Tambah
                </button>
            </form>

            @if ($kategoriList->isEmpty())
                <p class="text-sm text-slate-500">Belum ada kategori. Berita tetap bisa ditulis tanpa kategori.</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach ($kategoriList as $k)
                        <span class="flex items-center gap-2 rounded-full bg-slate-100 py-1 pr-1 pl-3 text-sm">
                            {{ $k->nama }}
                            <span class="text-xs text-slate-400">{{ $k->berita_count }}</span>
                            @if ($k->berita_count === 0)
                                <form method="POST" action="{{ route('admin.kategori-berita.destroy', $k) }}"
                                      data-konfirmasi data-bahaya
                                      data-judul="Hapus kategori berita?"
                                      data-pesan="Kategori {{ $k->nama }} akan dihapus."
                                      data-tombol="Hapus">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-full px-1.5 text-slate-400 hover:text-red-600">&times;</button>
                                </form>
                            @endif
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Saringan --}}
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="min-w-48 flex-1">
            <label for="q" class="mb-1.5 block text-sm font-medium text-slate-700">Cari judul</label>
            <input id="q" type="search" name="q" value="{{ $kata }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm
                          focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
        </div>
        <div class="w-48">
            <x-admin.select name="status" label="Status" :value="$status"
                            :pilihan="['draf' => 'Draf', 'terbit' => 'Terbit']" kosong="Semua status" />
        </div>
        <div class="w-48">
            <x-admin.select name="kategori_id" label="Kategori" :value="$kategoriId"
                            :pilihan="$kategoriList->pluck('nama', 'id')" kosong="Semua kategori" />
        </div>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
            Terapkan
        </button>
    </form>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @if ($daftar->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-slate-900">Belum ada berita</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    Sampaikan pengumuman, kegiatan, dan kabar desa kepada warga lewat halaman berita.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Berita</th>
                            <th class="px-4 py-3 font-semibold">Kategori</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Utama</th>
                            <th class="px-4 py-3 font-semibold">Dilihat</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($daftar as $berita)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $berita->urlSampulKecil() }}" alt=""
                                             class="h-12 w-16 rounded-lg bg-slate-100 object-cover">
                                        <div class="min-w-0">
                                            <p class="font-medium text-slate-900">{{ $berita->judul }}</p>
                                            <p class="text-xs text-slate-400">
                                                {{ $berita->tanggal_terbit?->translatedFormat('d M Y') ?? 'belum terbit' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $berita->kategori?->nama ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.berita.status', $berita) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-full px-2.5 py-1 text-xs font-medium transition
                                                       {{ $berita->sudahTerbit()
                                                           ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                                                           : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                            {{ $berita->sudahTerbit() ? 'Terbit' : 'Draf' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.berita.utama', $berita) }}">
                                        @csrf
                                        <button type="submit" title="Jadikan berita utama"
                                                class="text-lg leading-none transition
                                                       {{ $berita->utama ? 'text-amber-500' : 'text-slate-300 hover:text-amber-400' }}">
                                            {{ $berita->utama ? '★' : '☆' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-slate-500 tabular-nums">{{ number_format($berita->dilihat, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($berita->sudahTerbit())
                                            <a href="{{ route('publik.berita.show', $berita) }}" target="_blank"
                                               class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-500 hover:bg-slate-200">Lihat</a>
                                        @endif
                                        <a href="{{ route('admin.berita.edit', $berita) }}"
                                           class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">Ubah</a>
                                        <form method="POST" action="{{ route('admin.berita.destroy', $berita) }}"
                                              data-konfirmasi data-bahaya
                                              data-judul="Hapus berita?"
                                              data-pesan="{{ $berita->judul }} akan dihapus permanen beserta gambar sampulnya."
                                              data-catatan="Tautan berita ini yang sudah tersebar akan mati."
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
