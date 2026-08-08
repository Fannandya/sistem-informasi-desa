@extends('publik.layouts.app')

@section('judul', 'Statistik Penduduk')
@section('deskripsi', 'Data kependudukan ' . $desa->nama_desa . ': jumlah penduduk, kepala keluarga, sebaran umur, dan pendidikan.')

@section('konten')
<div class="mx-auto max-w-5xl px-6 md:px-12 py-12 md:py-16">

    <nav class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('beranda') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="mx-1.5">/</span>
        <span class="text-on-surface">Statistik</span>
    </nav>

    <header class="mb-8 max-w-2xl">
        <h1 class="text-3xl font-semibold text-primary">Statistik Penduduk</h1>
        <p class="mt-2 text-on-surface-variant">
            Gambaran kependudukan {{ $desa->nama_desa }} sebagai bentuk keterbukaan informasi desa.
        </p>
    </header>

    {{-- ------------------------- Ringkasan ------------------------- --}}
    <div class="mb-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest p-5 shadow-[0px_4px_20px_rgba(0,0,0,0.03)]">
            <p class="text-sm text-on-surface-variant">Jumlah Penduduk</p>
            <p class="mt-1 text-3xl font-semibold text-on-surface">
                {{ number_format($ringkasan['jumlah_penduduk'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-on-surface-variant">jiwa</p>
        </div>

        @if ($ringkasan['jumlah_kk'] !== null)
            <div class="rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest p-5 shadow-[0px_4px_20px_rgba(0,0,0,0.03)]">
                <p class="text-sm text-on-surface-variant">Kepala Keluarga</p>
                <p class="mt-1 text-3xl font-semibold text-on-surface">
                    {{ number_format($ringkasan['jumlah_kk'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-on-surface-variant">data tahun {{ $ringkasan['tahun_kk'] }}</p>
            </div>
        @endif

        <div class="rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest p-5 shadow-[0px_4px_20px_rgba(0,0,0,0.03)]">
            <p class="text-sm text-on-surface-variant">Laki-laki</p>
            <p class="mt-1 text-3xl font-semibold text-on-surface">
                {{ number_format($ringkasan['laki'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-on-surface-variant">jiwa</p>
        </div>

        <div class="rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest p-5 shadow-[0px_4px_20px_rgba(0,0,0,0.03)]">
            <p class="text-sm text-on-surface-variant">Perempuan</p>
            <p class="mt-1 text-3xl font-semibold text-on-surface">
                {{ number_format($ringkasan['perempuan'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-on-surface-variant">jiwa</p>
        </div>
    </div>

    @if ($ringkasan['jiwa_per_kk'] !== null)
        <p class="mb-10 text-sm text-on-surface-variant">
            Rata-rata <span class="font-semibold text-on-surface">{{ number_format($ringkasan['jiwa_per_kk'], 2, ',', '.') }}</span>
            jiwa per keluarga, tersebar di {{ $jumlahDusun }} dusun.
        </p>
    @endif

    {{-- ------------------------- Grafik ------------------------- --}}
    <div class="grid gap-6 lg:grid-cols-2">

        <section class="rounded-[12px] border border-outline-variant/30 p-5" data-grafik>
            <h2 class="mb-4 text-sm font-semibold text-primary">Sebaran Kelompok Umur</h2>
            <div class="relative h-72">
                <canvas id="grafikUmurPublik" class="hidden"></canvas>
                <div data-status="muat" class="absolute inset-0 flex items-center justify-center text-sm text-on-surface-variant">Memuat…</div>
                <div data-status="kosong" class="absolute inset-0 hidden flex-col items-center justify-center text-sm text-on-surface-variant">Belum ada data</div>
                <div data-status="gagal" class="absolute inset-0 hidden flex-col items-center justify-center text-sm text-amber-700">Gagal memuat grafik</div>
            </div>

            {{-- Padanan tabel: grafik saja tidak terbaca pembaca layar. --}}
            <details class="mt-4">
                <summary class="cursor-pointer text-xs text-on-surface-variant">Lihat dalam bentuk tabel</summary>
                <table class="mt-3 w-full text-sm">
                    <thead class="text-left text-xs text-on-surface-variant">
                        <tr><th class="py-1.5">Kelompok</th><th class="py-1.5 text-right">Jumlah</th><th class="py-1.5 text-right">Persen</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($sebaranUmur as $baris)
                            <tr>
                                <td class="py-1.5 text-on-surface">{{ $baris['label'] }}</td>
                                <td class="py-1.5 text-right tabular-nums text-on-surface">{{ $baris['tampilan'] }}</td>
                                <td class="py-1.5 text-right tabular-nums text-on-surface-variant">{{ $baris['persen'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        </section>

        <section class="rounded-[12px] border border-outline-variant/30 p-5" data-grafik>
            <h2 class="mb-4 text-sm font-semibold text-primary">Tingkat Pendidikan</h2>
            <div class="relative h-72">
                <canvas id="grafikPendidikanPublik" class="hidden"></canvas>
                <div data-status="muat" class="absolute inset-0 flex items-center justify-center text-sm text-on-surface-variant">Memuat…</div>
                <div data-status="kosong" class="absolute inset-0 hidden flex-col items-center justify-center text-sm text-on-surface-variant">Belum ada data</div>
                <div data-status="gagal" class="absolute inset-0 hidden flex-col items-center justify-center text-sm text-amber-700">Gagal memuat grafik</div>
            </div>

            <details class="mt-4">
                <summary class="cursor-pointer text-xs text-on-surface-variant">Lihat dalam bentuk tabel</summary>
                <table class="mt-3 w-full text-sm">
                    <thead class="text-left text-xs text-on-surface-variant">
                        <tr><th class="py-1.5">Jenjang</th><th class="py-1.5 text-right">Jumlah</th><th class="py-1.5 text-right">Persen</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($pendidikan as $baris)
                            <tr>
                                <td class="py-1.5 text-on-surface">{{ $baris['label'] }}</td>
                                <td class="py-1.5 text-right tabular-nums text-on-surface">{{ $baris['tampilan'] }}</td>
                                <td class="py-1.5 text-right tabular-nums text-on-surface-variant">{{ $baris['persen'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        </section>
    </div>

    {{-- ------------------------- Piramida Penduduk ------------------------- --}}
    <section class="mt-6 rounded-[12px] border border-outline-variant/30 p-5" data-grafik>
        <h2 class="mb-4 text-sm font-semibold text-primary">Piramida Penduduk</h2>
        <div class="relative h-[32rem]">
            <canvas id="grafikPiramidaPublik" class="hidden"></canvas>
            <div data-status="muat" class="absolute inset-0 flex items-center justify-center text-sm text-on-surface-variant">Memuat…</div>
            <div data-status="kosong" class="absolute inset-0 hidden flex-col items-center justify-center text-sm text-on-surface-variant">Belum ada data</div>
            <div data-status="gagal" class="absolute inset-0 hidden flex-col items-center justify-center text-sm text-amber-700">Gagal memuat grafik</div>
        </div>

        <details class="mt-4">
            <summary class="cursor-pointer text-xs text-on-surface-variant">Lihat dalam bentuk tabel</summary>
            <table class="mt-3 w-full text-sm">
                <thead class="text-left text-xs text-on-surface-variant">
                    <tr><th class="py-1.5">Kelompok Umur</th><th class="py-1.5 text-right">Laki-laki</th><th class="py-1.5 text-right">Perempuan</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($piramida as $baris)
                        <tr>
                            <td class="py-1.5 text-on-surface">{{ $baris['label'] }}</td>
                            <td class="py-1.5 text-right tabular-nums text-on-surface">{{ $baris['laki_tampilan'] }}</td>
                            <td class="py-1.5 text-right tabular-nums text-on-surface">{{ $baris['perempuan_tampilan'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </details>
    </section>

    {{-- ------------------------- Berdasarkan Pekerjaan ------------------------- --}}
    <section class="mt-6 overflow-hidden rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest shadow-[0px_4px_20px_rgba(0,0,0,0.03)]">
        <div class="border-b border-outline-variant/30 px-5 py-4">
            <h2 class="text-sm font-semibold text-primary">Berdasarkan Pekerjaan</h2>
        </div>
        <div class="grid lg:grid-cols-[minmax(0,1fr)_2fr]">
            <div class="max-h-96 overflow-y-auto border-b border-outline-variant/30 lg:border-r lg:border-b-0">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-surface-container-low text-left text-xs tracking-wider text-on-surface-variant uppercase">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Jenis Pekerjaan</th>
                            <th class="px-5 py-3 text-right font-semibold">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($sebaranPekerjaan as $baris)
                            <tr>
                                <td class="px-5 py-3 text-on-surface">{{ $baris['label'] }}</td>
                                <td class="px-5 py-3 text-right tabular-nums text-on-surface">{{ $baris['tampilan'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-5 py-10 text-center text-on-surface-variant">Belum ada data pekerjaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="grid grid-cols-2 gap-4 p-5 sm:grid-cols-3">
                @foreach (array_slice($sebaranPekerjaan, 0, 6) as $baris)
                    <div class="rounded-[12px] border border-outline-variant/30 bg-surface p-4">
                        <p class="text-xs font-medium text-on-surface-variant">{{ $baris['label'] }}</p>
                        <p class="mt-6 text-right text-2xl font-semibold text-on-surface">{{ $baris['tampilan'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ------------------------- Berdasarkan Agama ------------------------- --}}
    <section class="mt-6 overflow-hidden rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest shadow-[0px_4px_20px_rgba(0,0,0,0.03)]">
        <div class="border-b border-outline-variant/30 px-5 py-4">
            <h2 class="text-sm font-semibold text-primary">Berdasarkan Agama</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-container-low text-left text-xs tracking-wider text-on-surface-variant uppercase">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Agama</th>
                        <th class="px-5 py-3 text-right font-semibold">Jumlah</th>
                        <th class="px-5 py-3 text-right font-semibold">Persen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($agama as $baris)
                        <tr>
                            <td class="px-5 py-3 font-medium text-on-surface">{{ $baris['label'] }}</td>
                            <td class="px-5 py-3 text-right tabular-nums text-on-surface">{{ $baris['tampilan'] }}</td>
                            <td class="px-5 py-3 text-right tabular-nums text-on-surface-variant">{{ $baris['persen'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-10 text-center text-on-surface-variant">Belum ada data agama.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ------------------------- Berdasarkan Status Perkawinan ------------------------- --}}
    <section class="mt-6 overflow-hidden rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest shadow-[0px_4px_20px_rgba(0,0,0,0.03)]">
        <div class="border-b border-outline-variant/30 px-5 py-4">
            <h2 class="text-sm font-semibold text-primary">Berdasarkan Status Perkawinan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-container-low text-left text-xs tracking-wider text-on-surface-variant uppercase">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Status Perkawinan</th>
                        <th class="px-5 py-3 text-right font-semibold">Jumlah</th>
                        <th class="px-5 py-3 text-right font-semibold">Persen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($statusPerkawinan as $baris)
                        <tr>
                            <td class="px-5 py-3 font-medium text-on-surface">{{ $baris['label'] }}</td>
                            <td class="px-5 py-3 text-right tabular-nums text-on-surface">{{ $baris['tampilan'] }}</td>
                            <td class="px-5 py-3 text-right tabular-nums text-on-surface-variant">{{ $baris['persen'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-10 text-center text-on-surface-variant">Belum ada data status perkawinan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ------------------------- Sebaran dusun ------------------------- --}}
    <section class="mt-6 overflow-hidden rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest shadow-[0px_4px_20px_rgba(0,0,0,0.03)]">
        <div class="border-b border-outline-variant/30 px-5 py-4">
            <h2 class="text-sm font-semibold text-primary">Sebaran Penduduk per Dusun</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-container-low text-left text-xs tracking-wider text-on-surface-variant uppercase">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Dusun</th>
                        <th class="px-5 py-3 text-right font-semibold">Penduduk</th>
                        <th class="px-5 py-3 text-right font-semibold">KK</th>
                        <th class="px-5 py-3 text-right font-semibold">Persen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sebaranDusun as $baris)
                        <tr>
                            <td class="px-5 py-3 font-medium text-on-surface">{{ $baris['label'] }}</td>
                            <td class="px-5 py-3 text-right tabular-nums text-on-surface">{{ $baris['tampilan'] }}</td>
                            <td class="px-5 py-3 text-right tabular-nums {{ $baris['jumlah_kk'] ? 'text-on-surface' : 'text-slate-300' }}">
                                {{ $baris['jumlah_kk'] ? number_format($baris['jumlah_kk'], 0, ',', '.') : '—' }}
                            </td>
                            <td class="px-5 py-3 text-right tabular-nums text-on-surface-variant">{{ $baris['persen'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-on-surface-variant">Belum ada data wilayah.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-8 rounded-[12px] bg-surface-container-low px-5 py-4 text-xs leading-relaxed text-on-surface-variant">
        <p>
            Data bersumber dari pendataan penduduk yang dikelola aparat {{ $desa->nama_desa }}.
            @if ($tanggalPembaruan)
                Terakhir diperbarui {{ $tanggalPembaruan }}.
            @endif
        </p>
        <p class="mt-1.5">
            Rincian dibatasi sampai tingkat dusun, dan kelompok berisi kurang dari
            {{ \App\Queries\StatistikPublik::AMBANG }} orang ditampilkan sebagai
            &ldquo;&lt;{{ \App\Queries\StatistikPublik::AMBANG }}&rdquo; agar tidak ada warga yang
            dapat dikenali dari angka ini.
        </p>
    </div>
</div>
@endsection

@push('skrip')
@vite('resources/js/grafik.js')
<script type="module">
    const G = window.Grafik;

    const konfigurasiPublik = (jenis) => (baris) => ({
        type: jenis === 'umur' ? 'bar' : 'bar',
        data: {
            labels: baris.map((b) => b.label),
            datasets: [{
                label: 'Jumlah warga',
                // Kelompok yang disamarkan dikirim sebagai null, sehingga
                // batangnya memang tidak digambar — bukan digambar lalu
                // ditutupi.
                data: baris.map((b) => b.jumlah),
                backgroundColor: G.WARNA.utama,
                borderRadius: 6,
                maxBarThickness: 48,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (k) => baris[k.dataIndex].jumlah === null
                            ? `kurang dari ${@js(\App\Queries\StatistikPublik::AMBANG)} orang`
                            : `${baris[k.dataIndex].tampilan} warga (${baris[k.dataIndex].persen}%)`,
                    },
                },
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } },
            },
        },
    });

    G.muat('grafikUmurPublik', @js(route('publik.statistik.data', 'umur')), konfigurasiPublik('umur'));
    G.muat('grafikPendidikanPublik', @js(route('publik.statistik.data', 'pendidikan')), konfigurasiPublik('pendidikan'));
    G.muat('grafikPiramidaPublik', @js(route('publik.statistik.data', 'piramida')), G.piramida);
</script>
@endpush
