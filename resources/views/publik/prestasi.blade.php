@extends('publik.layouts.app')

@section('judul', 'Prestasi Desa')
@section('deskripsi', 'Penghargaan, program unggulan, dan inovasi yang diraih ' . $desa->nama_desa . '.')

@section('konten')
<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6">

    <nav class="mb-6 text-sm text-slate-500">
        <a href="{{ route('beranda') }}" class="hover:text-emerald-700">Beranda</a>
        <span class="mx-1.5">/</span>
        <span class="text-slate-700">Prestasi Desa</span>
    </nav>

    <header class="mb-8">
        <h1 class="text-3xl font-semibold text-slate-900">Prestasi Desa</h1>
        <p class="mt-2 max-w-2xl text-slate-600">
            Penghargaan, program unggulan, dan inovasi yang diraih {{ $desa->nama_desa }}.
        </p>
    </header>

    {{-- Ringkasan per tingkat --}}
    @if ($jumlahPerTingkat->isNotEmpty())
        <div class="mb-10 flex flex-wrap gap-2">
            <a href="{{ route('publik.prestasi') }}"
               class="rounded-full px-4 py-2 text-sm font-medium transition
                      {{ $tingkat === null ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Semua
            </a>
            @foreach (\App\Enums\TingkatPrestasi::cases() as $t)
                @if ($jumlahPerTingkat->has($t->value))
                    <a href="{{ route('publik.prestasi', ['tingkat' => $t->value]) }}"
                       class="rounded-full px-4 py-2 text-sm font-medium transition
                              {{ $tingkat === $t ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        {{ $t->label() }}
                        <span class="ml-1 opacity-60">{{ $jumlahPerTingkat[$t->value] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    @if ($daftar->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 px-6 py-20 text-center">
            <p class="font-medium text-slate-900">
                {{ $tingkat ? 'Belum ada prestasi pada tingkat ini' : 'Data prestasi belum tersedia' }}
            </p>
            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                Penghargaan dan capaian desa akan ditampilkan di sini setelah dilengkapi aparat desa.
            </p>
        </div>
    @else
        {{-- Lini masa: capaian terbaru lebih dulu --}}
        <ol class="relative space-y-8 border-l-2 border-slate-200 pl-6">
            @foreach ($daftar as $prestasi)
                <li class="relative">
                    <span class="absolute top-1.5 -left-[1.9rem] h-3.5 w-3.5 rounded-full border-2 border-white bg-emerald-500"></span>

                    <article class="overflow-hidden rounded-2xl border border-slate-200">
                        <div class="flex flex-col sm:flex-row">
                            @if (filled($prestasi->foto))
                                <div class="shrink-0 bg-slate-100 sm:w-48">
                                    <img src="{{ $prestasi->urlFotoKecil() }}" alt="{{ $prestasi->judul }}"
                                         loading="lazy" class="h-full max-h-44 w-full object-cover">
                                </div>
                            @endif

                            <div class="min-w-0 flex-1 p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-slate-900 tabular-nums">{{ $prestasi->tahun }}</span>
                                    @if ($prestasi->tingkat)
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $prestasi->tingkat->warna() }}">
                                            {{ $prestasi->tingkat->label() }}
                                        </span>
                                    @endif
                                </div>

                                <h2 class="mt-1.5 font-semibold text-slate-900">{{ $prestasi->judul }}</h2>

                                @if (filled($prestasi->penyelenggara))
                                    <p class="mt-0.5 text-sm text-slate-500">oleh {{ $prestasi->penyelenggara }}</p>
                                @endif

                                @if (filled($prestasi->deskripsi))
                                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $prestasi->deskripsi }}</p>
                                @endif
                            </div>
                        </div>
                    </article>
                </li>
            @endforeach
        </ol>
    @endif
</div>
@endsection
