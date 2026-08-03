@props([
    'name',
    'label',
    'path' => null,
    'jenis' => 'bawaan',
    'keterangan' => null,
])

@php
    $unggahan = app(\App\Services\LayananUnggahGambar::class);
    $urlSaatIni = $unggahan->urlThumbnail($path, $jenis);
    $maksMb = round(config('unggahan.maks_kb') / 1024, 1);
    $format = implode(', ', config('unggahan.ekstensi_diizinkan'));
@endphp

<div x-data="{
        pratinjau: @js($urlSaatIni),
        adaGambar: @js(filled($path)),
        hapus: false,
        pilih(e) {
            const berkas = e.target.files[0];
            if (!berkas) return;
            this.pratinjau = URL.createObjectURL(berkas);
            this.adaGambar = true;
            this.hapus = false;
        },
    }">
    <p class="mb-1.5 text-sm font-medium text-slate-700">{{ $label }}</p>

    <div class="flex items-start gap-4">
        <div class="shrink-0 overflow-hidden rounded-lg bg-slate-100 ring-1 ring-slate-200">
            <img :src="pratinjau" alt="Pratinjau {{ $label }}"
                 class="h-24 w-24 object-cover transition"
                 :class="hapus && 'opacity-30 grayscale'">
        </div>

        <div class="min-w-0 flex-1">
            <input id="{{ $name }}" name="{{ $name }}" type="file"
                   accept="{{ collect(config('unggahan.mime_diizinkan'))->implode(',') }}"
                   @change="pilih"
                   class="block w-full text-sm text-slate-600
                          file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2
                          file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">

            <p class="mt-1.5 text-xs text-slate-500">
                {{ $keterangan ? $keterangan . ' ' : '' }}Format {{ $format }}, maksimal {{ $maksMb }} MB.
            </p>

            <label x-show="adaGambar" x-cloak class="mt-2 flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="hapus_{{ $name }}" value="1" x-model="hapus"
                       class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                Hapus gambar ini
            </label>

            @error($name)
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
