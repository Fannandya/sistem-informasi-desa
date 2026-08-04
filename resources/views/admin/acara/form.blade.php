@extends('admin.layouts.app')

@php $ubah = $acara->exists; @endphp

@section('judul', $ubah ? 'Ubah Dokumentasi Acara' : 'Tulis Dokumentasi Acara')

@section('konten')
<div class="max-w-3xl space-y-6">

    <nav class="text-sm">
        <a href="{{ route('admin.acara.index') }}" class="text-slate-500 hover:text-slate-800">
            &larr; Kembali ke Galeri Acara
        </a>
    </nav>

    {{-- ----------------------- Data acara ----------------------- --}}
    <form method="POST"
          action="{{ $ubah ? route('admin.acara.update', $acara) : route('admin.acara.store') }}"
          data-lacak-perubahan
          class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf
        @if ($ubah) @method('PUT') @endif

        <x-admin.field name="judul" label="Judul Kegiatan" :value="$acara->judul" required
                       placeholder="mis. Kerja Bakti Bersihkan Saluran Air" />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field name="tanggal_acara" label="Tanggal Pelaksanaan" type="date" required
                           :value="$acara->tanggal_acara?->toDateString()" />
            <x-admin.field name="lokasi" label="Lokasi" :value="$acara->lokasi"
                           placeholder="mis. Balai Desa" />
        </div>

        <x-admin.textarea name="narasi" label="Narasi Kegiatan" rows="6" :value="$acara->narasi"
                          keterangan="Cerita singkat tentang jalannya kegiatan." />

        <x-admin.select name="status" label="Status" required :value="$acara->status ?? 'draf'"
                        :pilihan="['draf' => 'Draf — hanya terlihat admin', 'terbit' => 'Terbit — tampil di halaman publik']"
                        :kosong="false" />

        @if ($ubah)
            <details class="rounded-lg border border-slate-200 p-4">
                <summary class="cursor-pointer text-sm font-medium text-slate-700">Alamat URL halaman</summary>
                <div class="mt-4">
                    <x-admin.field name="slug" label="Slug" :value="$acara->slug" />
                    <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        Tidak ikut berubah saat judul diubah. Mengubahnya akan <strong>mematikan tautan lama</strong>.
                    </div>
                </div>
            </details>
        @endif

        <x-admin.simpan :label="$ubah ? 'Simpan Perubahan' : 'Simpan & Lanjut Unggah Foto'">
            <a href="{{ route('admin.acara.index') }}" class="text-sm text-slate-500 hover:text-slate-800">Batal</a>
        </x-admin.simpan>
    </form>

    @if (! $ubah)
        <p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600">
            Foto dapat diunggah setelah acara disimpan.
        </p>
    @else
        {{-- ----------------------- Unggah foto ----------------------- --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-sm font-semibold text-slate-900">Unggah Foto</h2>
            <p class="mt-1 mb-4 text-xs text-slate-500">
                Maksimal {{ config('unggahan.maks_berkas_per_kiriman') }} foto sekali unggah,
                masing-masing maksimal {{ round(config('unggahan.maks_kb') / 1024) }} MB.
            </p>

            <form method="POST" action="{{ route('admin.acara.foto.unggah', $acara) }}"
                  enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="min-w-56 flex-1">
                    <input type="file" name="foto[]" multiple required
                           accept="{{ collect(config('unggahan.mime_diizinkan'))->implode(',') }}"
                           class="block w-full text-sm text-slate-600
                                  file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2
                                  file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                </div>
                <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    Unggah
                </button>
            </form>
            @error('foto')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('foto.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- ----------------------- Kelola foto ----------------------- --}}
        @if ($acara->foto->isNotEmpty())
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-1 text-sm font-semibold text-slate-900">
                    Foto Terunggah ({{ $acara->foto->count() }})
                </h2>
                <p class="mb-5 text-xs text-slate-500">
                    Pilih satu foto sebagai sampul galeri. Bila tidak dipilih, foto pertama yang dipakai.
                </p>

                <form method="POST" action="{{ route('admin.acara.foto.perbarui', $acara) }}" class="space-y-4">
                    @csrf @method('PUT')

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($acara->foto as $foto)
                            <div class="flex gap-3 rounded-xl border border-slate-200 p-3">
                                <img src="{{ $foto->urlKecil() }}" alt=""
                                     class="h-20 w-20 shrink-0 rounded-lg bg-slate-100 object-cover">

                                <div class="min-w-0 flex-1 space-y-2">
                                    <input name="keterangan[{{ $foto->id }}]" value="{{ $foto->keterangan }}"
                                           placeholder="Keterangan foto (opsional)" maxlength="200"
                                           class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm">

                                    <div class="flex items-center justify-between gap-2">
                                        <label class="flex items-center gap-1.5 text-xs text-slate-600">
                                            <input type="radio" name="sampul" value="{{ $foto->id }}"
                                                   @checked($foto->sampul)
                                                   class="border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            Jadikan sampul
                                        </label>

                                        <button type="submit" form="hapus-foto-{{ $foto->id }}"
                                                class="text-xs font-medium text-red-700 hover:underline">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit"
                            class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                        Simpan Keterangan & Sampul
                    </button>
                </form>

                {{-- Form hapus diletakkan di luar form utama agar tidak bersarang --}}
                @foreach ($acara->foto as $foto)
                    <form id="hapus-foto-{{ $foto->id }}" method="POST"
                          action="{{ route('admin.acara.foto.hapus', [$acara, $foto]) }}"
                          data-konfirmasi data-bahaya
                          data-judul="Hapus foto?"
                          data-pesan="Foto ini akan dihapus permanen dari galeri acara."
                          data-tombol="Hapus Foto"
                          class="hidden">
                        @csrf @method('DELETE')
                    </form>
                @endforeach
            </div>
        @endif
    @endif
</div>
@endsection
