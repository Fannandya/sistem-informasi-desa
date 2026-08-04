@extends('admin.layouts.app')

@section('judul', 'Arsip Warga')

@section('konten')
<div class="space-y-5">

    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        Warga di halaman ini sudah keluar dari daftar aktif dan tidak ikut terhitung pada statistik
        jumlah penduduk. Datanya tetap tersimpan dan dapat dipulihkan kapan saja.
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">
            <span class="font-semibold text-slate-900">{{ number_format($daftar->total(), 0, ',', '.') }}</span>
            data terarsip{{ $pencarian->adaFilter() ? ' cocok dengan saringan' : '' }}
        </p>

        <x-admin.tombol-ekspor :pencarian="$pencarian" arsip :ada-saringan="$pencarian->adaFilter()" />
    </div>

    {{-- Saringan --}}
    <form method="GET" action="{{ route('admin.penduduk.arsip') }}"
          class="grid gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2 lg:grid-cols-5">

        <div class="lg:col-span-2">
            <label for="q" class="mb-1.5 block text-sm font-medium text-slate-700">Nama</label>
            <input id="q" type="search" name="q" value="{{ $pencarian->nilai('q') }}"
                   placeholder="Cari nama…"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm
                          focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
        </div>

        <x-admin.select name="alasan_keluar" label="Alasan Keluar" :value="$pencarian->nilai('alasan_keluar')"
                        :pilihan="\App\Enums\AlasanKeluar::pilihan()" kosong="Semua alasan" />

        <x-admin.field name="dari" label="Keluar Sejak" type="date" :value="$pencarian->nilai('dari')" />
        <x-admin.field name="sampai" label="Keluar Sampai" type="date" :value="$pencarian->nilai('sampai')" />

        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-5">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800">
                Terapkan
            </button>
            @if ($pencarian->adaFilter())
                <a href="{{ route('admin.penduduk.arsip') }}"
                   class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    Bersihkan
                </a>
            @endif
        </div>
    </form>

    @if ($pencarian->adaFilter())
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm text-slate-500">Saringan aktif:</span>
            @foreach ($pencarian->aktif() as $chip)
                <a href="{{ route('admin.penduduk.arsip', $chip['sisa']) }}"
                   class="group inline-flex items-center gap-1.5 rounded-full bg-slate-200 py-1 pr-2 pl-3 text-xs
                          font-medium text-slate-700 transition hover:bg-slate-300">
                    {{ $chip['label'] }}
                    <svg class="h-3.5 w-3.5 text-slate-500 group-hover:text-slate-800" fill="none"
                         stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            @endforeach
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @if ($daftar->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-medium text-slate-900">
                    {{ $pencarian->adaFilter() ? 'Tidak ada data arsip yang cocok' : 'Arsip masih kosong' }}
                </p>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    {{ $pencarian->adaFilter()
                        ? 'Coba longgarkan saringannya.'
                        : 'Warga yang diarsipkan dari halaman Data Warga akan muncul di sini.' }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">Umur Terakhir</th>
                            <th class="px-4 py-3 font-semibold">Wilayah</th>
                            <th class="px-4 py-3 font-semibold">Alasan Keluar</th>
                            <th class="px-4 py-3 font-semibold">Tanggal Keluar</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($daftar as $warga)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $warga->nama_lengkap }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $warga->tanggal_lahir && $warga->tanggal_keluar
                                        ? $warga->tanggal_lahir->diffInYears($warga->tanggal_keluar) . ' tahun'
                                        : '—' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ $warga->wilayah->dusun }}
                                    <span class="text-slate-400">· {{ $warga->wilayah->labelSingkat() }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($warga->alasan_keluar)
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $warga->alasan_keluar->warna() }}">
                                            {{ $warga->alasan_keluar->label() }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ $warga->tanggal_keluar?->translatedFormat('d M Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.penduduk.pulihkan', $warga->id) }}"
                                              data-konfirmasi
                                              data-judul="Pulihkan warga ini?"
                                              data-pesan="{{ $warga->nama_lengkap }} akan kembali ke daftar warga aktif."
                                              data-catatan="Alasan dan tanggal keluar dikosongkan, sehingga ia tidak lagi terhitung pada statistik tahun tersebut."
                                              data-tombol="Pulihkan">
                                            @csrf
                                            <button type="submit"
                                                    class="rounded-md px-2.5 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-50">
                                                Pulihkan
                                            </button>
                                        </form>

                                        {{-- Hapus permanen sengaja hanya ada di sini: warga harus melewati
                                             pengarsipan lebih dulu, sehingga tidak ada data yang lenyap tanpa
                                             pernah tercatat alasan keluarnya. --}}
                                        <form method="POST" action="{{ route('admin.penduduk.hapus-permanen', $warga->id) }}"
                                              data-konfirmasi data-bahaya
                                              data-judul="Hapus permanen dari database?"
                                              data-pesan="{{ $warga->nama_lengkap }} akan dihapus selamanya. Tindakan ini tidak dapat dibatalkan dan datanya tidak dapat dipulihkan."
                                              @if ($warga->alasan_keluar?->value === 'meninggal')
                                                  data-catatan="Warga ini tercatat meninggal pada {{ $warga->tanggal_keluar?->translatedFormat('d F Y') }}. Menghapusnya membuat angka kematian tahun {{ $warga->tanggal_keluar?->year }} berkurang satu, sehingga laporan yang sudah dibuat tidak lagi cocok."
                                              @elseif ($warga->alasan_keluar?->value === 'pindah_keluar')
                                                  data-catatan="Warga ini tercatat pindah keluar pada {{ $warga->tanggal_keluar?->translatedFormat('d F Y') }}. Menghapusnya membuat angka perpindahan tahun {{ $warga->tanggal_keluar?->year }} berkurang satu."
                                              @else
                                                  data-catatan="Data bertanda duplikat tidak pernah masuk statistik, jadi menghapusnya tidak mengubah angka apa pun."
                                              @endif
                                              data-ketik="{{ $warga->nama_lengkap }}"
                                              data-ketik-field="konfirmasi_nama"
                                              data-tombol="Hapus Permanen">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-md px-2.5 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($daftar->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $daftar->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
