@extends('admin.layouts.app')

@section('judul', 'Dashboard')

@section('konten')

@if ($kk->adaData() && ! $kk->lengkap())
    <!-- Warning Banner -->
    <div class="bg-secondary-fixed-dim/20 border border-secondary text-primary-container px-6 py-4 rounded-xl mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between shadow-sm">
        <div class="flex items-center space-x-3 mb-3 sm:mb-0">
            <span class="material-symbols-outlined text-secondary">warning</span>
            <span class="font-label-md text-sm font-bold text-on-surface">{{ $kk->jumlahBelumDiisi() }} RT belum diisi data KK (Tahun {{ $kk->tahunAcuan() }})</span>
        </div>
        <a href="{{ route('admin.rekap-kk.index', ['tahun' => $kk->tahunAcuan()]) }}" class="font-label-sm text-sm font-medium underline hover:text-primary text-on-surface">Perbaiki Sekarang</a>
    </div>
@endif

<div class="space-y-8">
    <div>
        <h2 class="text-xl font-bold text-on-surface font-headline-md">
            Selamat datang, {{ auth('admin')->user()->nama }}.
        </h2>
        <p class="mt-2 text-sm text-on-surface-variant">Ringkasan kondisi kependudukan {{ $desa->nama_desa }}.</p>
    </div>

    {{-- ------------------------ Kartu ringkasan ------------------------ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Card 1: Total Warga -->
        <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] transition-transform hover:-translate-y-1 duration-300">
            <div class="flex justify-between items-start mb-4">
                <span class="font-label-sm text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Total Warga</span>
                <span class="material-symbols-outlined text-secondary bg-secondary/10 p-1.5 rounded-full text-[20px]">person</span>
            </div>
            <div class="font-headline-lg text-4xl font-bold text-on-surface mb-2">
                {{ number_format($totalWarga, 0, ',', '.') }}
            </div>
            <div class="font-label-sm text-xs text-on-surface-variant flex items-center">
                warga aktif saat ini
            </div>
        </div>

        <!-- Card 2: Jumlah KK -->
        <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] transition-transform hover:-translate-y-1 duration-300">
            <div class="flex justify-between items-start mb-4">
                <span class="font-label-sm text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Jumlah KK</span>
                <span class="material-symbols-outlined text-tertiary bg-tertiary/10 p-1.5 rounded-full text-[20px]">family_restroom</span>
            </div>
            
            @if ($kk->adaData())
                <div class="font-headline-lg text-4xl font-bold text-on-surface mb-2">
                    {{ number_format($kk->total(), 0, ',', '.') }}
                </div>
                <div class="font-label-sm text-xs text-on-surface-variant flex items-center justify-between">
                    <span>Data tahun {{ $kk->tahunAcuan() }}</span>
                    @if ($kk->rataJiwaPerKk() !== null)
                        <span class="font-medium text-primary">{{ number_format($kk->rataJiwaPerKk(), 2, ',', '.') }} jiwa/KK</span>
                    @endif
                </div>
            @else
                <div class="font-headline-lg text-4xl font-bold text-outline-variant mb-2">—</div>
                <div class="font-label-sm text-xs text-on-surface-variant flex items-center">
                    <a href="{{ route('admin.rekap-kk.index') }}" class="text-primary underline hover:text-primary-container">Isi rekap KK</a>
                </div>
            @endif
        </div>

        <!-- Card 3: Kelahiran -->
        <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] transition-transform hover:-translate-y-1 duration-300">
            <div class="flex justify-between items-start mb-4">
                <span class="font-label-sm text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Kelahiran</span>
                <span class="material-symbols-outlined text-primary-container bg-primary-container/10 p-1.5 rounded-full text-[20px]">child_care</span>
            </div>
            <div class="font-headline-lg text-4xl font-bold text-on-surface mb-2">
                {{ number_format($kelahiran, 0, ',', '.') }}
            </div>
            <div class="font-label-sm text-xs text-on-surface-variant flex items-center">
                <span>Tahun {{ $tahun }}</span>
            </div>
        </div>

        <!-- Card 4: Kematian -->
        <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] transition-transform hover:-translate-y-1 duration-300">
            <div class="flex justify-between items-start mb-4">
                <span class="font-label-sm text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Kematian</span>
                <span class="material-symbols-outlined text-error bg-error-container p-1.5 rounded-full text-[20px]">church</span>
            </div>
            <div class="font-headline-lg text-4xl font-bold text-on-surface mb-2">
                {{ number_format($kematian, 0, ',', '.') }}
            </div>
            <div class="font-label-sm text-xs text-on-surface-variant flex items-center">
                <span>Tahun {{ $tahun }}</span>
            </div>
        </div>
    </div>

    {{-- ---------------------------- Grafik ---------------------------- --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin.kartu-grafik judul="Sebaran Umur" kanvas="grafikUmur"
                                  keterangan="Dihitung dari tanggal lahir terhadap hari ini, jadi ikut bergeser sendiri seiring waktu." />
        </div>
        <div>
            <x-admin.kartu-grafik judul="Rasio Gender" kanvas="grafikGender" />
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-admin.kartu-grafik judul="Mata Pencaharian" kanvas="grafikPekerjaan" tinggi="h-96"
                              keterangan="Sepuluh terbanyak; sisanya digabung sebagai Lainnya." />

        <x-admin.kartu-grafik judul="Tingkat Pendidikan" kanvas="grafikPendidikan" tinggi="h-96" />
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] overflow-hidden">
        <x-admin.kartu-grafik judul="Tren Kelahiran & Kematian" kanvas="grafikTren" tinggi="h-80" class="border-none shadow-none"
                              keterangan="Perpindahan penduduk ikut ditampilkan agar pertumbuhan desa terbaca utuh.">
            <x-slot:aksi>
                <select id="pilihTahunTren"
                        class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-1.5 text-xs shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none text-on-surface">
                    <option value="5">5 tahun terakhir</option>
                    <option value="10">10 tahun terakhir</option>
                    <option value="15">15 tahun terakhir</option>
                </select>
            </x-slot:aksi>
        </x-admin.kartu-grafik>
    </div>

    {{-- ----------------------- Sebaran wilayah ----------------------- --}}
    <div class="rounded-xl bg-surface-container-lowest shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-outline-variant overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-outline-variant px-6 py-5 bg-surface-container-lowest">
            <div>
                <h3 class="font-headline-md text-lg font-bold text-on-surface">Sebaran Per Wilayah</h3>
                <p class="mt-1 text-xs text-on-surface-variant">
                    Wilayah yang belum punya warga tetap tampil dengan angka nol.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <select id="pilihTingkatWilayah"
                        class="rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-1.5 text-xs shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none text-on-surface">
                    <option value="dusun">Per Dusun</option>
                    <option value="rw">Per RW</option>
                    <option value="rt">Per RT</option>
                </select>
                <button type="button" id="urutkanWilayah" data-urutan="desc"
                        class="rounded-lg border border-outline-variant px-3 py-1.5 text-xs font-medium text-on-surface transition hover:bg-surface-container-low shadow-sm">
                    Terbanyak &darr;
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-container-low text-left text-xs tracking-wider text-on-surface-variant uppercase border-b border-outline-variant">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Wilayah</th>
                        <th class="px-6 py-4 text-right font-semibold">Warga</th>
                        <th class="px-6 py-4 text-right font-semibold">KK</th>
                        <th class="px-6 py-4 text-right font-semibold">Jiwa/KK</th>
                    </tr>
                </thead>
                <tbody id="tabelWilayah" class="divide-y divide-outline-variant">
                    <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-on-surface-variant font-medium">Memuat…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('skrip')
@vite('resources/js/grafik.js')

<script type="module">
    const angka = (n) => new Intl.NumberFormat('id-ID').format(n);
    const G = window.Grafik;

    G.muat('grafikUmur', @js(route('admin.statistik.umur')), G.sebaranUmur);
    G.muat('grafikGender', @js(route('admin.statistik.gender')), G.rasioGender);
    G.muat('grafikPekerjaan', @js(route('admin.statistik.pekerjaan')), G.mataPencaharian);
    G.muat('grafikPendidikan', @js(route('admin.statistik.pendidikan')), G.pendidikan);

    // --- Tren, dengan pemilih rentang tahun ---
    const muatTren = (tahun) =>
        G.muat('grafikTren', `{{ route('admin.statistik.tren') }}?tahun=${tahun}`, G.trenMutasi);

    muatTren(5);
    document.getElementById('pilihTahunTren')?.addEventListener('change', (e) => muatTren(e.target.value));

    // --- Tabel sebaran wilayah ---
    const tbody = document.getElementById('tabelWilayah');
    const pilihTingkat = document.getElementById('pilihTingkatWilayah');
    const tombolUrut = document.getElementById('urutkanWilayah');

    async function muatWilayah() {
        const tingkat = pilihTingkat.value;
        const urutan = tombolUrut.dataset.urutan;

        tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-sm text-on-surface-variant font-medium">Memuat…</td></tr>';

        try {
            const respons = await fetch(
                `{{ route('admin.statistik.wilayah') }}?tingkat=${tingkat}&urutan=${urutan}`,
                { headers: { Accept: 'application/json' } },
            );
            if (!respons.ok) throw new Error(`HTTP ${respons.status}`);

            const { data } = await respons.json();

            if (!data.length) {
                tbody.innerHTML =
                    '<tr><td colspan="4" class="px-6 py-8 text-center text-sm text-on-surface-variant font-medium">Belum ada wilayah terdaftar.</td></tr>';
                return;
            }

            tbody.innerHTML = data.map((b) => `
                <tr class="hover:bg-surface-container-low transition-colors duration-150">
                    <td class="px-6 py-4 font-bold text-on-surface">${b.label}</td>
                    <td class="px-6 py-4 text-right tabular-nums text-slate-700 font-medium">${angka(b.jumlah_warga)}</td>
                    <td class="px-6 py-4 text-right tabular-nums ${b.jumlah_kk ? 'text-slate-700 font-medium' : 'text-outline-variant'}">${b.jumlah_kk ? angka(b.jumlah_kk) : '—'}</td>
                    <td class="px-6 py-4 text-right tabular-nums ${b.jiwa_per_kk ? 'text-slate-700 font-medium' : 'text-outline-variant'}">${b.jiwa_per_kk ? b.jiwa_per_kk.toFixed(2).replace('.', ',') : '—'}</td>
                </tr>
            `).join('');
        } catch (e) {
            console.error('Gagal memuat sebaran wilayah:', e);
            tbody.innerHTML =
                '<tr><td colspan="4" class="px-6 py-8 text-center text-sm text-error font-medium">Gagal memuat data wilayah.</td></tr>';
        }
    }

    pilihTingkat.addEventListener('change', muatWilayah);
    tombolUrut.addEventListener('click', () => {
        const naik = tombolUrut.dataset.urutan === 'asc';
        tombolUrut.dataset.urutan = naik ? 'desc' : 'asc';
        tombolUrut.innerHTML = naik ? 'Terbanyak &darr;' : 'Tersedikit &uarr;';
        muatWilayah();
    });

    muatWilayah();
</script>
@endpush
@endsection
