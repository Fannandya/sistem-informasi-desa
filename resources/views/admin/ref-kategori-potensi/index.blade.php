@extends('admin.layouts.app')

@section('judul', 'Kategori Potensi Desa')

@section('konten')
<div class="space-y-5">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-on-surface-variant">
            <span class="font-semibold text-on-surface">{{ $daftar->count() }}</span> kategori terdaftar
        </p>
    </div>

    <div class="overflow-hidden rounded-2xl bg-surface shadow-sm ring-1 ring-outline">
        <div class="px-6 py-4 border-b border-outline bg-surface-container-low">
            <form method="POST" action="{{ route('admin.ref-kategori-potensi.store') }}" class="flex gap-3">
                @csrf
                <input type="text" name="nama" placeholder="Nama kategori potensi baru..."
                       class="flex-1 rounded-[12px] border border-outline px-3 py-2.5 text-sm
                              focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none"
                       required>
                <button type="submit"
                        class="rounded-[12px] bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary/90">
                    Tambah
                </button>
            </form>
            @if ($errors->has('nama'))
                <p class="mt-2 text-xs text-error">{{ $errors->first('nama') }}</p>
            @endif
        </div>

        @if ($daftar->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-on-surface">Belum ada kategori potensi</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-on-surface-variant">
                    Buat kategori untuk mengelompokkan potensi desa.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface-container-low text-left text-xs tracking-wider text-on-surface-variant uppercase">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Kategori</th>
                            <th class="px-4 py-3 font-semibold">Jumlah Potensi</th>
                            <th class="px-4 py-3 font-semibold">Urutan</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline">
                        @foreach ($daftar as $item)
                            <tr class="hover:bg-surface-container-low">
                                <td class="px-4 py-3 font-medium text-on-surface">
                                    {{ $item->nama }}
                                </td>
                                <td class="px-4 py-3 text-on-surface-variant">
                                    {{ $item->potensi()->count() }} potensi
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if (!$loop->first)
                                            <form method="POST" action="{{ route('admin.ref-kategori-potensi.geser', $item) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="arah" value="atas">
                                                <button type="submit"
                                                    class="rounded px-2 py-1 text-on-surface hover:bg-surface-container text-xs"
                                                    title="Geser ke atas">
                                                    <span class="material-symbols-outlined text-base">arrow_upward</span>
                                                </button>
                                            </form>
                                        @endif
                                        <span>{{ $item->urutan }}</span>
                                        @if (!$loop->last)
                                            <form method="POST" action="{{ route('admin.ref-kategori-potensi.geser', $item) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="arah" value="bawah">
                                                <button type="submit"
                                                    class="rounded px-2 py-1 text-on-surface hover:bg-surface-container text-xs"
                                                    title="Geser ke bawah">
                                                    <span class="material-symbols-outlined text-base">arrow_downward</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.ref-kategori-potensi.destroy', $item) }}"
                                            class="inline"
                                            data-konfirmasi
                                            data-judul="Hapus kategori potensi?"
                                            data-pesan="Kategori akan dihapus dari sistem."
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
