@extends('admin.layouts.app')

@section('judul', 'Wilayah')

@section('konten')
<div class="grid gap-6 lg:grid-cols-3">

    {{-- Form tambah --}}
    <div class="lg:col-span-1">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-sm font-semibold text-slate-900">Tambah Wilayah</h2>
            <p class="mt-1 mb-5 text-xs text-slate-500">
                Nomor RT dan RW otomatis ditulis tiga digit, sehingga “2” dan “02”
                tidak menjadi dua wilayah berbeda.
            </p>

            <form method="POST" action="{{ route('admin.wilayah.store') }}" class="space-y-4">
                @csrf
                <x-admin.field name="dusun" label="Nama Dusun" required placeholder="mis. Dusun Krajan" />

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.field name="rw" label="RW" required placeholder="001" />
                    <x-admin.field name="rt" label="RT" required placeholder="001" />
                </div>

                <x-admin.field name="urutan" label="Urutan Tampil" type="number"
                               keterangan="Semakin kecil, semakin atas." />

                <button type="submit"
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                               transition hover:bg-emerald-700">
                    Tambah Wilayah
                </button>
            </form>
        </div>
    </div>

    {{-- Daftar --}}
    <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            @if ($daftar->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="font-medium text-slate-900">Belum ada wilayah</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Tambahkan Dusun, RW, dan RT lebih dulu sebelum mencatat data warga.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Dusun</th>
                                <th class="px-4 py-3 font-semibold">RW</th>
                                <th class="px-4 py-3 font-semibold">RT</th>
                                <th class="px-4 py-3 font-semibold">Warga</th>
                                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($daftar as $wilayah)
                                <tr x-data="{ ubah: false }" class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-medium text-slate-900" x-show="!ubah">{{ $wilayah->dusun }}</td>
                                    <td class="px-4 py-3 text-slate-600" x-show="!ubah">{{ $wilayah->rw }}</td>
                                    <td class="px-4 py-3 text-slate-600" x-show="!ubah">{{ $wilayah->rt }}</td>
                                    <td class="px-4 py-3 text-slate-600" x-show="!ubah">
                                        {{ $wilayah->penduduk_count }}
                                    </td>
                                    <td class="px-4 py-3 text-right" x-show="!ubah">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" @click="ubah = true"
                                                    class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">
                                                Ubah
                                            </button>

                                            @if ($wilayah->penduduk_count > 0)
                                                <span class="cursor-not-allowed rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-300"
                                                      title="Masih dipakai {{ $wilayah->penduduk_count }} data warga">
                                                    Hapus
                                                </span>
                                            @else
                                                <form method="POST" action="{{ route('admin.wilayah.destroy', $wilayah) }}"
                                                      data-konfirmasi data-bahaya
                                                      data-judul="Hapus wilayah?"
                                                      data-pesan="{{ $wilayah->label() }} akan dihapus dari daftar wilayah."
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

                                    {{-- Baris sunting inline --}}
                                    <td colspan="5" class="px-4 py-3" x-show="ubah" x-cloak>
                                        <form method="POST" action="{{ route('admin.wilayah.update', $wilayah) }}"
                                              class="flex flex-wrap items-end gap-3">
                                            @csrf @method('PUT')
                                            <div class="min-w-40 flex-1">
                                                <label class="mb-1 block text-xs text-slate-500">Dusun</label>
                                                <input name="dusun" value="{{ $wilayah->dusun }}" required
                                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                            </div>
                                            <div class="w-20">
                                                <label class="mb-1 block text-xs text-slate-500">RW</label>
                                                <input name="rw" value="{{ $wilayah->rw }}" required
                                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                            </div>
                                            <div class="w-20">
                                                <label class="mb-1 block text-xs text-slate-500">RT</label>
                                                <input name="rt" value="{{ $wilayah->rt }}" required
                                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                            </div>
                                            <div class="w-24">
                                                <label class="mb-1 block text-xs text-slate-500">Urutan</label>
                                                <input name="urutan" type="number" value="{{ $wilayah->urutan }}"
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
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
