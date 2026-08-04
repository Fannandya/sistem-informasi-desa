@extends('publik.layouts.app')

@section('judul', 'Potensi Desa')
@section('deskripsi', 'UMKM, hasil bumi, wisata, dan kerajinan ' . $desa->nama_desa . '.')

@section('konten')
<div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">

    <nav class="mb-6 text-sm text-slate-500">
        <a href="{{ route('beranda') }}" class="hover:text-emerald-700">Beranda</a>
        <span class="mx-1.5">/</span>
        <span class="text-slate-700">Potensi Desa</span>
    </nav>

    <header class="mb-8 max-w-2xl">
        <h1 class="text-3xl font-semibold text-slate-900">Potensi Desa</h1>
        <p class="mt-2 text-slate-600">
            UMKM, hasil bumi, wisata, dan kerajinan yang dimiliki {{ $desa->nama_desa }}.
        </p>
    </header>

    {{-- Saringan kategori --}}
    @if ($jumlahPerKategori->isNotEmpty())
        <div class="mb-8 flex flex-wrap gap-2">
            <a href="{{ route('publik.potensi.index') }}"
               class="rounded-full px-4 py-2 text-sm font-medium transition
                      {{ $kategori === null ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Semua
            </a>
            @foreach (\App\Enums\KategoriPotensi::cases() as $k)
                @if ($jumlahPerKategori->has($k->value))
                    <a href="{{ route('publik.potensi.index', ['kategori' => $k->value]) }}"
                       class="rounded-full px-4 py-2 text-sm font-medium transition
                              {{ $kategori === $k ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        {{ $k->label() }}
                        <span class="ml-1 opacity-60">{{ $jumlahPerKategori[$k->value] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    @if ($daftar->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 px-6 py-20 text-center">
            <p class="font-medium text-slate-900">
                {{ $kategori ? 'Belum ada potensi pada kategori ini' : 'Data potensi belum tersedia' }}
            </p>
            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                {{ $kategori
                    ? 'Coba lihat kategori lain.'
                    : 'Potensi desa akan ditampilkan di sini setelah dilengkapi aparat desa.' }}
            </p>
            @if ($kategori)
                <a href="{{ route('publik.potensi.index') }}"
                   class="mt-5 inline-block rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white">
                    Lihat semua potensi
                </a>
            @endif
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($daftar as $potensi)
                <a href="{{ route('publik.potensi.show', $potensi) }}"
                   class="group overflow-hidden rounded-2xl border border-slate-200 transition hover:border-slate-300 hover:shadow-md">
                    <div class="aspect-[4/3] overflow-hidden bg-slate-100">
                        <img src="{{ $potensi->urlFotoKecil() }}" alt="{{ $potensi->nama }}" loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                    </div>
                    <div class="p-5">
                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $potensi->kategori->warna() }}">
                            {{ $potensi->kategori->label() }}
                        </span>
                        <h2 class="mt-3 font-semibold text-slate-900 group-hover:text-emerald-700">
                            {{ $potensi->nama }}
                        </h2>
                        @if ($potensi->ringkasan())
                            <p class="mt-1.5 line-clamp-3 text-sm text-slate-600">{{ $potensi->ringkasan() }}</p>
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
