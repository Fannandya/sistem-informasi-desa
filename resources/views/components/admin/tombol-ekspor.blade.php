@props([
    'pencarian',
    'arsip' => false,
    'adaSaringan' => false,
])

{{--
    Tombol ekspor dengan pilihan format.

    Saringan yang sedang aktif ikut terbawa sebagai hidden input, sehingga
    berkas yang diunduh berisi persis data yang tampil di layar — bukan
    seluruh data desa.
--}}

<div x-data="{ buka: false }" @click.outside="buka = false" class="relative">
    <button type="button" @click="buka = !buka"
            class="flex items-center gap-1.5 rounded-lg border border-slate-300 px-4 py-2.5 text-sm
                   font-medium text-slate-700 transition hover:bg-slate-50">
        Ekspor
        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="buka" x-cloak
         class="absolute right-0 z-20 mt-2 w-64 rounded-xl bg-white p-2 shadow-lg ring-1 ring-slate-200">

        @if ($adaSaringan)
            <p class="px-3 py-2 text-xs text-slate-500">
                Mengunduh <strong class="text-slate-700">hasil saringan yang sedang tampil</strong>.
            </p>
        @else
            <p class="px-3 py-2 text-xs text-slate-500">
                Mengunduh <strong class="text-slate-700">seluruh data</strong>{{ $arsip ? ' arsip' : ' warga aktif' }}.
            </p>
        @endif

        @foreach (['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (.csv)'] as $format => $label)
            <form method="GET" action="{{ route('admin.penduduk.ekspor') }}"
                  data-konfirmasi
                  data-judul="Unduh berkas {{ strtoupper($format) }}?"
                  data-pesan="{{ $adaSaringan
                      ? 'Berkas berisi hasil saringan yang sedang tampil, bukan seluruh data.'
                      : 'Berkas berisi seluruh data ' . ($arsip ? 'arsip' : 'warga aktif') . '.' }}"
                  data-catatan="Berkas memuat nama warga. Simpan di tempat yang aman dan jangan dibagikan sembarangan."
                  data-tombol="Unduh">
                @foreach ($pencarian->parameter() as $kunci => $nilai)
                    <input type="hidden" name="{{ $kunci }}" value="{{ $nilai }}">
                @endforeach
                @if ($arsip)
                    <input type="hidden" name="arsip" value="1">
                @endif
                <input type="hidden" name="format" value="{{ $format }}">

                <button type="submit"
                        class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-100">
                    {{ $label }}
                </button>
            </form>
        @endforeach

        <p class="border-t border-slate-100 px-3 pt-2 pb-1 text-xs text-slate-400">
            Berkas tidak memuat NIK, nomor KK, nomor telepon, maupun tanggal lahir.
        </p>
    </div>
</div>
