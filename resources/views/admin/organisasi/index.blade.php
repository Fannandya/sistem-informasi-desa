@extends('admin.layouts.app')

@section('judul', 'Kelola Organisasi')

@section('konten')
<div class="grid gap-6 lg:grid-cols-3">

    {{-- ----------------------- Form tambah ----------------------- --}}
    <div class="lg:col-span-1">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-sm font-semibold text-slate-900">Tambah Organisasi</h2>
            <p class="mt-1 mb-5 text-xs text-slate-500">
                Kelompok yang tersedia saat mencatat pengurus, sekaligus judul bagian
                pada halaman Struktur Organisasi publik.
            </p>

            <form method="POST" action="{{ route('admin.organisasi.store') }}" class="space-y-4">
                @csrf

                <x-admin.field name="nama" label="Nama Organisasi" required
                               placeholder="mis. Karang Taruna" />

                <x-admin.field name="urutan" label="Urutan Tampil" type="number"
                               keterangan="Kosongkan untuk menaruhnya di urutan terakhir." />

                <button type="submit"
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                               transition hover:bg-emerald-700">
                    Tambah Organisasi
                </button>
            </form>
        </div>

        <div class="mt-4 rounded-2xl bg-slate-50 p-5 text-xs leading-relaxed text-slate-600">
            <p class="mb-1.5 font-medium text-slate-700">Cara kerjanya</p>
            <ul class="list-disc space-y-1 pl-4">
                <li>Urutan menentukan kelompok mana tampil lebih dulu di halaman publik.</li>
                <li>Organisasi yang masih dipakai pengurus tidak dapat dihapus.</li>
                <li>Nama tidak boleh sama, tanpa memandang besar-kecil huruf.</li>
            </ul>
        </div>
    </div>

    {{-- ------------------------- Daftar ------------------------- --}}
    <div class="lg:col-span-2">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-600">
                <span class="font-semibold text-slate-900">{{ $daftar->count() }}</span> kelompok organisasi
            </p>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.pengurus.index') }}"
                   class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Kelola Pengurus
                </a>
                <a href="{{ route('publik.struktur') }}" target="_blank"
                   class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Lihat Halaman Publik
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            @if ($daftar->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="font-medium text-slate-900">Belum ada organisasi</p>
                    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                        Tambahkan minimal satu kelompok — misalnya Pemerintah Desa — sebelum
                        mencatat pengurus.
                    </p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Urutan</th>
                            <th class="px-4 py-3 font-semibold">Nama Organisasi</th>
                            <th class="px-4 py-3 font-semibold">Pengurus</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($daftar as $organisasi)
                            <tr x-data="{ ubah: false }" class="hover:bg-slate-50">

                                {{-- ---- Tampilan biasa ---- --}}
                                <td class="px-4 py-3" x-show="!ubah">
                                    <div class="flex items-center gap-1">
                                        <span class="w-6 text-slate-500 tabular-nums">{{ $organisasi->urutan }}</span>
                                        @foreach (['naik' => ['&uarr;', 'Naikkan urutan'], 'turun' => ['&darr;', 'Turunkan urutan']] as $arah => [$ikon, $judul])
                                            <form method="POST" action="{{ route('admin.organisasi.geser', $organisasi) }}">
                                                @csrf
                                                <input type="hidden" name="arah" value="{{ $arah }}">
                                                <button type="submit" aria-label="{{ $judul }} {{ $organisasi->nama }}"
                                                        title="{{ $judul }}"
                                                        class="rounded px-1.5 py-0.5 text-slate-400 hover:bg-slate-200 hover:text-slate-700">
                                                    {!! $ikon !!}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-4 py-3 font-medium text-slate-900" x-show="!ubah">
                                    {{ $organisasi->nama }}
                                </td>

                                <td class="px-4 py-3" x-show="!ubah">
                                    @if ($organisasi->pengurus_count > 0)
                                        <a href="{{ route('admin.pengurus.index', ['organisasi_id' => $organisasi->id]) }}"
                                           class="text-slate-600 underline decoration-slate-300 hover:text-emerald-700">
                                            {{ $organisasi->pengurus_count }} pengurus
                                        </a>
                                    @else
                                        <span class="text-slate-400">belum ada</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right" x-show="!ubah">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="ubah = true"
                                                class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">
                                            Ubah
                                        </button>

                                        @if ($organisasi->pengurus_count > 0)
                                            <span class="cursor-not-allowed rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-300"
                                                  title="Masih dipakai {{ $organisasi->pengurus_count }} pengurus">
                                                Hapus
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('admin.organisasi.destroy', $organisasi) }}"
                                                  data-konfirmasi data-bahaya
                                                      data-judul="Hapus organisasi?"
                                                      data-pesan="Kelompok {{ $organisasi->nama }} akan dihapus dari daftar organisasi."
                                                      data-tombol="Hapus">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="rounded-md px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>

                                {{-- ---- Baris sunting ---- --}}
                                <td colspan="4" class="px-4 py-3" x-show="ubah" x-cloak>
                                    <form method="POST" action="{{ route('admin.organisasi.update', $organisasi) }}"
                                          class="flex flex-wrap items-end gap-3">
                                        @csrf @method('PUT')

                                        <div class="min-w-48 flex-1">
                                            <label class="mb-1 block text-xs text-slate-500">Nama Organisasi</label>
                                            <input name="nama" value="{{ $organisasi->nama }}" required maxlength="60"
                                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                        </div>

                                        <div class="w-28">
                                            <label class="mb-1 block text-xs text-slate-500">Urutan</label>
                                            <input name="urutan" type="number" value="{{ $organisasi->urutan }}"
                                                   min="0" max="999"
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
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
