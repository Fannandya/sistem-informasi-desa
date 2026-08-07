@extends('admin.layouts.app')

@section('judul', 'Agama')

@section('konten')
<div class="grid gap-6 lg:grid-cols-3">

    <div class="lg:col-span-1">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-sm font-semibold text-slate-900">Tambah Agama</h2>
            <p class="mt-1 mb-5 text-xs text-slate-500">
                Daftar ini yang muncul sebagai pilihan pada formulir warga, dan menjadi
                kategori pada tabel agama di halaman statistik publik.
            </p>

            <form method="POST" action="{{ route('admin.agama.store') }}" id="formAgamaTambah" class="space-y-4">
                @csrf
                <input type="hidden" name="paksa" value="0">
                <x-admin.field name="nama" label="Nama Agama" required placeholder="mis. Islam" />

                <button type="submit"
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                               transition hover:bg-emerald-700">
                    Tambah Agama
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            @if ($daftar->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="font-medium text-slate-900">Belum ada daftar agama</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Tambahkan agama yang umum di desa ini.
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
                        @foreach ($daftar as $agama)
                            @php $bukaOtomatis = session('agama_duplikat.form') === "formAgamaUbah{$agama->id}"; @endphp
                            <tr x-data="{ ubah: {{ $bukaOtomatis ? 'true' : 'false' }} }" class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900" x-show="!ubah">
                                    {{ $agama->nama }}
                                </td>
                                <td class="px-4 py-3 text-slate-600" x-show="!ubah">
                                    {{ $agama->penduduk_count }} warga
                                </td>
                                <td class="px-4 py-3 text-right" x-show="!ubah">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="ubah = true"
                                                class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">
                                            Ubah
                                        </button>

                                        @if ($agama->is_template)
                                            <span class="cursor-not-allowed rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-300"
                                                  title="Agama bawaan tidak dapat dihapus">
                                                Hapus
                                            </span>
                                        @elseif ($agama->penduduk_count > 0)
                                            <span class="cursor-not-allowed rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-300"
                                                  title="Masih dipakai {{ $agama->penduduk_count }} data warga">
                                                Hapus
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('admin.agama.destroy', $agama) }}"
                                                  data-konfirmasi data-bahaya
                                                  data-judul="Hapus agama?"
                                                  data-pesan="Agama {{ $agama->nama }} akan dihapus dari daftar referensi."
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
                                    <form method="POST" action="{{ route('admin.agama.update', $agama) }}"
                                          id="formAgamaUbah{{ $agama->id }}"
                                          class="flex flex-wrap items-end gap-3">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="paksa" value="0">
                                        <div class="min-w-48 flex-1">
                                            <label class="mb-1 block text-xs text-slate-500">Nama Agama</label>
                                            <input name="nama" value="{{ $agama->nama }}" required
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

@if (session('agama_duplikat'))
    <script type="application/json" id="agamaDuplikat">@json(session('agama_duplikat'))</script>
@endif

@push('skrip')
<script>
    const dataAgamaDuplikat = document.getElementById('agamaDuplikat');

    if (dataAgamaDuplikat) {
        const { ada, baru, form: idForm } = JSON.parse(dataAgamaDuplikat.textContent);

        window.Dialog.buka({
            jenis: 'tanya',
            judul: 'Nama agama serupa sudah terdaftar',
            pesan: `Sudah ada "${ada}" di daftar. Tetap tambahkan "${baru}" sebagai entri terpisah?`,
            catatan: 'Ini bisa memecah data pada tabel/grafik agama menjadi dua kategori berbeda.',
            tombol: 'Tetap Tambahkan',
            batal: 'Batal, saya ubah dulu',
        }).then((lanjut) => {
            if (!lanjut) return;

            const form = document.getElementById(idForm);
            if (!form) return;

            form.querySelector('[name="paksa"]').value = '1';
            form.requestSubmit();
        });
    }
</script>
@endpush
@endsection
