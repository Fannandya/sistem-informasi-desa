@extends('publik.layouts.app')

@section('judul', 'Fasilitas Umum')
@section('deskripsi', 'Sarana pendidikan, kesehatan, ibadah, dan olahraga yang tersedia di ' . $desa->nama_desa . '.')

@section('konten')
<div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">

    <nav class="mb-6 text-sm text-slate-500">
        <a href="{{ route('beranda') }}" class="hover:text-emerald-700">Beranda</a>
        <span class="mx-1.5">/</span>
        <span class="text-slate-700">Fasilitas Umum</span>
    </nav>

    <header class="mb-8 max-w-2xl">
        <h1 class="text-3xl font-semibold text-slate-900">Fasilitas Umum</h1>
        <p class="mt-2 text-slate-600">
            Sarana yang tersedia di {{ $desa->nama_desa }} dan dapat dimanfaatkan warga.
        </p>
    </header>

    @if ($jumlahPerKategori->isNotEmpty())
        <div class="mb-10 flex flex-wrap gap-2">
            <a href="{{ route('publik.fasilitas') }}"
               class="rounded-full px-4 py-2 text-sm font-medium transition
                      {{ $kategori === null ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Semua
            </a>
            @foreach (\App\Enums\KategoriFasilitas::cases() as $k)
                @if ($jumlahPerKategori->has($k->value))
                    <a href="{{ route('publik.fasilitas', ['kategori' => $k->value]) }}"
                       class="rounded-full px-4 py-2 text-sm font-medium transition
                              {{ $kategori === $k ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        {{ $k->label() }}
                        <span class="ml-1 opacity-60">{{ $jumlahPerKategori[$k->value] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    @forelse (\App\Enums\KategoriFasilitas::cases() as $k)
        @continue (! $kelompok->has($k->value))

        <section class="mb-12">
            <div class="mb-5 flex items-baseline gap-3">
                <h2 class="text-xl font-semibold text-slate-900">{{ $k->label() }}</h2>
                <span class="text-sm text-slate-400">{{ $kelompok[$k->value]->count() }} tempat</span>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($kelompok[$k->value] as $fasilitas)
                    <article class="overflow-hidden rounded-2xl border border-slate-200">
                        <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                            <img src="{{ $fasilitas->urlFotoKecil() }}" alt="{{ $fasilitas->nama }}"
                                 loading="lazy" class="h-full w-full object-cover">
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-slate-900">{{ $fasilitas->nama }}</h3>
                            @if ($fasilitas->lokasiTerbaca())
                                <p class="mt-1 text-xs text-slate-500">{{ $fasilitas->lokasiTerbaca() }}</p>
                            @endif
                            @if (filled($fasilitas->deskripsi))
                                <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $fasilitas->deskripsi }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @empty
    @endforelse

    @if ($kelompok->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 px-6 py-20 text-center">
            <p class="font-medium text-slate-900">
                {{ $kategori ? 'Belum ada fasilitas pada kategori ini' : 'Data fasilitas belum tersedia' }}
            </p>
            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                Daftar sarana desa akan ditampilkan di sini setelah dilengkapi aparat desa.
            </p>
        </div>
    @endif
</div>
@endsection
