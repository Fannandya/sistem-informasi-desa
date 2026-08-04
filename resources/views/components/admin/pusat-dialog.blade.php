{{--
    Satu-satunya dialog di panel admin.

    Seluruh konfirmasi, peringatan, dan pesan hasil aksi memakai elemen ini —
    isinya diisi resources/js/dialog.js saat dibutuhkan. Halaman daftar bisa
    memuat 50 baris; menempelkan satu modal per baris membuat dokumennya
    membengkak tanpa alasan.
--}}

{{-- `hidden` mengalahkan `flex` karena Tailwind menempatkan utilitas hidden
     paling akhir di antara utilitas display; JS cukup melepas kelas itu. --}}
<div id="pusatDialog" class="fixed inset-0 z-[60] hidden flex items-center justify-center bg-on-surface/50 p-4"
     role="dialog" aria-modal="true" aria-labelledby="dialogJudul">

    <div id="dialogPanel" class="w-full max-w-md rounded-[12px] bg-surface-container-lowest p-6 shadow-xl">

        <div class="flex gap-4">
            <div id="dialogIkon" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-surface-dim text-on-surface">
                <span class="material-symbols-outlined text-xl">help</span>
            </div>

            <div class="min-w-0 flex-1">
                <h2 id="dialogJudul" class="text-base font-semibold text-on-surface">Konfirmasi</h2>

                <p id="dialogPesan" class="mt-1.5 hidden text-sm leading-relaxed text-on-surface-variant"></p>

                <ul id="dialogRincian" class="mt-2 hidden list-inside list-disc space-y-1 text-sm text-on-surface"></ul>

                <p id="dialogCatatan"
                   class="mt-3 hidden rounded-[12px] border border-tertiary-fixed bg-tertiary-fixed/20 px-3 py-2 text-xs text-on-tertiary-container"></p>

                <div id="dialogBlokKetik" class="mt-4 hidden">
                    <label for="dialogInputKetik" class="mb-1.5 block text-xs text-on-surface-variant">
                        Ketik <span id="dialogLabelKetik" class="font-semibold text-on-surface"></span> untuk melanjutkan
                    </label>
                    <input id="dialogInputKetik" type="text" autocomplete="off"
                           class="w-full rounded-[12px] border border-outline-variant px-3 py-2 text-sm shadow-sm
                                  focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap justify-end gap-2">
            <button type="button" id="dialogBatal"
                    class="rounded-[12px] border border-outline px-4 py-2.5 text-sm font-medium text-on-surface
                           transition hover:bg-surface-container">
                Batal
            </button>
            <button type="button" id="dialogSetuju"
                    class="rounded-[12px] bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary
                           transition hover:bg-primary-container focus:ring-2 focus:ring-primary/40 focus:outline-none">
                Lanjutkan
            </button>
        </div>
    </div>
</div>
