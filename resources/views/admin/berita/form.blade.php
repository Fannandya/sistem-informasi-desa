@extends('admin.layouts.app')

@php $ubah = $berita->exists; @endphp

@section('judul', $ubah ? 'Ubah Berita' : 'Tulis Berita')

@section('konten')
<div class="max-w-3xl">
    <nav class="mb-5 text-sm">
        <a href="{{ route('admin.berita.index') }}" class="text-slate-500 hover:text-slate-800">
            &larr; Kembali ke Berita Desa
        </a>
    </nav>

    <form method="POST"
          action="{{ $ubah ? route('admin.berita.update', $berita) : route('admin.berita.store') }}"
          enctype="multipart/form-data"
          x-data="formBerita(@js($ubah ? 'berita-' . $berita->id : 'berita-baru'))"
          @submit="bersihkanDraf()"
          class="space-y-5 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf
        @if ($ubah) @method('PUT') @endif

        <x-admin.field name="judul" label="Judul Berita" :value="$berita->judul" required
                       x-model="judul" placeholder="mis. Musyawarah Desa Bahas APBDes 2027" />

        <x-admin.select name="kategori_id" label="Kategori" :value="$berita->kategori_id"
                        :pilihan="$kategoriList->pluck('nama', 'id')" kosong="— Tanpa kategori —" />

        <div>
            <label for="ringkasan" class="mb-1.5 block text-sm font-medium text-slate-700">Ringkasan</label>
            <textarea id="ringkasan" name="ringkasan" rows="2" maxlength="300" x-model="ringkasan"
                      class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm
                             focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"></textarea>
            <div class="mt-1.5 flex items-start justify-between gap-4">
                <p class="text-xs text-slate-500">
                    Tampil pada kartu daftar dan hasil pencarian Google. Bila dikosongkan,
                    diambil otomatis dari awal isi berita.
                </p>
                <p class="shrink-0 text-xs tabular-nums"
                   :class="ringkasan.length > 160 ? 'text-amber-600' : 'text-slate-500'"
                   x-text="`${ringkasan.length} / 300`"></p>
            </div>
            @error('ringkasan')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Editor teks kaya --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">
                Isi Berita <span class="text-red-500" aria-hidden="true">*</span>
            </label>
            <input id="isi" type="hidden" name="isi" value="{{ old('isi', $berita->isi) }}" x-ref="isi">
            <trix-editor input="isi" @trix-change="simpanDraf()"></trix-editor>
            <p class="mt-1.5 text-xs text-slate-500">
                Tulisan tersimpan sementara di peramban ini setiap beberapa detik, sehingga tidak hilang
                bila halaman tertutup tidak sengaja.
            </p>
            @error('isi')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div x-show="adaDrafTersimpan" x-cloak
             class="flex flex-wrap items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <span>Ada tulisan yang belum sempat disimpan dari sesi sebelumnya.</span>
            <button type="button" @click="pulihkanDraf()"
                    class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-700">
                Pulihkan
            </button>
            <button type="button" @click="bersihkanDraf()"
                    class="text-xs underline">Abaikan</button>
        </div>

        <x-admin.gambar name="gambar_sampul" label="Gambar Sampul" :path="$berita->gambar_sampul" jenis="berita"
                        keterangan="Dipakai pada kartu daftar dan pratinjau saat tautan dibagikan." />

        <x-admin.select name="status" label="Status" required :value="$berita->status ?? 'draf'"
                        :pilihan="['draf' => 'Draf — hanya terlihat admin', 'terbit' => 'Terbit — tampil di halaman publik']"
                        :kosong="false" />

        @if ($ubah)
            <details class="rounded-lg border border-slate-200 p-4">
                <summary class="cursor-pointer text-sm font-medium text-slate-700">Alamat URL halaman</summary>
                <div class="mt-4">
                    <x-admin.field name="slug" label="Slug" :value="$berita->slug" />
                    <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        Alamat ini <strong>tidak ikut berubah</strong> saat judul diubah, supaya tautan yang
                        sudah dibagikan tetap hidup. Mengubahnya di sini akan <strong>mematikan tautan lama</strong>.
                    </div>
                </div>
            </details>
        @endif

        <x-admin.simpan :label="$ubah ? 'Simpan Perubahan' : 'Simpan Berita'">
            <a href="{{ route('admin.berita.index') }}" class="text-sm text-slate-500 hover:text-slate-800">Batal</a>
        </x-admin.simpan>
    </form>
</div>

@push('skrip')
@vite('resources/js/editor.js')
<script>
    function formBerita(kunci) {
        const KUNCI = `draf-${kunci}`;

        return {
            judul: @js(old('judul', $berita->judul ?? '')),
            ringkasan: @js(old('ringkasan', $berita->ringkasan ?? '')),
            adaDrafTersimpan: false,

            init() {
                const tersimpan = localStorage.getItem(KUNCI);
                const isiSekarang = this.$refs.isi.value ?? '';

                // Tawarkan pemulihan hanya bila draf lokal benar-benar berbeda
                // dari yang sudah tersimpan di server.
                if (tersimpan) {
                    try {
                        const draf = JSON.parse(tersimpan);
                        if (draf.isi && draf.isi !== isiSekarang) {
                            this.adaDrafTersimpan = true;
                        }
                    } catch (e) {
                        localStorage.removeItem(KUNCI);
                    }
                }
            },

            simpanDraf() {
                localStorage.setItem(KUNCI, JSON.stringify({
                    judul: this.judul,
                    ringkasan: this.ringkasan,
                    isi: this.$refs.isi.value,
                    waktu: Date.now(),
                }));
            },

            pulihkanDraf() {
                const draf = JSON.parse(localStorage.getItem(KUNCI));
                this.judul = draf.judul ?? this.judul;
                this.ringkasan = draf.ringkasan ?? this.ringkasan;
                document.querySelector('trix-editor').editor.loadHTML(draf.isi ?? '');
                this.adaDrafTersimpan = false;
            },

            bersihkanDraf() {
                localStorage.removeItem(KUNCI);
                this.adaDrafTersimpan = false;
            },
        };
    }
</script>
@endpush
@endsection
