@props(['warga'])

{{--
    Dialog pengarsipan warga.

    Alasan keluar wajib dipilih, bukan opsional: tanpa itu statistik kematian
    ikut menghitung warga yang sebenarnya pindah atau datanya ganda (PRD 2.7).
--}}

<div x-data="{ buka: false }" @keydown.escape.window="buka = false">
    <button type="button" @click="buka = true"
            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-error transition hover:bg-error/10">
        Arsipkan
    </button>

    <template x-teleport="body">
        <div x-show="buka" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-on-surface/50" @click="buka = false"></div>

            <div class="relative w-full max-w-md rounded-[12px] bg-surface-container-lowest p-6 shadow-xl"
                 role="dialog" aria-modal="true">

                <h2 class="text-lg font-semibold text-on-surface">Arsipkan {{ $warga->nama_lengkap }}?</h2>
                <p class="mt-1.5 text-sm text-on-surface-variant">
                    Datanya tidak dihapus — hanya keluar dari daftar aktif dan bisa dipulihkan
                    kapan saja dari halaman Arsip Warga.
                </p>

                <form method="POST" action="{{ route('admin.penduduk.arsipkan', $warga) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('DELETE')

                    <fieldset>
                        <legend class="mb-2 text-sm font-medium text-on-surface">Alasan keluar</legend>
                        <div class="space-y-2">
                            @foreach (\App\Enums\AlasanKeluar::cases() as $alasan)
                                <label class="flex cursor-pointer gap-3 rounded-[12px] border border-outline-variant p-3
                                              transition hover:border-outline hover:bg-surface-container
                                              has-checked:border-primary has-checked:bg-primary-fixed/20">
                                    <input type="radio" name="alasan_keluar" value="{{ $alasan->value }}" required
                                           class="mt-0.5 border-outline-variant text-primary focus:ring-primary/20">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium text-on-surface">{{ $alasan->label() }}</span>
                                        <span class="block text-xs text-on-surface-variant">{{ $alasan->keterangan() }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div>
                        <label for="tanggal_keluar_{{ $warga->id }}" class="mb-1.5 block text-sm font-medium text-on-surface">
                            Tanggal kejadian
                        </label>
                        <input id="tanggal_keluar_{{ $warga->id }}" type="date" name="tanggal_keluar" required
                               value="{{ now()->toDateString() }}"
                               max="{{ now()->toDateString() }}"
                               min="{{ $warga->tanggal_masuk?->toDateString() }}"
                               class="w-full rounded-[12px] border border-outline-variant px-3 py-2.5 text-sm shadow-sm
                                      focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="buka = false"
                                class="rounded-[12px] border border-outline px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container">
                            Batal
                        </button>
                        <button type="submit"
                                class="rounded-[12px] bg-error px-4 py-2.5 text-sm font-semibold text-on-error hover:bg-error/90">
                            Arsipkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
