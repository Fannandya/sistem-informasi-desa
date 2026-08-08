@extends('publik.layouts.app')

@section('judul', 'Statistik Penduduk')
@section('deskripsi', 'Data kependudukan ' . $desa->nama_desa . ': jumlah penduduk, kepala keluarga, sebaran umur, dan pendidikan.')

@section('konten')
<div class="mx-auto max-w-5xl px-6 md:px-12 py-12 md:py-16">

    <nav class="mb-6 text-sm text-on-surface-variant font-medium">
        <a href="{{ route('beranda') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="mx-2 text-outline-variant/60">/</span>
        <span class="text-on-surface">Statistik</span>
    </nav>

    <header class="mb-10 max-w-2xl">
        <h1 class="text-4xl font-semibold text-primary font-headline-md tracking-tight">Statistik Penduduk</h1>
        <p class="mt-3 text-on-surface-variant leading-relaxed">
            Gambaran kependudukan {{ $desa->nama_desa }} sebagai bentuk keterbukaan informasi desa.
        </p>
    </header>

    {{-- ------------------------- Ringkasan ------------------------- --}}
    <div class="mb-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-outline-variant/60 bg-surface-container-lowest p-6 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-sm font-medium text-on-surface-variant">Jumlah Penduduk</p>
            <p class="mt-2 text-4xl font-semibold text-on-surface font-headline-md tracking-tight">
                {{ number_format($ringkasan['jumlah_penduduk'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs font-medium text-on-surface-variant/80">jiwa</p>
        </div>

        @if ($ringkasan['jumlah_kk'] !== null)
            <div class="rounded-2xl border border-outline-variant/60 bg-surface-container-lowest p-6 shadow-sm hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-on-surface-variant">Kepala Keluarga</p>
                <p class="mt-2 text-4xl font-semibold text-on-surface font-headline-md tracking-tight">
                    {{ number_format($ringkasan['jumlah_kk'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs font-medium text-on-surface-variant/80">data tahun {{ $ringkasan['tahun_kk'] }}</p>
            </div>
        @endif

        <div class="rounded-2xl border border-outline-variant/60 bg-surface-container-lowest p-6 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-sm font-medium text-on-surface-variant">Laki-laki</p>
            <p class="mt-2 text-4xl font-semibold text-on-surface font-headline-md tracking-tight">
                {{ number_format($ringkasan['laki'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs font-medium text-on-surface-variant/80">jiwa</p>
        </div>

        <div class="rounded-2xl border border-outline-variant/60 bg-surface-container-lowest p-6 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-sm font-medium text-on-surface-variant">Perempuan</p>
            <p class="mt-2 text-4xl font-semibold text-on-surface font-headline-md tracking-tight">
                {{ number_format($ringkasan['perempuan'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs font-medium text-on-surface-variant/80">jiwa</p>
        </div>
    </div>

    @if ($ringkasan['jiwa_per_kk'] !== null)
        <div class="mb-12 inline-flex items-center gap-2 rounded-lg bg-primary/10 px-4 py-2 text-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-primary text-[20px]">info</span>
            <p>
                Rata-rata <span class="font-semibold text-primary">{{ number_format($ringkasan['jiwa_per_kk'], 2, ',', '.') }}</span>
                jiwa per keluarga, tersebar di <span class="font-semibold text-primary">{{ $jumlahDusun }}</span> dusun.
            </p>
        </div>
    @endif

    {{-- ------------------------- Grafik ------------------------- --}}
    <div class="grid gap-6 lg:grid-cols-2">

        <section class="rounded-2xl border border-outline-variant/60 bg-surface-container-lowest shadow-sm p-6" data-grafik>
            <h2 class="mb-6 text-lg font-semibold text-on-surface font-headline-md">Sebaran Kelompok Umur</h2>
            <div class="relative h-72">
                <canvas id="grafikUmurPublik" class="hidden"></canvas>
                <div data-status="muat" class="absolute inset-0 flex items-center justify-center text-sm font-medium text-on-surface-variant bg-surface-container-lowest/50 backdrop-blur-sm">Memuat…</div>
                <div data-status="kosong" class="absolute inset-0 hidden flex-col items-center justify-center text-sm font-medium text-on-surface-variant">Belum ada data</div>
                <div data-status="gagal" class="absolute inset-0 hidden flex-col items-center justify-center text-sm font-medium text-error">Gagal memuat grafik</div>
            </div>

            {{-- Padanan tabel: grafik saja tidak terbaca pembaca layar. --}}
            <details class="mt-6 group">
                <summary class="cursor-pointer text-sm font-medium text-primary hover:text-primary-container transition-colors flex items-center gap-1 select-none">
                    <span class="material-symbols-outlined text-[18px] transition-transform group-open:rotate-180">expand_more</span>
                    Lihat dalam bentuk tabel
                </summary>
                <div class="mt-4 overflow-hidden rounded-xl border border-outline-variant/40">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-high text-left text-xs font-semibold text-on-surface uppercase tracking-wider">
                            <tr><th class="px-4 py-3">Kelompok</th><th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3 text-right">Persen</th></tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30 bg-surface-container-lowest">
                            @foreach ($sebaranUmur as $baris)
                                <tr class="hover:bg-surface-container-low/50 transition-colors">
                                    <td class="px-4 py-2.5 font-medium text-on-surface">{{ $baris['label'] }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-on-surface">{{ $baris['tampilan'] }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-on-surface-variant">{{ $baris['persen'] !== null ? $baris['persen'].'%' : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </section>

        <section class="rounded-2xl border border-outline-variant/60 bg-surface-container-lowest shadow-sm p-6" data-grafik>
            <h2 class="mb-6 text-lg font-semibold text-on-surface font-headline-md">Tingkat Pendidikan</h2>
            <div class="relative h-72">
                <canvas id="grafikPendidikanPublik" class="hidden"></canvas>
                <div data-status="muat" class="absolute inset-0 flex items-center justify-center text-sm font-medium text-on-surface-variant bg-surface-container-lowest/50 backdrop-blur-sm">Memuat…</div>
                <div data-status="kosong" class="absolute inset-0 hidden flex-col items-center justify-center text-sm font-medium text-on-surface-variant">Belum ada data</div>
                <div data-status="gagal" class="absolute inset-0 hidden flex-col items-center justify-center text-sm font-medium text-error">Gagal memuat grafik</div>
            </div>

            <details class="mt-6 group">
                <summary class="cursor-pointer text-sm font-medium text-primary hover:text-primary-container transition-colors flex items-center gap-1 select-none">
                    <span class="material-symbols-outlined text-[18px] transition-transform group-open:rotate-180">expand_more</span>
                    Lihat dalam bentuk tabel
                </summary>
                <div class="mt-4 overflow-hidden rounded-xl border border-outline-variant/40">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-container-high text-left text-xs font-semibold text-on-surface uppercase tracking-wider">
                            <tr><th class="px-4 py-3">Jenjang</th><th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3 text-right">Persen</th></tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30 bg-surface-container-lowest">
                            @foreach ($pendidikan as $baris)
                                <tr class="hover:bg-surface-container-low/50 transition-colors">
                                    <td class="px-4 py-2.5 font-medium text-on-surface">{{ $baris['label'] }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-on-surface">{{ $baris['tampilan'] }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-on-surface-variant">{{ $baris['persen'] !== null ? $baris['persen'].'%' : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </section>
    </div>

    {{-- ------------------------- Piramida Penduduk ------------------------- --}}
    <section class="mt-8 rounded-2xl border border-outline-variant/60 bg-surface-container-lowest shadow-sm p-6" data-grafik>
        <h2 class="mb-6 text-lg font-semibold text-on-surface font-headline-md">Piramida Penduduk</h2>
        <div class="relative h-[32rem]">
            <canvas id="grafikPiramidaPublik" class="hidden"></canvas>
            <div data-status="muat" class="absolute inset-0 flex items-center justify-center text-sm font-medium text-on-surface-variant bg-surface-container-lowest/50 backdrop-blur-sm">Memuat…</div>
            <div data-status="kosong" class="absolute inset-0 hidden flex-col items-center justify-center text-sm font-medium text-on-surface-variant">Belum ada data</div>
            <div data-status="gagal" class="absolute inset-0 hidden flex-col items-center justify-center text-sm font-medium text-error">Gagal memuat grafik</div>
        </div>

        <details class="mt-6 group">
            <summary class="cursor-pointer text-sm font-medium text-primary hover:text-primary-container transition-colors flex items-center gap-1 select-none">
                <span class="material-symbols-outlined text-[18px] transition-transform group-open:rotate-180">expand_more</span>
                Lihat dalam bentuk tabel
            </summary>
            <div class="mt-4 overflow-hidden rounded-xl border border-outline-variant/40 max-w-3xl">
                <table class="w-full text-sm">
                    <thead class="bg-surface-container-high text-left text-xs font-semibold text-on-surface uppercase tracking-wider">
                        <tr><th class="px-4 py-3">Kelompok Umur</th><th class="px-4 py-3 text-right">Laki-laki</th><th class="px-4 py-3 text-right">Perempuan</th></tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30 bg-surface-container-lowest">
                        @foreach ($piramida as $baris)
                            <tr class="hover:bg-surface-container-low/50 transition-colors">
                                <td class="px-4 py-2.5 font-medium text-on-surface">{{ $baris['label'] }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums text-on-surface">{{ $baris['laki_tampilan'] }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums text-on-surface">{{ $baris['perempuan_tampilan'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </section>

    {{-- ------------------------- Berdasarkan Pekerjaan ------------------------- --}}
    <section class="mt-8 overflow-hidden rounded-2xl border border-outline-variant/60 bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant/40 bg-surface-container-lowest px-6 py-5">
            <h2 class="text-lg font-semibold text-on-surface font-headline-md">Berdasarkan Pekerjaan</h2>
        </div>
        <div class="grid lg:grid-cols-[minmax(0,1fr)_2fr]">
            <div class="max-h-[28rem] overflow-y-auto border-b border-outline-variant/40 lg:border-r lg:border-b-0 scrollbar-gelap">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-surface-container-high text-left text-xs tracking-wider text-on-surface uppercase z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-3.5 font-semibold">Jenis Pekerjaan</th>
                            <th class="px-6 py-3.5 text-right font-semibold">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30 bg-surface-container-lowest">
                        @forelse ($sebaranPekerjaan as $baris)
                            <tr class="hover:bg-surface-container-low/50 transition-colors">
                                <td class="px-6 py-3 font-medium text-on-surface">{{ $baris['label'] }}</td>
                                <td class="px-6 py-3 text-right tabular-nums text-on-surface">{{ $baris['tampilan'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-6 py-12 text-center text-on-surface-variant">Belum ada data pekerjaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="bg-surface-container-low p-6 lg:p-8">
                <h3 class="mb-4 text-sm font-semibold text-on-surface-variant uppercase tracking-wider">Top 6 Pekerjaan</h3>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach (array_slice($sebaranPekerjaan, 0, 6) as $baris)
                        <div class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-5 shadow-sm hover:-translate-y-0.5 transition-transform">
                            <p class="text-xs font-semibold text-on-surface-variant line-clamp-2" title="{{ $baris['label'] }}">{{ $baris['label'] }}</p>
                            <p class="mt-4 text-right text-3xl font-bold text-primary font-headline-md">{{ $baris['tampilan'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ------------------------- Berdasarkan Agama ------------------------- --}}
    <section class="mt-8 overflow-hidden rounded-2xl border border-outline-variant/60 bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant/40 bg-surface-container-lowest px-6 py-5">
            <h2 class="text-lg font-semibold text-on-surface font-headline-md">Berdasarkan Agama</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-container-high text-left text-xs font-semibold text-on-surface uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Agama</th>
                        <th class="px-6 py-3.5 text-right">Jumlah</th>
                        <th class="px-6 py-3.5 text-right">Persen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30 bg-surface-container-lowest">
                    @forelse ($agama as $baris)
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-6 py-3 font-medium text-on-surface">{{ $baris['label'] }}</td>
                            <td class="px-6 py-3 text-right tabular-nums text-on-surface">{{ $baris['tampilan'] }}</td>
                            <td class="px-6 py-3 text-right tabular-nums text-on-surface-variant font-medium">{{ $baris['persen'] !== null ? $baris['persen'].'%' : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-12 text-center text-on-surface-variant">Belum ada data agama.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ------------------------- Berdasarkan Status Perkawinan ------------------------- --}}
    <section class="mt-8 overflow-hidden rounded-2xl border border-outline-variant/60 bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant/40 bg-surface-container-lowest px-6 py-5">
            <h2 class="text-lg font-semibold text-on-surface font-headline-md">Berdasarkan Status Perkawinan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-container-high text-left text-xs font-semibold text-on-surface uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Status Perkawinan</th>
                        <th class="px-6 py-3.5 text-right">Jumlah</th>
                        <th class="px-6 py-3.5 text-right">Persen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30 bg-surface-container-lowest">
                    @forelse ($statusPerkawinan as $baris)
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-6 py-3 font-medium text-on-surface">{{ $baris['label'] }}</td>
                            <td class="px-6 py-3 text-right tabular-nums text-on-surface">{{ $baris['tampilan'] }}</td>
                            <td class="px-6 py-3 text-right tabular-nums text-on-surface-variant font-medium">{{ $baris['persen'] !== null ? $baris['persen'].'%' : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-12 text-center text-on-surface-variant">Belum ada data status perkawinan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ------------------------- Sebaran dusun ------------------------- --}}
    <section class="mt-8 overflow-hidden rounded-2xl border border-outline-variant/60 bg-surface-container-lowest shadow-sm">
        <div class="border-b border-outline-variant/40 bg-surface-container-lowest px-6 py-5">
            <h2 class="text-lg font-semibold text-on-surface font-headline-md">Sebaran Penduduk per Dusun</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-container-high text-left text-xs font-semibold text-on-surface uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Dusun</th>
                        <th class="px-6 py-3.5 text-right">Penduduk</th>
                        <th class="px-6 py-3.5 text-right">KK</th>
                        <th class="px-6 py-3.5 text-right">Persen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30 bg-surface-container-lowest">
                    @forelse ($sebaranDusun as $baris)
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-6 py-3 font-medium text-on-surface">{{ $baris['label'] }}</td>
                            <td class="px-6 py-3 text-right tabular-nums text-on-surface">{{ $baris['tampilan'] }}</td>
                            <td class="px-6 py-3 text-right tabular-nums {{ $baris['jumlah_kk'] ? 'text-on-surface' : 'text-outline-variant' }}">
                                {{ $baris['jumlah_kk'] ? number_format($baris['jumlah_kk'], 0, ',', '.') : '—' }}
                            </td>
                            <td class="px-6 py-3 text-right tabular-nums text-on-surface-variant font-medium">{{ $baris['persen'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-on-surface-variant">Belum ada data wilayah.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-10 rounded-2xl bg-surface-container-low border border-outline-variant/40 px-6 py-5 text-xs sm:text-sm leading-relaxed text-on-surface-variant flex gap-4 items-start shadow-sm">
        <span class="material-symbols-outlined text-on-surface-variant/70 text-[24px] shrink-0 mt-0.5">policy</span>
        <div>
            <p class="font-medium text-on-surface">Pernyataan Privasi Data</p>
            <p class="mt-1">
                Data bersumber dari pendataan penduduk yang dikelola aparat {{ $desa->nama_desa }}.
                @if ($tanggalPembaruan)
                    Terakhir diperbarui <span class="font-medium text-on-surface-variant">{{ $tanggalPembaruan }}</span>.
                @endif
            </p>
            <p class="mt-2 text-on-surface-variant/90">
                Rincian dibatasi sampai tingkat dusun, dan kelompok berisi kurang dari
                <span class="font-medium text-on-surface">{{ \App\Queries\StatistikPublik::AMBANG }} orang</span> ditampilkan sebagai
                &ldquo;<span class="font-medium text-on-surface">&lt;{{ \App\Queries\StatistikPublik::AMBANG }}</span>&rdquo; agar tidak ada warga yang
                dapat dikenali dari angka ini. Baris bertanda
                &ldquo;<span class="font-medium text-on-surface">{{ \App\Queries\StatistikPublik::LABEL_DISAMARKAN }}</span>&rdquo; ikut ditutup —
                beserta persennya — supaya jumlah kelompok kecil tadi tidak dapat dihitung
                kembali dengan mengurangkan baris lain dari totalnya.
            </p>
        </div>
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
                        label: (k) => {
                            const b = baris[k.dataIndex];

                            if (b.jumlah !== null) {
                                return `${b.tampilan} warga (${b.persen}%)`;
                            }

                            // Tidak semua batang kosong berarti "di bawah
                            // ambang": ada juga yang benar-benar nol dan ada
                            // yang sengaja disamarkan agar kelompok kecil di
                            // tabel yang sama tidak bisa dihitung lewat
                            // pengurangan. Keterangannya mengikuti tampilan.
                            return b.tampilan.startsWith('<')
                                ? `kurang dari ${@js(\App\Queries\StatistikPublik::AMBANG)} orang`
                                : b.tampilan;
                        },
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
