@extends('admin.layouts.app')

@section('judul', 'Carousel Beranda')

@section('konten')
<div class="space-y-5">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-on-surface-variant">
            <span class="font-semibold text-on-surface">{{ $gambar->count() }}</span> gambar carousel
        </p>
        <a href="{{ route('admin.carousel.create') }}"
           class="rounded-[12px] bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary/90">
            Tambah Gambar
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl bg-surface shadow-sm ring-1 ring-outline">
        @if ($gambar->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-on-surface">Belum ada gambar carousel</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-on-surface-variant">
                    Tambahkan gambar sampul untuk hero section di halaman beranda.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface-container-low text-left text-xs tracking-wider text-on-surface-variant uppercase">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Gambar</th>
                            <th class="px-4 py-3 font-semibold">Judul</th>
                            <th class="px-4 py-3 font-semibold">Urutan</th>
                            <th class="px-4 py-3 font-semibold">Tampil</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline">
                        @foreach ($gambar as $item)
                            <tr class="hover:bg-surface-container-low">
                                <td class="px-4 py-3">
                                    @if ($item->path)
                                        <img src="{{ app(\App\Services\LayananUnggahGambar::class)->urlThumbnail($item->path) }}"
                                             alt="{{ $item->judul }}"
                                             class="h-10 w-16 rounded object-cover">
                                    @else
                                        <span class="text-on-surface-variant">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    {{ $item->judul ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if ($loop->first)
                                            <span class="text-on-surface-variant">{{ $item->urutan }}</span>
                                        @else
                                            <form method="POST" action="{{ route('admin.carousel.geser', $item) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="arah" value="atas">
                                                <button type="submit"
                                                    class="rounded px-2 py-1 text-on-surface hover:bg-surface-container-low text-xs"
                                                    title="Geser ke atas">
                                                    <span class="material-symbols-outlined text-base">arrow_upward</span>
                                                </button>
                                            </form>
                                        @endif
                                        <span>{{ $item->urutan }}</span>
                                        @if (!$loop->last)
                                            <form method="POST" action="{{ route('admin.carousel.geser', $item) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="arah" value="bawah">
                                                <button type="submit"
                                                    class="rounded px-2 py-1 text-on-surface hover:bg-surface-container-low text-xs"
                                                    title="Geser ke bawah">
                                                    <span class="material-symbols-outlined text-base">arrow_downward</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.carousel.update', $item) }}"
                                        class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="tampil" value="{{ $item->tampil ? '0' : '1' }}">
                                        <button type="submit"
                                            class="text-xs font-semibold px-2.5 py-1.5 rounded-lg transition"
                                            :class="@json($item->tampil)
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-gray-100 text-gray-700'">
                                            {{ $item->tampil ? 'Ya' : 'Tidak' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.carousel.edit', $item) }}"
                                           class="text-primary hover:text-primary/80 font-medium">
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('admin.carousel.destroy', $item) }}"
                                            class="inline"
                                            data-konfirmasi
                                            data-judul="Hapus gambar carousel?"
                                            data-pesan="Gambar akan dihapus dari sistem."
                                            data-tombol="Hapus"
                                            data-bahaya>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error hover:text-error/80 font-medium">
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
        @endif
    </div>

</div>
@endsection
