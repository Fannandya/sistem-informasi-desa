@extends('admin.layouts.app')

@php $ubah = $pengurus->exists; @endphp

@section('judul', $ubah ? 'Ubah Pengurus' : 'Tambah Pengurus')

@section('konten')
<div class="max-w-2xl">

    <nav class="mb-5 text-sm">
        <a href="{{ route('admin.pengurus.index') }}" class="text-slate-500 hover:text-slate-800">
            &larr; Kembali ke Struktur Organisasi
        </a>
    </nav>

    @if ($organisasiList->isEmpty())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            Belum ada kelompok organisasi. Tambahkan lebih dulu di halaman
            <a href="{{ route('admin.pengurus.index') }}" class="underline">Struktur Organisasi</a>.
        </div>
    @else
        <form method="POST"
              action="{{ $ubah ? route('admin.pengurus.update', $pengurus) : route('admin.pengurus.store') }}"
              enctype="multipart/form-data"
              class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            @csrf
            @if ($ubah) @method('PUT') @endif

            <x-admin.field name="nama" label="Nama Lengkap" :value="$pengurus->nama" required />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.field name="jabatan" label="Jabatan" :value="$pengurus->jabatan" required
                               placeholder="mis. Kepala Desa, Ketua RT" />

                <x-admin.select name="organisasi_id" label="Organisasi" required
                                :value="$pengurus->organisasi_id"
                                :pilihan="$organisasiList->pluck('nama', 'id')"
                                kosong="— Pilih organisasi —" />
            </div>

            <x-admin.select name="wilayah_id" label="Wilayah yang Diwakili"
                            :value="$pengurus->wilayah_id"
                            :pilihan="$wilayahList->mapWithKeys(fn ($w) => [$w->id => $w->label()])"
                            kosong="— Tidak mewakili wilayah tertentu —"
                            keterangan="Diisi hanya untuk pengurus RT atau RW." />

            <div>
                <x-admin.field name="nomor_hp" label="Nomor HP Kontak Resmi" :value="$pengurus->nomor_hp"
                               placeholder="0812xxxxxxxx" />
                <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    Nomor ini <strong>ditampilkan di halaman publik</strong> dan dapat dilihat siapa saja.
                    Gunakan nomor dinas, bukan nomor pribadi yang tidak ingin tersebar.
                </div>
            </div>

            <x-admin.gambar name="foto" label="Foto Pengurus" :path="$pengurus->foto" jenis="pengurus"
                            keterangan="Sebaiknya foto potret setengah badan." />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.field name="urutan" label="Urutan Tampil" type="number"
                               :value="$pengurus->urutan ?? 0"
                               keterangan="Semakin kecil, semakin atas dalam kelompoknya." />

                <div class="flex items-end">
                    <label class="flex items-center gap-2 pb-2.5 text-sm text-slate-700">
                        <input type="checkbox" name="tampil" value="1"
                               @checked(old('tampil', $pengurus->tampil ?? true))
                               class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        Tampilkan di halaman publik
                    </label>
                </div>
            </div>

            <x-admin.simpan :label="$ubah ? 'Simpan Perubahan' : 'Simpan Pengurus'">
                <a href="{{ route('admin.pengurus.index') }}" class="text-sm text-slate-500 hover:text-slate-800">Batal</a>
            </x-admin.simpan>
        </form>
    @endif
</div>
@endsection
