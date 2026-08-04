@extends('admin.layouts.app')

@section('judul', 'Dashboard')

@section('konten')
<div class="space-y-6">

    <div>
        <h2 class="text-lg font-semibold text-slate-900">
            Selamat datang, {{ auth('admin')->user()->nama }}.
        </h2>
        <p class="mt-1 text-sm text-slate-600">Ringkasan kondisi kependudukan {{ $desa->nama_desa }}.</p>
    </div>

    {{-- ------------------------ Kartu ringkasan ------------------------ --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-slate-500">Total Warga</p>
            <p class="mt-1 text-3xl font-semibold text-slate-900">
                {{ number_format($totalWarga, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">warga aktif saat ini</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-slate-500">Jumlah KK</p>

            @if ($kk->adaData())
                <p class="mt-1 text-3xl font-semibold text-slate-900">
                    {{ number_format($kk->total(), 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-slate-400">data tahun {{ $kk->tahunAcuan() }}</p>

                @if (! $kk->lengkap())
                    {{-- PRD 2.9: penanda ini hanya di panel admin, tidak publik. --}}
                    <a href="{{ route('admin.rekap-kk.index', ['tahun' => $kk->tahunAcuan()]) }}"
                       class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1
                              text-xs font-medium text-amber-800 transition hover:bg-amber-200">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        {{ $kk->jumlahBelumDiisi() }} RT belum diisi
                    </a>
                @endif
            @else
                <p class="mt-1 text-3xl font-semibold text-slate-300">—</p>
                <a href="{{ route('admin.rekap-kk.index') }}"
                   class="mt-1 inline-block text-xs text-emerald-700 underline hover:text-emerald-800">
                    Isi rekap KK
                </a>
            @endif
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-slate-500">Kelahiran</p>
            <p class="mt-1 text-3xl font-semibold text-slate-900">
                {{ number_format($kelahiran, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">tahun {{ $tahun }}</p>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-slate-500">Kematian</p>
            <p class="mt-1 text-3xl font-semibold text-slate-900">
                {{ number_format($kematian, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-400">tahun {{ $tahun }}</p>
        </div>
    </div>

    @if ($kk->rataJiwaPerKk() !== null)
        <p class="text-sm text-slate-600">
            Rata-rata <span class="font-semibold text-slate-900">{{ number_format($kk->rataJiwaPerKk(), 2, ',', '.') }}</span>
            jiwa per keluarga.
        </p>
    @endif

    {{-- ---------------------------- Grafik ---------------------------- --}}
    <div class="grid gap-5 lg:grid-cols-3">
        <x-admin.kartu-grafik judul="Sebaran Umur" kanvas="grafikUmur" class="lg:col-span-2"
                              keterangan="Dihitung dari tanggal lahir terhadap hari ini, jadi ikut bergeser sendiri seiring waktu." />

        <x-admin.kartu-grafik judul="Rasio Gender" kanvas="grafikGender" />
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <x-admin.kartu-grafik judul="Mata Pencaharian" kanvas="grafikPekerjaan" tinggi="h-96"
                              keterangan="Sepuluh terbanyak; sisanya digabung sebagai Lainnya." />

        <x-admin.kartu-grafik judul="Tingkat Pendidikan" kanvas="grafikPendidikan" tinggi="h-96" />
    </div>

    <x-admin.kartu-grafik judul="Tren Kelahiran & Kematian" kanvas="grafikTren" tinggi="h-80"
                          keterangan="Perpindahan penduduk ikut ditampilkan agar pertumbuhan desa terbaca utuh.">
        <x-slot:aksi>
            <select id="pilihTahunTren"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs shadow-sm
                           focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                <option value="5">5 tahun terakhir</option>
                <option value="10">10 tahun terakhir</option>
                <option value="15">15 tahun terakhir</option>
            </select>
        </x-slot:aksi>
    </x-admin.kartu-grafik>

    {{-- ----------------------- Sebaran wilayah ----------------------- --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Sebaran Per Wilayah</h2>
                <p class="mt-0.5 text-xs text-slate-500">
                    Wilayah yang belum punya warga tetap tampil dengan angka nol.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <select id="pilihTingkatWilayah"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs shadow-sm
                               focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                    <option value="dusun">Per Dusun</option>
                    <option value="rw">Per RW</option>
                    <option value="rt">Per RT</option>
                </select>
                <button type="button" id="urutkanWilayah" data-urutan="desc"
                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700
                               transition hover:bg-slate-50">
                    Terbanyak &darr;
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs tracking-wider text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Wilayah</th>
                        <th class="px-5 py-3 text-right font-semibold">Warga</th>
                        <th class="px-5 py-3 text-right font-semibold">KK</th>
                        <th class="px-5 py-3 text-right font-semibold">Jiwa/KK</th>
                    </tr>
                </thead>
                <tbody id="tabelWilayah" class="divide-y divide-slate-100">
                    <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-slate-400">Memuat…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('skrip')
@vite('resources/js/grafik.js')

{{-- type="module" disengaja: @vite menghasilkan modul yang tertunda, dan
     modul dieksekusi berurutan sesuai posisinya. Skrip biasa akan jalan lebih
     dulu, saat window.Grafik belum sempat terdefinisi. --}}
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

        tbody.innerHTML = '<tr><td colspan="4" class="px-5 py-8 text-center text-sm text-slate-400">Memuat…</td></tr>';

        try {
            const respons = await fetch(
                `{{ route('admin.statistik.wilayah') }}?tingkat=${tingkat}&urutan=${urutan}`,
                { headers: { Accept: 'application/json' } },
            );
            if (!respons.ok) throw new Error(`HTTP ${respons.status}`);

            const { data } = await respons.json();

            if (!data.length) {
                tbody.innerHTML =
                    '<tr><td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada wilayah terdaftar.</td></tr>';
                return;
            }

            tbody.innerHTML = data.map((b) => `
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-900">${b.label}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-slate-700">${angka(b.jumlah_warga)}</td>
                    <td class="px-5 py-3 text-right tabular-nums ${b.jumlah_kk ? 'text-slate-700' : 'text-slate-300'}">${b.jumlah_kk ? angka(b.jumlah_kk) : '—'}</td>
                    <td class="px-5 py-3 text-right tabular-nums ${b.jiwa_per_kk ? 'text-slate-700' : 'text-slate-300'}">${b.jiwa_per_kk ? b.jiwa_per_kk.toFixed(2).replace('.', ',') : '—'}</td>
                </tr>
            `).join('');
        } catch (e) {
            console.error('Gagal memuat sebaran wilayah:', e);
            tbody.innerHTML =
                '<tr><td colspan="4" class="px-5 py-8 text-center text-sm text-amber-700">Gagal memuat data wilayah.</td></tr>';
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
