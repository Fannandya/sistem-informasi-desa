@extends('admin.layouts.app')

@section('judul', 'Periksa Sebelum Impor')

@section('konten')
<div class="max-w-5xl space-y-6"
     x-data="{ ganda: {{ $analisis->adaGanda() ? 'true' : 'false' }}, dipilih: [] }">

    <nav class="text-sm">
        <a href="{{ route('admin.penduduk.impor') }}" class="text-slate-500 hover:text-slate-800">
            &larr; Kembali ke halaman impor
        </a>
    </nav>

    {{-- ------------------------- Ringkasan ------------------------- --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Belum ada data yang tersimpan</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Berkas <span class="font-medium text-slate-800">{{ $namaBerkas }}</span>
                    berisi {{ number_format($analisis->totalBaris, 0, ',', '.') }} baris.
                    Periksa daftar di bawah, lalu tekan Lanjut Impor bila sudah sesuai.
                </p>
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl bg-emerald-50 p-4">
                <p class="text-2xl font-semibold text-emerald-700">{{ count($analisis->siap) }}</p>
                <p class="text-xs text-emerald-800">akan ditambahkan</p>
            </div>
            <div class="rounded-xl bg-amber-50 p-4">
                <p class="text-2xl font-semibold text-amber-700">{{ count($analisis->ganda) }}</p>
                <p class="text-xs text-amber-800">terdeteksi ganda — perlu keputusan Anda</p>
            </div>
            <div class="rounded-xl bg-red-50 p-4">
                <p class="text-2xl font-semibold text-red-700">{{ count($analisis->gagal) }}</p>
                <p class="text-xs text-red-800">bermasalah, tidak akan diimpor</p>
            </div>
        </div>
    </div>

    {{-- ------------------------ Data ganda ------------------------ --}}
    @if ($analisis->adaGanda())
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-amber-300">
            <div class="border-b border-amber-200 bg-amber-50 px-6 py-4">
                <h2 class="font-semibold text-amber-900">
                    {{ count($analisis->ganda) }} data terdeteksi sama dengan warga yang sudah ada
                </h2>
                <p class="mt-1 text-sm text-amber-800">
                    Sistem tidak dapat memastikan apakah ini benar-benar orang yang sama, karena
                    NIK dan nomor KK sengaja tidak disimpan. <strong>Anda yang memutuskan.</strong>
                    Centang baris yang memang orang berbeda dan tetap ingin ditambahkan;
                    biarkan kosong bila memang duplikat.
                </p>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <button type="button"
                            @click="dipilih = {{ Js::from($analisis->nomorBarisGanda()) }}"
                            class="rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs font-medium text-amber-900 hover:bg-amber-100">
                        Centang semua
                    </button>
                    <button type="button" @click="dipilih = []"
                            class="rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs font-medium text-amber-900 hover:bg-amber-100">
                        Hapus semua centang
                    </button>
                    <span class="text-xs text-amber-800">
                        <span x-text="dipilih.length"></span> dari {{ count($analisis->ganda) }} dipilih untuk tetap diimpor
                    </span>
                </div>
            </div>

            <div class="max-h-[32rem] overflow-y-auto">
                @foreach ($analisis->ganda as $baris)
                    <label class="flex cursor-pointer gap-4 border-b border-slate-100 px-6 py-4 transition
                                  hover:bg-slate-50 has-checked:bg-emerald-50/60">
                        <input type="checkbox" form="formImpor" name="ganda[]" value="{{ $baris->nomorBaris }}"
                               x-model.number="dipilih"
                               class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded bg-slate-200 px-1.5 py-0.5 text-xs text-slate-600 tabular-nums">
                                    baris {{ $baris->nomorBaris }}
                                </span>
                                <span class="text-xs text-amber-700">{{ $baris->alasanGanda() }}</span>
                            </div>

                            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                                {{-- Data dari berkas --}}
                                <div class="rounded-lg border border-slate-200 bg-white p-3">
                                    <p class="mb-1.5 text-xs font-semibold tracking-wider text-slate-500 uppercase">
                                        Dari berkas
                                    </p>
                                    <p class="font-medium text-slate-900">{{ $baris->nama }}</p>
                                    <dl class="mt-1 space-y-0.5 text-xs text-slate-600">
                                        <div class="flex gap-2"><dt class="w-24 text-slate-400">Tanggal lahir</dt><dd>{{ $baris->tanggalLahirTerbaca() }}</dd></div>
                                        <div class="flex gap-2"><dt class="w-24 text-slate-400">Jenis kelamin</dt><dd>{{ $baris->jenisKelamin }}</dd></div>
                                        <div class="flex gap-2"><dt class="w-24 text-slate-400">Wilayah</dt><dd>{{ $baris->wilayah }}</dd></div>
                                        <div class="flex gap-2"><dt class="w-24 text-slate-400">Pekerjaan</dt><dd>{{ $baris->pekerjaan ?? '—' }}</dd></div>
                                        <div class="flex gap-2"><dt class="w-24 text-slate-400">Pendidikan</dt><dd>{{ $baris->pendidikan ?? '—' }}</dd></div>
                                        <div class="flex gap-2"><dt class="w-24 text-slate-400">Agama</dt><dd>{{ $baris->agama ?? '—' }}</dd></div>
                                        <div class="flex gap-2"><dt class="w-24 text-slate-400">Status Perkawinan</dt><dd>{{ $baris->statusPerkawinan ?? '—' }}</dd></div>
                                    </dl>
                                </div>

                                {{-- Data yang sudah ada --}}
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <p class="mb-1.5 text-xs font-semibold tracking-wider text-slate-500 uppercase">
                                        {{ $baris->gandaDenganBaris !== null ? 'Baris sebelumnya di berkas' : 'Sudah ada di sistem' }}
                                    </p>

                                    @if ($baris->miripDengan)
                                        <p class="font-medium text-slate-900">
                                            {{ $baris->miripDengan->nama_lengkap }}
                                            @if ($baris->miripDengan->trashed())
                                                <span class="ml-1 rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-600">terarsip</span>
                                            @endif
                                        </p>
                                        <dl class="mt-1 space-y-0.5 text-xs text-slate-600">
                                            <div class="flex gap-2"><dt class="w-24 text-slate-400">Tanggal lahir</dt><dd>{{ $baris->miripDengan->tanggal_lahir?->toDateString() ?? 'tidak diisi' }}</dd></div>
                                            <div class="flex gap-2"><dt class="w-24 text-slate-400">Umur</dt><dd>{{ $baris->miripDengan->umurTerbaca() }}</dd></div>
                                            <div class="flex gap-2"><dt class="w-24 text-slate-400">Wilayah</dt><dd>{{ $baris->miripDengan->wilayah?->label() ?? '—' }}</dd></div>
                                            <div class="flex gap-2"><dt class="w-24 text-slate-400">Dicatat</dt><dd>{{ $baris->miripDengan->created_at?->translatedFormat('d M Y') }}</dd></div>
                                        </dl>
                                    @else
                                        <p class="text-sm text-slate-600">
                                            Baris {{ $baris->gandaDenganBaris }} pada berkas ini berisi nama,
                                            tanggal lahir, dan wilayah yang sama persis.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ------------------------ Baris gagal ------------------------ --}}
    @if ($analisis->adaGagal())
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="font-semibold text-slate-900">
                    {{ count($analisis->gagal) }} baris tidak dapat diimpor
                </h2>
                <p class="mt-1 text-sm text-slate-600">
                    Baris berikut dilewati. Baris lain tetap dapat diimpor.
                    Untuk memasukkan baris ini, perbaiki berkas asalnya lalu unggah ulang.
                </p>
            </div>
            <div class="max-h-72 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-6 py-2 font-semibold">Baris</th>
                            <th class="px-6 py-2 font-semibold">Nama</th>
                            <th class="px-6 py-2 font-semibold">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($analisis->gagal as $gagal)
                            <tr>
                                <td class="px-6 py-2 tabular-nums text-slate-500">{{ $gagal['baris'] }}</td>
                                <td class="px-6 py-2 text-slate-900">{{ $gagal['nama'] }}</td>
                                <td class="px-6 py-2 text-red-700">{{ $gagal['alasan'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- --------------------- Yang akan ditambahkan --------------------- --}}
    @if ($analisis->siap !== [])
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="font-semibold text-slate-900">
                    {{ count($analisis->siap) }} warga yang akan ditambahkan
                </h2>
                <p class="mt-1 text-sm text-slate-600">
                    Seluruh baris berikut akan masuk sebagai warga baru dengan cara masuk
                    &ldquo;Pendataan Awal&rdquo;.
                </p>
            </div>
            <div class="max-h-[32rem] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-6 py-2 font-semibold">Baris</th>
                            <th class="px-6 py-2 font-semibold">Nama</th>
                            <th class="px-6 py-2 font-semibold">Tanggal Lahir</th>
                            <th class="px-6 py-2 font-semibold">L/P</th>
                            <th class="px-6 py-2 font-semibold">Wilayah</th>
                            <th class="px-6 py-2 font-semibold">Pekerjaan</th>
                            <th class="px-6 py-2 font-semibold">Pendidikan</th>
                            <th class="px-6 py-2 font-semibold">Agama</th>
                            <th class="px-6 py-2 font-semibold">Status Perkawinan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($analisis->siap as $baris)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-2 tabular-nums text-slate-400">{{ $baris->nomorBaris }}</td>
                                <td class="px-6 py-2 font-medium whitespace-nowrap text-slate-900">{{ $baris->nama }}</td>
                                <td class="px-6 py-2 whitespace-nowrap {{ $baris->tanggalLahir ? 'text-slate-600' : 'text-slate-400 italic' }}">
                                    {{ $baris->tanggalLahirTerbaca() }}
                                </td>
                                <td class="px-6 py-2 text-slate-600">{{ $baris->jenisKelamin }}</td>
                                <td class="px-6 py-2 whitespace-nowrap text-slate-600">{{ $baris->wilayah }}</td>
                                <td class="px-6 py-2 text-slate-600">{{ $baris->pekerjaan ?? '—' }}</td>
                                <td class="px-6 py-2 text-slate-600">{{ $baris->pendidikan ?? '—' }}</td>
                                <td class="px-6 py-2 text-slate-600">{{ $baris->agama ?? '—' }}</td>
                                <td class="px-6 py-2 text-slate-600">{{ $baris->statusPerkawinan ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ------------------------- Keputusan ------------------------- --}}
    <div class="sticky bottom-4 rounded-2xl bg-white p-5 shadow-lg ring-1 ring-slate-300">
        @if ($analisis->tidakAdaYangBisaDiimpor())
            <p class="mb-4 text-sm text-slate-600">
                Tidak ada baris yang dapat diimpor dari berkas ini. Perbaiki berkasnya lalu unggah ulang.
            </p>
        @else
            <p class="mb-4 text-sm text-slate-700">
                Akan ditambahkan
                <span class="font-semibold text-slate-900">
                    <span x-text="{{ count($analisis->siap) }} + dipilih.length"></span> warga
                </span>
                @if ($analisis->adaGanda())
                    — {{ count($analisis->siap) }} data baru
                    ditambah <span x-text="dipilih.length"></span> data ganda yang Anda setujui.
                @endif
            </p>
        @endif

        <form id="formImpor" method="POST" action="{{ route('admin.penduduk.impor.proses') }}"
              data-konfirmasi
              data-judul="Simpan data ke database?"
              data-pesan="{{ count($analisis->siap) }} warga baru akan ditambahkan, ditambah data ganda yang Anda centang."
              @if ($analisis->adaGanda())
                  data-catatan="Data ganda yang tidak dicentang tidak akan diimpor. Periksa sekali lagi sebelum melanjutkan."
              @endif
              data-tombol="Ya, Simpan Sekarang"
              class="flex flex-wrap items-center gap-3">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            @if (! $analisis->tidakAdaYangBisaDiimpor())
                <button type="submit"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white
                               transition hover:bg-emerald-700">
                    Lanjut Impor
                </button>
            @endif
        </form>

        <form method="POST" action="{{ route('admin.penduduk.impor.batal') }}"
              data-konfirmasi data-bahaya
              data-judul="Batalkan impor?"
              data-pesan="Berkas yang sudah diunggah akan dibuang dan tidak ada data yang tersimpan."
              data-tombol="Ya, Batalkan"
              class="mt-3">
            @csrf
            <button type="submit"
                    class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700
                           transition hover:bg-slate-50">
                Batal
            </button>
            <span class="ml-2 text-xs text-slate-500">Berkas dibuang, tidak ada yang tersimpan.</span>
        </form>
    </div>
</div>
@endsection
