@extends('publik.layouts.app')

@section('judul', 'Prestasi Desa')
@section('deskripsi', 'Penghargaan, program unggulan, dan inovasi yang diraih ' . $desa->nama_desa . '.')

@section('konten')
<div class="mx-auto max-w-4xl px-6 md:px-12 py-12 md:py-16">

    <nav class="mb-6 text-sm text-on-surface-variant">
        <a href="{{ route('beranda') }}" class="hover:text-primary transition-colors">Beranda</a>
        <span class="mx-1.5">/</span>
        <span class="text-on-surface">Prestasi Desa</span>
    </nav>

    <header class="mb-8">
        <h1 class="text-3xl font-semibold text-on-surface">Prestasi Desa</h1>
        <p class="mt-2 max-w-2xl text-on-surface-variant">
            Penghargaan, program unggulan, dan inovasi yang diraih {{ $desa->nama_desa }}.
        </p>
    </header>

    {{-- Ringkasan per tingkat --}}
    @if ($jumlahPerTingkat->isNotEmpty())
        <div class="mb-10 flex flex-wrap gap-2">
            <a href="{{ route('publik.prestasi') }}"
               class="rounded-full px-4 py-2 text-sm font-medium transition
                      {{ $tingkat === null ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface hover:bg-surface-container-low' }}">
                Semua
            </a>
            @foreach (\App\Enums\TingkatPrestasi::cases() as $t)
                @if ($jumlahPerTingkat->has($t->value))
                    <a href="{{ route('publik.prestasi', ['tingkat' => $t->value]) }}"
                       class="rounded-full px-4 py-2 text-sm font-medium transition
                              {{ $tingkat === $t ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface hover:bg-surface-container-low' }}">
                        {{ $t->label() }}
                        <span class="ml-1 opacity-60">{{ $jumlahPerTingkat[$t->value] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    @if ($daftar->isEmpty())
        <div class="rounded-[12px] border border-dashed border-outline-variant/50 px-6 py-20 text-center">
            <p class="font-medium text-on-surface">
                {{ $tingkat ? 'Belum ada prestasi pada tingkat ini' : 'Data prestasi belum tersedia' }}
            </p>
            <p class="mx-auto mt-1 max-w-md text-sm text-on-surface-variant">
                Penghargaan dan capaian desa akan ditampilkan di sini setelah dilengkapi aparat desa.
            </p>
        </div>
    @else
        {{-- Lini masa: capaian terbaru lebih dulu --}}
        <ol class="relative space-y-8 border-l-2 border-outline-variant/30 pl-6">
            @foreach ($daftar as $prestasi)
                <li class="relative">
                    <span class="absolute top-1.5 -left-[1.9rem] h-3.5 w-3.5 rounded-full border-2 border-white bg-primary-container0"></span>

                    <article class="overflow-hidden rounded-[12px] border border-outline-variant/30">
                        <div class="flex flex-col sm:flex-row">
                            @if (filled($prestasi->foto))
                                <div class="shrink-0 bg-surface-container-lowest sm:w-48">
                                    <img src="{{ $prestasi->urlFotoKecil() }}" alt="{{ $prestasi->judul }}"
                                         loading="lazy" class="h-full max-h-44 w-full object-cover">
                                </div>
                            @endif

                            <div class="min-w-0 flex-1 p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-on-surface tabular-nums">{{ $prestasi->tahun }}</span>
                                    @if ($prestasi->tingkat)
                                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $prestasi->tingkat->warna() }}">
                                            {{ $prestasi->tingkat->label() }}
                                        </span>
                                    @endif
                                </div>

                                <h2 class="mt-1.5 font-semibold text-on-surface">{{ $prestasi->judul }}</h2>

                                @if (filled($prestasi->penyelenggara))
                                    <p class="mt-0.5 text-sm text-on-surface-variant">oleh {{ $prestasi->penyelenggara }}</p>
                                @endif

                                @if (filled($prestasi->deskripsi))
                                    <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">{{ $prestasi->deskripsi }}</p>
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
