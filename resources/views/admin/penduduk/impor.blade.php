@extends('admin.layouts.app')

@section('judul', 'Impor Data Warga')

@section('konten')
<div class="max-w-3xl space-y-6">

    <nav class="text-sm">
        <a href="{{ route('admin.penduduk.index') }}" class="text-slate-500 hover:text-slate-800">
            &larr; Kembali ke Data Warga
        </a>
    </nav>

    {{-- ------------------ Form unggah ------------------ --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-sm font-semibold text-slate-900">Unggah Berkas</h2>
        <p class="mt-1 mb-5 text-xs text-slate-500">
            Format .xlsx, .xls, atau .csv. Maksimal
            {{ number_format(config('impor.maks_baris'), 0, ',', '.') }} baris dan
            {{ round(config('impor.maks_kb') / 1024) }} MB per berkas.
        </p>

        <div class="mb-5 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            Berkas <strong>tidak langsung disimpan</strong>. Setelah diunggah, Anda akan melihat
            daftar lengkap data yang akan ditambahkan beserta data yang terdeteksi ganda,
            lalu memutuskan sendiri apakah impor dilanjutkan atau dibatalkan.
        </div>

        <form method="POST" action="{{ route('admin.penduduk.impor.pratinjau') }}"
              enctype="multipart/form-data"
              data-konfirmasi
              data-judul="Periksa berkas ini?"
              data-pesan="Berkas akan dibaca dan ditampilkan hasilnya untuk Anda periksa."
              data-catatan="Belum ada data yang tersimpan pada langkah ini."
              data-tombol="Periksa Sekarang"
              class="space-y-5">
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

            <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
                <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                               transition hover:bg-emerald-700">
                    Periksa Berkas
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
                Bila salah satunya ditemukan, seluruh berkas ditolak. Hapus kolom tersebut dari
                berkas sumber lebih dulu.
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
