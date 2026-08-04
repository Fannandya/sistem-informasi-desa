@extends('admin.layouts.app')

@section('judul', 'Impor Data Warga')

@php $hasil = session('hasilImpor'); @endphp

@section('konten')
<div class="max-w-3xl space-y-6">

    <nav class="text-sm">
        <a href="{{ route('admin.penduduk.index') }}" class="text-slate-500 hover:text-slate-800">
            &larr; Kembali ke Data Warga
        </a>
    </nav>

    {{-- ------------------ Hasil impor terakhir ------------------ --}}
    @if ($hasil)
        <div class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-sm font-semibold text-slate-900">Hasil Impor</h2>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl bg-emerald-50 p-4">
                    <p class="text-2xl font-semibold text-emerald-700">{{ $hasil->berhasil }}</p>
                    <p class="text-xs text-emerald-800">baris masuk</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-4">
                    <p class="text-2xl font-semibold text-amber-700">{{ count($hasil->duplikat) }}</p>
                    <p class="text-xs text-amber-800">dilewati (ganda)</p>
                </div>
                <div class="rounded-xl bg-red-50 p-4">
                    <p class="text-2xl font-semibold text-red-700">{{ count($hasil->gagal) }}</p>
                    <p class="text-xs text-red-800">gagal</p>
                </div>
            </div>

            @if ($hasil->gagal !== [])
                <div>
                    <h3 class="mb-2 text-sm font-medium text-slate-900">Baris yang gagal</h3>
                    <div class="max-h-72 overflow-y-auto rounded-lg ring-1 ring-slate-200">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                                <tr>
                                    <th class="px-3 py-2 font-semibold">Baris</th>
                                    <th class="px-3 py-2 font-semibold">Nama</th>
                                    <th class="px-3 py-2 font-semibold">Alasan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($hasil->gagal as $baris)
                                    <tr>
                                        <td class="px-3 py-2 tabular-nums text-slate-500">{{ $baris['baris'] }}</td>
                                        <td class="px-3 py-2 text-slate-900">{{ $baris['nama'] }}</td>
                                        <td class="px-3 py-2 text-red-700">{{ $baris['alasan'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">
                        Perbaiki baris-baris ini di berkas asli, lalu unggah ulang. Baris yang sudah
                        berhasil masuk akan terdeteksi sebagai ganda dan dilewati.
                    </p>
                </div>
            @endif

            @if ($hasil->duplikat !== [])
                <div>
                    <h3 class="mb-2 text-sm font-medium text-slate-900">Baris yang dilewati</h3>
                    <div class="max-h-56 overflow-y-auto rounded-lg ring-1 ring-slate-200">
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($hasil->duplikat as $baris)
                                    <tr>
                                        <td class="px-3 py-2 tabular-nums text-slate-500">{{ $baris['baris'] }}</td>
                                        <td class="px-3 py-2 text-slate-900">{{ $baris['nama'] }}</td>
                                        <td class="px-3 py-2 text-amber-700">{{ $baris['keterangan'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">
                        Tanpa NIK atau nomor KK, kesamaan nama + tanggal lahir + wilayah adalah petunjuk
                        terkuat yang tersedia — tetapi bukan kepastian. Periksa daftar ini bila ada warga
                        berbeda yang kebetulan sama persis ketiganya.
                    </p>
                </div>
            @endif
        </div>
    @endif

    {{-- ------------------ Form unggah ------------------ --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-sm font-semibold text-slate-900">Unggah Berkas</h2>
        <p class="mt-1 mb-5 text-xs text-slate-500">
            Format .xlsx, .xls, atau .csv. Maksimal
            {{ number_format(config('impor.maks_baris'), 0, ',', '.') }} baris dan
            {{ round(config('impor.maks_kb') / 1024) }} MB per berkas.
        </p>

        <form method="POST" action="{{ route('admin.penduduk.impor.proses') }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label for="berkas" class="mb-1.5 block text-sm font-medium text-slate-700">
                    Berkas Data Warga <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <input id="berkas" name="berkas" type="file" required
                       accept=".xlsx,.xls,.csv,text/csv"
                       class="block w-full text-sm text-slate-600
                              file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2
                              file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                @error('berkas')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3">
                <input type="checkbox" name="lewati_duplikat" value="1" checked
                       class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <span>
                    <span class="block text-sm font-medium text-slate-900">Lewati baris yang terdeteksi ganda</span>
                    <span class="block text-xs text-slate-500">
                        Baris dianggap ganda bila nama, tanggal lahir, dan wilayahnya sama persis dengan
                        warga yang sudah ada. Hilangkan centang bila desa Anda memang punya warga
                        berbeda dengan ketiga data itu sama.
                    </span>
                </span>
            </label>

            <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
                <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                               transition hover:bg-emerald-700">
                    Mulai Impor
                </button>
                <a href="{{ route('admin.penduduk.impor.contoh') }}"
                   class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700
                          transition hover:bg-slate-50">
                    Unduh Berkas Contoh
                </a>
            </div>
        </form>
    </div>

    {{-- ------------------ Panduan ------------------ --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="mb-4 text-sm font-semibold text-slate-900">Susunan Kolom</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                    <tr>
                        <th class="px-3 py-2 font-semibold">Kolom</th>
                        <th class="px-3 py-2 font-semibold">Wajib</th>
                        <th class="px-3 py-2 font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $panduan = [
                            'Nama Lengkap' => [true, 'Nama warga.'],
                            'Tanggal Lahir' => [false, 'Format 1985-04-17 atau 17/04/1985. Boleh dikosongkan.'],
                            'Jenis Kelamin' => [true, 'L atau P. Boleh juga ditulis Laki-laki / Perempuan.'],
                            'Dusun' => [true, 'Harus sama persis dengan data wilayah yang sudah terdaftar.'],
                            'RW' => [true, 'Boleh ditulis 1 atau 001.'],
                            'RT' => [true, 'Boleh ditulis 1 atau 001.'],
                            'Pekerjaan' => [false, 'Harus ada pada daftar pekerjaan. Kosongkan bila belum diketahui.'],
                            'Pendidikan' => [false, 'SD, SMP, SMA, D3, S1, dan seterusnya.'],
                        ];
                    @endphp
                    @foreach ($panduan as $kolom => [$wajib, $keterangan])
                        <tr>
                            <td class="px-3 py-2 font-medium whitespace-nowrap text-slate-900">{{ $kolom }}</td>
                            <td class="px-3 py-2">
                                @if ($wajib)
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-700">wajib</span>
                                @else
                                    <span class="text-xs text-slate-400">opsional</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-slate-600">{{ $keterangan }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <p class="font-medium">Berkas tidak boleh memuat NIK, nomor KK, atau nomor telepon.</p>
            <p class="mt-1 text-xs">
                Bila salah satunya ditemukan, seluruh berkas ditolak dan tidak ada satu baris pun
                yang diimpor. Hapus kolom tersebut dari berkas sumber lebih dulu.
            </p>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <p class="mb-1.5 text-xs font-semibold tracking-wider text-slate-500 uppercase">
                    Wilayah terdaftar ({{ $wilayahList->count() }})
                </p>
                <div class="max-h-40 overflow-y-auto rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                    @forelse ($wilayahList as $w)
                        <p>{{ $w->dusun }} · RT {{ $w->rt }}/RW {{ $w->rw }}</p>
                    @empty
                        <p class="text-slate-400">Belum ada. Tambahkan di menu Wilayah lebih dulu.</p>
                    @endforelse
                </div>
            </div>
            <div>
                <p class="mb-1.5 text-xs font-semibold tracking-wider text-slate-500 uppercase">
                    Pekerjaan terdaftar ({{ $pekerjaanList->count() }})
                </p>
                <div class="max-h-40 overflow-y-auto rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                    @forelse ($pekerjaanList as $p)
                        <p>{{ $p->nama }}</p>
                    @empty
                        <p class="text-slate-400">Belum ada. Tambahkan di menu Pekerjaan lebih dulu.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
