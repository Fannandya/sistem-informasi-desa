@extends('publik.layouts.app')

@section('judul', 'Profil Desa')
@section('deskripsi', $desa->deskripsiMeta())

@section('konten')
<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6">

    <nav class="mb-6 text-sm text-slate-500">
        <a href="{{ route('beranda') }}" class="hover:text-emerald-700">Beranda</a>
        <span class="mx-1.5">/</span>
        <span class="text-slate-700">Profil Desa</span>
    </nav>

    <header class="mb-10">
        <h1 class="text-3xl font-semibold text-slate-900">{{ $desa->nama_desa }}</h1>
        @if ($desa->wilayahLengkap())
            <p class="mt-1.5 text-slate-600">{{ $desa->wilayahLengkap() }}</p>
        @endif
    </header>

    {{-- Identitas --}}
    <dl class="mb-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            'Kecamatan' => $desa->kecamatan,
            'Kabupaten' => $desa->kabupaten,
            'Tahun Berdiri' => $desa->tahun_berdiri,
            'Luas Desa' => $desa->luas_desa ? number_format($desa->luas_desa, 2, ',', '.') . ' ha' : null,
        ] as $label => $nilai)
            @if (filled($nilai))
                <div class="rounded-2xl border border-slate-200 p-4">
                    <dt class="text-xs tracking-wider text-slate-500 uppercase">{{ $label }}</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $nilai }}</dd>
                </div>
            @endif
        @endforeach
    </dl>

    @if (filled($desa->visi))
        <section class="mb-10">
            <h2 class="mb-3 text-xl font-semibold text-slate-900">Visi</h2>
            <p class="leading-relaxed text-slate-700">{{ $desa->visi }}</p>
        </section>
    @endif

    @if ($desa->misiSebagaiDaftar())
        <section class="mb-10">
            <h2 class="mb-3 text-xl font-semibold text-slate-900">Misi</h2>
            <ol class="list-decimal space-y-2 pl-5 text-slate-700">
                @foreach ($desa->misiSebagaiDaftar() as $misi)
                    <li>{{ $misi }}</li>
                @endforeach
            </ol>
        </section>
    @endif

    @if (filled($desa->nilai_tujuan))
        <section class="mb-10">
            <h2 class="mb-3 text-xl font-semibold text-slate-900">Nilai & Tujuan Pembangunan</h2>
            <p class="leading-relaxed whitespace-pre-line text-slate-700">{{ $desa->nilai_tujuan }}</p>
        </section>
    @endif

    @if (filled($desa->sejarah))
        <section class="mb-10">
            <h2 class="mb-3 text-xl font-semibold text-slate-900">Sejarah Desa</h2>
            <p class="leading-relaxed whitespace-pre-line text-slate-700">{{ $desa->sejarah }}</p>
        </section>
    @endif

    @if (filled($desa->letak_geografis))
        <section class="mb-10">
            <h2 class="mb-3 text-xl font-semibold text-slate-900">Letak Geografis</h2>
            <p class="leading-relaxed whitespace-pre-line text-slate-700">{{ $desa->letak_geografis }}</p>
        </section>
    @endif

    @if (filled($desa->peta_embed))
        <section class="mb-10">
            <h2 class="mb-3 text-xl font-semibold text-slate-900">Peta Lokasi</h2>
            <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200">
                {{-- Hanya URL src yang disimpan; iframe dirender aplikasi sendiri (PRD fitur 03). --}}
                <iframe src="{{ $desa->peta_embed }}" class="h-96 w-full" style="border:0"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        title="Peta lokasi {{ $desa->nama_desa }}"></iframe>
            </div>
        </section>
    @endif

    @if (! filled($desa->visi) && ! filled($desa->sejarah) && ! $desa->misiSebagaiDaftar())
        <div class="rounded-2xl border border-dashed border-slate-300 px-6 py-16 text-center">
            <p class="font-medium text-slate-900">Profil desa belum dilengkapi</p>
            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                Visi, misi, dan sejarah desa akan ditampilkan di sini setelah diisi aparat desa.
            </p>
        </div>
    @endif
</div>
@endsection
