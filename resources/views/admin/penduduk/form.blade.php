@extends('admin.layouts.app')

@php $ubah = $penduduk->exists; @endphp

@section('judul', $ubah ? 'Ubah Data Warga' : 'Tambah Warga')

@section('konten')
<div class="max-w-2xl">

    <nav class="mb-5 text-sm">
        <a href="{{ route('admin.penduduk.index') }}" class="text-slate-500 hover:text-slate-800">
            &larr; Kembali ke Data Warga
        </a>
    </nav>

    <form method="POST"
          action="{{ $ubah ? route('admin.penduduk.update', $penduduk) : route('admin.penduduk.store') }}"
          data-lacak-perubahan
          x-data="formWarga(@js(old('tanggal_lahir', $penduduk->tanggal_lahir?->toDateString())), @js(old('jenis_masuk', $penduduk->jenis_masuk?->value)))"
          class="space-y-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        @csrf
        @if ($ubah) @method('PUT') @endif

        {{-- ---------------- Data pribadi ---------------- --}}
        <div class="space-y-5">
            <x-admin.field name="nama_lengkap" label="Nama Lengkap" :value="$penduduk->nama_lengkap" required />

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="tanggal_lahir" class="mb-1.5 block text-sm font-medium text-slate-700">
                        Tanggal Lahir <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input id="tanggal_lahir" name="tanggal_lahir" type="date" required
                           x-model="tanggalLahir" @change="sinkronkanTanggalMasuk()"
                           max="{{ now()->toDateString() }}"
                           value="{{ old('tanggal_lahir', $penduduk->tanggal_lahir?->toDateString()) }}"
                           class="w-full rounded-lg border px-3 py-2.5 text-sm shadow-sm focus:ring-2 focus:outline-none
                                  {{ $errors->has('tanggal_lahir')
                                      ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20'
                                      : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-500/20' }}">

                    {{-- Umur ditampilkan sebagai konfirmasi: salah ketik tahun
                         lahir langsung terlihat sebagai umur yang janggal. --}}
                    <p class="mt-1.5 text-xs" x-show="tanggalLahir" x-cloak
                       :class="umurJanggal ? 'text-amber-600' : 'text-slate-500'">
                        Umur saat ini: <span class="font-medium" x-text="umurTerbaca"></span>
                    </p>

                    @error('tanggal_lahir')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-admin.select name="jenis_kelamin" label="Jenis Kelamin" required
                                :value="$penduduk->jenis_kelamin?->value"
                                :pilihan="\App\Enums\JenisKelamin::pilihan()" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.select name="wilayah_id" label="Wilayah" required
                                :value="$penduduk->wilayah_id"
                                :pilihan="$wilayahList->mapWithKeys(fn ($w) => [$w->id => $w->label()])"
                                kosong="— Pilih wilayah —" />

                <x-admin.select name="pekerjaan_id" label="Pekerjaan"
                                :value="$penduduk->pekerjaan_id"
                                :pilihan="$pekerjaanList->pluck('nama', 'id')"
                                kosong="— Belum diisi —" />
            </div>

            <x-admin.select name="pendidikan" label="Pendidikan Terakhir"
                            :value="$penduduk->pendidikan?->value"
                            :pilihan="\App\Enums\Pendidikan::pilihan()"
                            kosong="— Belum diisi —" />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.select name="agama_id" label="Agama"
                                :value="$penduduk->agama_id"
                                :pilihan="$agamaList->pluck('nama', 'id')"
                                kosong="— Belum diisi —" />

                <x-admin.select name="status_perkawinan" label="Status Perkawinan"
                                :value="$penduduk->status_perkawinan?->value"
                                :pilihan="\App\Enums\StatusPerkawinan::pilihan()"
                                kosong="— Belum diisi —" />
            </div>
        </div>

        {{-- ---------------- Data mutasi ---------------- --}}
        <div class="space-y-5 border-t border-slate-200 pt-6">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Cara Warga Tercatat</h3>
                <p class="mt-1 text-xs text-slate-500">
                    Menentukan apakah warga ini dihitung sebagai kelahiran pada statistik desa.
                </p>
            </div>

            <fieldset>
                <div class="space-y-2">
                    @foreach (\App\Enums\JenisMasuk::cases() as $jenis)
                        <label class="flex cursor-pointer gap-3 rounded-lg border border-slate-200 p-3 transition
                                      hover:border-slate-300 hover:bg-slate-50
                                      has-checked:border-emerald-500 has-checked:bg-emerald-50">
                            <input type="radio" name="jenis_masuk" value="{{ $jenis->value }}" required
                                   x-model="jenisMasuk" @change="sinkronkanTanggalMasuk()"
                                   @checked(old('jenis_masuk', $penduduk->jenis_masuk?->value ?? \App\Enums\JenisMasuk::PendataanAwal->value) === $jenis->value)
                                   class="mt-0.5 border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-slate-900">{{ $jenis->label() }}</span>
                                <span class="block text-xs text-slate-500">{{ $jenis->keterangan() }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('jenis_masuk')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </fieldset>

            <div>
                <label for="tanggal_masuk" class="mb-1.5 block text-sm font-medium text-slate-700">
                    Tanggal Masuk <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <input id="tanggal_masuk" name="tanggal_masuk" type="date" required
                       x-model="tanggalMasuk"
                       max="{{ now()->toDateString() }}"
                       value="{{ old('tanggal_masuk', $penduduk->tanggal_masuk?->toDateString() ?? now()->toDateString()) }}"
                       class="w-full rounded-lg border px-3 py-2.5 text-sm shadow-sm focus:ring-2 focus:outline-none
                              {{ $errors->has('tanggal_masuk')
                                  ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20'
                                  : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-500/20' }}">
                <p class="mt-1.5 text-xs text-slate-500" x-show="jenisMasuk === 'lahir'" x-cloak>
                    Terisi otomatis mengikuti tanggal lahir. Masih bisa diubah bila perlu.
                </p>
                @error('tanggal_masuk')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <x-admin.simpan :label="$ubah ? 'Simpan Perubahan' : 'Simpan Warga'">
            <a href="{{ route('admin.penduduk.index') }}" class="text-sm text-slate-500 hover:text-slate-800">
                Batal
            </a>
        </x-admin.simpan>
    </form>
</div>

@push('skrip')
<script>
    function formWarga(tanggalLahirAwal, jenisMasukAwal) {
        return {
            tanggalLahir: tanggalLahirAwal ?? '',
            tanggalMasuk: document.getElementById('tanggal_masuk')?.value ?? '',
            jenisMasuk: jenisMasukAwal ?? 'pendataan_awal',

            get umur() {
                if (!this.tanggalLahir) return null;
                const lahir = new Date(this.tanggalLahir);
                if (isNaN(lahir)) return null;

                const kini = new Date();
                let tahun = kini.getFullYear() - lahir.getFullYear();
                const bulan = kini.getMonth() - lahir.getMonth();
                if (bulan < 0 || (bulan === 0 && kini.getDate() < lahir.getDate())) tahun--;
                return tahun;
            },

            get umurTerbaca() {
                const u = this.umur;
                if (u === null) return '—';
                if (u < 0) return 'tanggal lahir belum terjadi';
                return `${u} tahun`;
            },

            // Umur di luar rentang wajar biasanya berarti tahun salah ketik.
            get umurJanggal() {
                const u = this.umur;
                return u !== null && (u < 0 || u > 110);
            },

            sinkronkanTanggalMasuk() {
                if (this.jenisMasuk === 'lahir' && this.tanggalLahir) {
                    this.tanggalMasuk = this.tanggalLahir;
                }
            },
        };
    }
</script>
@endpush
@endsection
