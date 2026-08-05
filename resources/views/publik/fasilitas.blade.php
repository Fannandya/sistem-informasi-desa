@extends('publik.layouts.app')

@section('judul', 'Fasilitas Umum')
@section('deskripsi', 'Sarana pendidikan, kesehatan, ibadah, dan olahraga yang tersedia di ' . $desa->nama_desa . '.')

@section('konten')
<div class="mx-auto max-w-7xl px-6 md:px-12 py-12 md:py-16">

    <nav class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('beranda') }}" class="text-on-surface-variant hover:text-primary transition-colors">Beranda</a>
        <span class="mx-1.5">/</span>
        <span class="text-on-surface">Fasilitas Umum</span>
    </nav>

    <header class="mb-8 max-w-2xl">
        <h1 class="text-3xl font-semibold text-on-surface">Fasilitas Umum</h1>
        <p class="mt-2 text-on-surface-variant">
            Sarana yang tersedia di {{ $desa->nama_desa }} dan dapat dimanfaatkan warga.
        </p>
    </header>

    @if ($jumlahPerKategori->isNotEmpty())
        <div class="mb-10 flex flex-wrap gap-2">
            <a href="{{ route('publik.fasilitas') }}"
               class="rounded-full px-4 py-2 text-sm font-medium transition
                      {{ $kategoriId === null ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface border border-outline-variant/30 hover:border-primary/50' }}">
                Semua
            </a>
            @foreach ($kategoriFilter as $k)
                @if ($jumlahPerKategori->has($k->nama))
                    <a href="{{ route('publik.fasilitas', ['kategori' => $k->id]) }}"
                       class="rounded-full px-4 py-2 text-sm font-medium transition
                              {{ $kategoriId === $k->id ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface border border-outline-variant/30 hover:border-primary/50' }}">
                        {{ $k->nama }}
                        <span class="ml-1 opacity-60">{{ $jumlahPerKategori[$k->nama] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    @forelse ($kelompok as $kategoriNama => $items)
        <section class="mb-12">
            <div class="mb-5 flex items-baseline gap-3">
                <h2 class="text-xl font-semibold text-on-surface">{{ $kategoriNama }}</h2>
                <span class="text-sm text-slate-400">{{ $items->count() }} tempat</span>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $fasilitas)
                    <article class="overflow-hidden rounded-[12px] border border-outline-variant/30">
                        <div class="aspect-[16/10] overflow-hidden bg-surface-container-highest">
                            <img src="{{ $fasilitas->urlFotoKecil() }}" alt="{{ $fasilitas->nama }}"
                                 loading="lazy" class="h-full w-full object-cover">
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-on-surface">{{ $fasilitas->nama }}</h3>
                            @if ($fasilitas->lokasiTerbaca())
                                <p class="mt-1 text-xs text-on-surface-variant">{{ $fasilitas->lokasiTerbaca() }}</p>
                            @endif
                            @if (filled($fasilitas->deskripsi))
                                <p class="mt-2 line-clamp-3 text-sm text-on-surface-variant">{{ $fasilitas->deskripsi }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @empty
    @endforelse

    @if ($kelompok->isEmpty())
        <div class="rounded-[12px] border border-dashed border-outline-variant/50 px-6 py-20 text-center">
            <p class="font-medium text-on-surface">
                {{ $kategoriId ? 'Belum ada fasilitas pada kategori ini' : 'Data fasilitas belum tersedia' }}
            </p>
            <p class="mx-auto mt-1 max-w-md text-sm text-on-surface-variant">
                Daftar sarana desa akan ditampilkan di sini setelah dilengkapi aparat desa.
            </p>
        </div>
    @endif
</div>
@endsection
