@extends('admin.layouts.app')

@section('judul', 'Rekap Kepala Keluarga')

@section('konten')
<div class="max-w-4xl space-y-5">

    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        Nomor Kartu Keluarga sengaja tidak disimpan sistem ini, sehingga jumlah keluarga tidak bisa
        dihitung otomatis dari data warga. Angka di halaman inilah yang dipakai sebagai
        <strong>Jumlah KK</strong> pada dashboard dan halaman statistik publik.
    </div>

    {{-- ------------------- Pemilih tahun & ringkasan ------------------- --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-end justify-between gap-4">

            <form method="GET" action="{{ route('admin.rekap-kk.index') }}" class="flex items-end gap-3">
                <div>
                    <label for="tahun" class="mb-1.5 block text-sm font-medium text-slate-700">Tahun Rekap</label>
                    <select id="tahun" name="tahun" onchange="this.form.submit()"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm
                                   focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                        @foreach ($tahunTersedia as $t)
                            <option value="{{ $t }}" @selected($t === $tahun)>
                                {{ $t }}{{ $t === now()->year ? ' (tahun ini)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="flex flex-wrap items-center gap-6 text-right">
                <div>
                    <p class="text-xs tracking-wider text-slate-500 uppercase">Total KK</p>
                    <p class="text-2xl font-semibold text-slate-900">
                        {{ number_format($ringkasan->total(), 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs tracking-wider text-slate-500 uppercase">Rata-rata Jiwa/KK</p>
                    <p class="text-2xl font-semibold {{ $ringkasan->rataJiwaPerKk() === null ? 'text-slate-300' : 'text-slate-900' }}">
                        {{ $ringkasan->rataJiwaPerKk() === null
                            ? '—'
                            : number_format($ringkasan->rataJiwaPerKk(), 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Kelengkapan --}}
        <div class="mt-4 border-t border-slate-200 pt-4">
            @if ($ringkasan->jumlahBelumDiisi() > 0)
                <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span>
                        <strong>{{ $ringkasan->keteranganKelengkapan() }}</strong>
                        Total di atas belum menghitung {{ $ringkasan->jumlahBelumDiisi() }} RT yang masih kosong,
                        jadi angkanya lebih kecil dari keadaan sebenarnya.
                    </span>
                </div>
            @else
                <p class="flex items-center gap-2 text-sm text-emerald-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Rekap tahun {{ $tahun }} sudah lengkap — {{ $ringkasan->keteranganKelengkapan() }}
                </p>
            @endif

            @if ($terakhirDiperbarui)
                <p class="mt-2 text-xs text-slate-500">
                    Terakhir diperbarui {{ $terakhirDiperbarui->updated_at->translatedFormat('d F Y, H:i') }}
                    @if ($terakhirDiperbarui->admin)
                        oleh {{ $terakhirDiperbarui->admin->nama }}
                    @endif
                </p>
            @endif
        </div>
    </div>

    {{-- ------------------------- Formulir isian ------------------------- --}}
    <form method="POST" action="{{ route('admin.rekap-kk.simpan', $tahun) }}"
          data-lacak-perubahan class="space-y-5">
        @csrf

        @if ($kelompok->isEmpty())
            <div class="rounded-2xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-slate-200">
                <p class="font-medium text-slate-900">Belum ada wilayah terdaftar</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    Rekap KK diisi per RT, jadi daftar Dusun/RW/RT harus dibuat lebih dulu.
                </p>
                <a href="{{ route('admin.wilayah.index') }}"
                   class="mt-5 inline-block rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    Kelola Wilayah
                </a>
            </div>
        @else
            @foreach ($kelompok as $dusun => $daftarRt)
                @php
                    $subtotal = $daftarRt->sum(fn ($w) => (int) old("jumlah.{$w->id}", $tersimpan[$w->id] ?? 0));
                    $kosong = $daftarRt->filter(fn ($w) => ! filled(old("jumlah.{$w->id}", $tersimpan[$w->id] ?? null)))->count();
                @endphp

                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 bg-slate-50 px-5 py-3">
                        <h2 class="text-sm font-semibold text-slate-900">{{ $dusun }}</h2>
                        <p class="text-sm text-slate-600">
                            Subtotal <span class="font-semibold text-slate-900">{{ number_format($subtotal, 0, ',', '.') }}</span> KK
                            @if ($kosong > 0)
                                <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                                    {{ $kosong }} RT kosong
                                </span>
                            @endif
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($daftarRt as $w)
                            @php
                                $nilai = old("jumlah.{$w->id}", $tersimpan[$w->id] ?? null);
                                $belumDiisi = ! filled($nilai) && $nilai !== 0 && $nilai !== '0';
                            @endphp
                            <div class="flex flex-wrap items-center gap-4 px-5 py-3
                                        {{ $belumDiisi ? 'bg-amber-50/40' : '' }}">
                                <label for="kk_{{ $w->id }}" class="min-w-40 flex-1 text-sm text-slate-700">
                                    RT {{ $w->rt }} / RW {{ $w->rw }}
                                    @if ($belumDiisi)
                                        <span class="ml-1 text-xs text-amber-700">belum diisi</span>
                                    @endif
                                </label>

                                <div class="w-32">
                                    <input id="kk_{{ $w->id }}" name="jumlah[{{ $w->id }}]" type="number"
                                           min="0" max="100000" inputmode="numeric"
                                           value="{{ $nilai }}" placeholder="—"
                                           class="w-full rounded-lg border px-3 py-2 text-right text-sm tabular-nums shadow-sm
                                                  focus:ring-2 focus:outline-none
                                                  {{ $errors->has("jumlah.{$w->id}")
                                                      ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20'
                                                      : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-500/20' }}">
                                </div>

                                <span class="w-8 text-sm text-slate-400">KK</span>
                            </div>

                            @error("jumlah.{$w->id}")
                                <p class="px-5 pb-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                               transition hover:bg-emerald-700">
                    Simpan Rekap {{ $tahun }}
                </button>

                <p class="text-xs text-slate-500">
                    Isian yang dikosongkan akan dikembalikan menjadi “belum diisi”, bukan disimpan sebagai nol.
                </p>
            </div>
        @endif
    </form>

    {{-- Salin tahun sebelumnya — form terpisah agar tidak ikut terkirim saat menyimpan --}}
    @if ($adaTahunSebelumnya && ! $kelompok->isEmpty())
        <form method="POST" action="{{ route('admin.rekap-kk.salin', $tahun) }}"
              data-konfirmasi
              data-judul="Salin angka tahun {{ $tahun - 1 }}?"
              data-pesan="Isian yang sekarang tampil akan tertimpa angka tahun {{ $tahun - 1 }}."
              data-catatan="Belum ada yang tersimpan sampai Anda menekan tombol Simpan Rekap."
              data-tombol="Muat ke Formulir"
              class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            @csrf
            <div class="flex flex-wrap items-center gap-3">
                <button type="submit"
                        class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700
                               transition hover:bg-slate-50">
                    Salin Angka Tahun {{ $tahun - 1 }}
                </button>
                <p class="text-xs text-slate-500">
                    Angka dimuat ke formulir sebagai titik awal. Belum tersimpan sampai Anda menekan Simpan.
                </p>
            </div>
        </form>
    @endif
</div>
@endsection
