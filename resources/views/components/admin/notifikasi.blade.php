{{--
    Pesan dari server, dikirim ke resources/js/dialog.js untuk ditampilkan
    sebagai dialog — bukan banner, dan bukan alert() bawaan peramban.

    Blok <noscript> menjaga pesan tetap terbaca bila JavaScript gagal dimuat;
    tanpa itu admin menekan Simpan lalu tidak melihat apa pun.
--}}

@php
    $muatan = [
        'status' => session('status'),
        'galat' => session('galat'),
        'kesalahan' => $errors->any() ? array_values(array_unique($errors->all())) : [],
    ];
@endphp

@if (array_filter($muatan))
    <script type="application/json" id="pesanServer">@json($muatan)</script>

    <noscript>
        @if ($muatan['status'])
            <div class="mb-5 rounded-[12px] border border-primary-fixed bg-primary-fixed/20 px-4 py-3 text-sm text-on-primary-container">
                {{ $muatan['status'] }}
            </div>
        @endif

        @if ($muatan['galat'])
            <div class="mb-5 rounded-[12px] border border-tertiary-fixed bg-tertiary-fixed/20 px-4 py-3 text-sm text-on-tertiary-container">
                {{ $muatan['galat'] }}
            </div>
        @endif

        @if ($muatan['kesalahan'])
            <div class="mb-5 rounded-[12px] border border-error bg-error/10 px-4 py-3 text-sm text-on-error-container">
                <p class="font-medium">Perubahan belum tersimpan.</p>
                <ul class="mt-1.5 list-inside list-disc space-y-0.5">
                    @foreach ($muatan['kesalahan'] as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </noscript>
@endif
