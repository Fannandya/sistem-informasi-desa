@extends('publik.layouts.app')

@section('judul', 'Potensi Desa')
@section('deskripsi', 'UMKM, hasil bumi, wisata, dan kerajinan ' . $desa->nama_desa . '.')

@section('konten')
<div class="mx-auto max-w-7xl px-6 md:px-12 py-12 md:py-16">

    <nav class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('beranda') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="mx-1.5">/</span>
        <span class="text-on-surface">Potensi Desa</span>
    </nav>

    <header class="mb-8 max-w-2xl">
        <h1 class="text-3xl font-semibold text-on-surface">Potensi Desa</h1>
        <p class="mt-2 text-on-surface-variant">
            UMKM, hasil bumi, wisata, dan kerajinan yang dimiliki {{ $desa->nama_desa }}.
        </p>
    </header>

    {{-- Saringan kategori --}}
    @if ($jumlahPerKategori->isNotEmpty())
        <div class="mb-8 flex flex-wrap gap-2">
            <a href="{{ route('publik.potensi.index') }}"
               class="rounded-full px-4 py-2 text-sm font-medium transition
                      {{ $kategoriId === null ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface hover:bg-surface-container-low' }}">
                Semua
            </a>
            @foreach ($kategoriFilter as $k)
                @if ($jumlahPerKategori->has($k->nama))
                    <a href="{{ route('publik.potensi.index', ['kategori' => $k->id]) }}"
                       class="rounded-full px-4 py-2 text-sm font-medium transition
                              {{ $kategoriId === $k->id ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface hover:bg-surface-container-low' }}">
                        {{ $k->nama }}
                        <span class="ml-1 opacity-60">{{ $jumlahPerKategori[$k->nama] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    @if ($daftar->isEmpty())
        <div class="rounded-[12px] border border-dashed border-outline-variant/50 px-6 py-20 text-center">
            <p class="font-medium text-on-surface">
                {{ $kategoriId ? 'Belum ada potensi pada kategori ini' : 'Data potensi belum tersedia' }}
            </p>
            <p class="mx-auto mt-1 max-w-md text-sm text-on-surface-variant">
                {{ $kategoriId
                    ? 'Coba lihat kategori lain.'
                    : 'Potensi desa akan ditampilkan di sini setelah dilengkapi aparat desa.' }}
            </p>
            @if ($kategoriId)
                <a href="{{ route('publik.potensi.index') }}"
                   class="mt-5 inline-block rounded-[12px] bg-slate-900 px-4 py-2.5 text-sm font-medium text-white">
                    Lihat semua potensi
                </a>
            @endif
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($daftar as $potensi)
                <a href="{{ route('publik.potensi.show', $potensi) }}"
                   class="group overflow-hidden rounded-[12px] border border-outline-variant/30 bg-surface-container-lowest shadow-[0px_4px_20px_rgba(0,0,0,0.03)] transition hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] hover:-translate-y-1">
                    <div class="aspect-[4/3] overflow-hidden bg-surface-container-highest">
                        <img src="{{ $potensi->urlFotoKecil() }}" alt="{{ $potensi->nama }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    </div>
                    <div class="p-5">
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium bg-primary-container text-on-primary-container">
                            {{ $potensi->kategori?->nama ?? 'Lainnya' }}
                        </span>
                        <h2 class="mt-3 font-semibold text-on-surface group-hover:text-primary transition-colors">
                            {{ $potensi->nama }}
                        </h2>
                        @if ($potensi->ringkasan())
                            <p class="mt-1.5 line-clamp-3 text-sm text-on-surface-variant">{{ $potensi->ringkasan() }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        @if ($daftar->hasPages())
            <div class="mt-10">{{ $daftar->links() }}</div>
        @endif
    @endif
</div>
@endsection
