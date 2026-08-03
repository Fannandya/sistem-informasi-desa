@props(['ringkasKesalahan' => true])

{{--
    Pesan hasil aksi untuk seluruh halaman admin.

    Ringkasan kesalahan validasi ikut ditampilkan di sini karena tidak semua
    form punya tempat menampilkan pesannya sendiri. Dialog pengarsipan warga,
    misalnya, tertutup setelah halaman dimuat ulang — tanpa ringkasan ini
    admin hanya melihat halaman yang seolah tidak bereaksi.
--}}

@if (session('status'))
    <div class="mb-5 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

@if (session('galat'))
    <div class="mb-5 flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <span>{{ session('galat') }}</span>
    </div>
@endif

@if ($ringkasKesalahan && $errors->any())
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <p class="font-medium">Perubahan belum tersimpan.</p>
        <ul class="mt-1.5 list-inside list-disc space-y-0.5">
            @foreach ($errors->unique() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@endif
