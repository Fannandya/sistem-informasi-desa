@extends('admin.layouts.app')

@section('judul', 'Pekerjaan')

@section('konten')
<div class="grid gap-6 lg:grid-cols-3">

    <div class="lg:col-span-1">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-sm font-semibold text-slate-900">Tambah Pekerjaan</h2>
            <p class="mt-1 mb-5 text-xs text-slate-500">
                Daftar ini yang muncul sebagai pilihan pada formulir warga, dan menjadi
                kategori pada grafik mata pencaharian.
            </p>

            <form method="POST" action="{{ route('admin.pekerjaan.store') }}" class="space-y-4">
                @csrf
                <x-admin.field name="nama" label="Nama Pekerjaan" required placeholder="mis. Petani" />

                <button type="submit"
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                               transition hover:bg-emerald-700">
                    Tambah Pekerjaan
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            @if ($daftar->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="font-medium text-slate-900">Belum ada daftar pekerjaan</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Tambahkan jenis pekerjaan yang umum di desa ini.
                    </p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">Dipakai</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($daftar as $pekerjaan)
                            <tr x-data="{ ubah: false }" class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900" x-show="!ubah">
                                    {{ $pekerjaan->nama }}
                                </td>
                                <td class="px-4 py-3 text-slate-600" x-show="!ubah">
                                    {{ $pekerjaan->penduduk_count }} warga
                                </td>
                                <td class="px-4 py-3 text-right" x-show="!ubah">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="ubah = true"
                                                class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">
                                            Ubah
                                        </button>

                                        @if ($pekerjaan->penduduk_count > 0)
                                            <span class="cursor-not-allowed rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-300"
                                                  title="Masih dipakai {{ $pekerjaan->penduduk_count }} data warga">
                                                Hapus
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('admin.pekerjaan.destroy', $pekerjaan) }}"
                                                  data-konfirmasi data-bahaya
                                                  data-judul="Hapus pekerjaan?"
                                                  data-pesan="Pekerjaan {{ $pekerjaan->nama }} akan dihapus dari daftar referensi."
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

                                <td colspan="3" class="px-4 py-3" x-show="ubah" x-cloak>
                                    <form method="POST" action="{{ route('admin.pekerjaan.update', $pekerjaan) }}"
                                          class="flex flex-wrap items-end gap-3">
                                        @csrf @method('PUT')
                                        <div class="min-w-48 flex-1">
                                            <label class="mb-1 block text-xs text-slate-500">Nama Pekerjaan</label>
                                            <input name="nama" value="{{ $pekerjaan->nama }}" required
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
