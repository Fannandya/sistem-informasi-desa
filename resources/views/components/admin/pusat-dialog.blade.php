{{--
    Satu-satunya dialog di panel admin.

    Seluruh konfirmasi, peringatan, dan pesan hasil aksi memakai elemen ini —
    isinya diisi resources/js/dialog.js saat dibutuhkan. Halaman daftar bisa
    memuat 50 baris; menempelkan satu modal per baris membuat dokumennya
    membengkak tanpa alasan.
--}}

{{-- `hidden` mengalahkan `flex` karena Tailwind menempatkan utilitas hidden
     paling akhir di antara utilitas display; JS cukup melepas kelas itu. --}}
<div id="pusatDialog" class="fixed inset-0 z-[60] hidden flex items-center justify-center bg-slate-900/50 p-4"
     role="dialog" aria-modal="true" aria-labelledby="dialogJudul">

    <div id="dialogPanel" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">

        <div class="flex gap-4">
            <div id="dialogIkon" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="" />
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <h2 id="dialogJudul" class="text-base font-semibold text-slate-900">Konfirmasi</h2>

                <p id="dialogPesan" class="mt-1.5 hidden text-sm leading-relaxed text-slate-600"></p>

                <ul id="dialogRincian" class="mt-2 hidden list-inside list-disc space-y-1 text-sm text-slate-700"></ul>

                <p id="dialogCatatan"
                   class="mt-3 hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800"></p>

                <div id="dialogBlokKetik" class="mt-4 hidden">
                    <label for="dialogInputKetik" class="mb-1.5 block text-xs text-slate-600">
                        Ketik <span id="dialogLabelKetik" class="font-semibold text-slate-900"></span> untuk melanjutkan
                    </label>
                    <input id="dialogInputKetik" type="text" autocomplete="off"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm
                                  focus:border-red-500 focus:ring-2 focus:ring-red-500/20 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap justify-end gap-2">
            <button type="button" id="dialogBatal"
                    class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700
                           transition hover:bg-slate-50">
                Batal
            </button>
            <button type="button" id="dialogSetuju"
                    class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                           transition hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500/40 focus:outline-none">
                Lanjutkan
            </button>
        </div>
    </div>
</div>
