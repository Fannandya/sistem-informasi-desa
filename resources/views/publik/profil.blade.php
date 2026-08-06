@extends('publik.layouts.app')

@section('judul', 'Profil Desa')
@section('deskripsi', $desa->deskripsiMeta())

@section('konten')
<div class="bg-surface">
    {{-- Breadcrumb --}}
    <nav class="max-w-7xl mx-auto px-6 py-4 text-sm">
        <a href="{{ route('beranda') }}" class="text-on-surface-variant hover:text-primary transition-colors">Beranda</a>
        <span class="mx-2 text-on-surface-variant">/</span>
        <span class="text-on-surface font-medium">Profil Desa</span>
    </nav>

    {{-- Hero: Foto sampul + header --}}
    <div class="max-w-7xl mx-auto px-6 pt-4 pb-12 md:pb-16">
        <div class="grid gap-8 items-start md:grid-cols-2 lg:grid-cols-3">
            {{-- Foto sampul --}}
            <div class="lg:col-span-1 md:col-span-1">
                @if (filled($desa->foto_sampul))
                    <img src="{{ $desa->urlSampul() }}" alt="{{ $desa->nama_desa }}"
                         class="w-full aspect-square object-cover rounded-[12px] shadow-[0px_10px_30px_rgba(0,0,0,0.1)]">
                @else
                    <div class="w-full aspect-square rounded-[12px] bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-6xl opacity-50">landscape</span>
                    </div>
                @endif
            </div>

            {{-- Header info --}}
            <div class="lg:col-span-2 md:col-span-1">
                <h1 class="font-headline-lg text-4xl md:text-5xl font-bold text-primary mb-2">{{ $desa->nama_desa }}</h1>
                @if ($desa->wilayahLengkap())
                    <p class="font-body-md text-lg text-on-surface-variant mb-6">{{ $desa->wilayahLengkap() }}</p>
                @endif

                {{-- Grid identitas 2x2 --}}
                <div class="grid grid-cols-2 gap-4">
                    @php
                        $identitas = [
                            ['label' => 'Kecamatan', 'nilai' => $desa->kecamatan],
                            ['label' => 'Kabupaten', 'nilai' => $desa->kabupaten],
                            ['label' => 'Tahun Berdiri', 'nilai' => $desa->tahun_berdiri],
                            ['label' => 'Luas Desa', 'nilai' => filled($desa->luas_desa) ? number_format($desa->luas_desa, 1, ',', '.') . ' ha' : null],
                        ];
                    @endphp
                    @foreach ($identitas as $item)
                        @if (filled($item['nilai']))
                            <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-[12px] p-4 shadow-[0px_2px_8px_rgba(0,0,0,0.02)]">
                                <div class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ $item['label'] }}</div>
                                <div class="text-lg font-bold text-on-surface mt-2">{{ $item['nilai'] }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Content sections --}}
<div class="max-w-7xl mx-auto px-6 py-12 md:py-16 space-y-10 md:space-y-16">

    {{-- Visi --}}
    @if (filled($desa->visi))
        <section data-fade-in>
            <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-[12px] p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.02)]">
                <div class="text-xs font-semibold text-tertiary uppercase tracking-widest mb-3">Visi Desa</div>
                <p class="font-headline-lg text-2xl md:text-3xl italic font-normal text-on-surface leading-relaxed">
                    "{{ $desa->visi }}"
                </p>
            </div>
        </section>
    @endif

    {{-- Misi --}}
    @if ($desa->misiSebagaiDaftar())
        <section data-fade-in>
            <h2 class="font-headline-lg text-3xl font-bold text-primary mb-6">Misi Desa</h2>
            <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-[12px] p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.02)]">
                <ol class="list-decimal space-y-4 pl-6 text-on-surface-variant">
                    @foreach ($desa->misiSebagaiDaftar() as $misi)
                        <li class="font-body-md text-base leading-relaxed"><span class="text-on-surface font-medium">{{ $misi }}</span></li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- Nilai & Tujuan --}}
    @if (filled($desa->nilai_tujuan))
        <section data-fade-in>
            <h2 class="font-headline-lg text-3xl font-bold text-primary mb-6">Nilai & Tujuan Pembangunan</h2>
            <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-[12px] p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.02)]">
                <p class="font-body-md text-base text-on-surface-variant leading-relaxed whitespace-pre-line">{{ $desa->nilai_tujuan }}</p>
            </div>
        </section>
    @endif

    {{-- Sejarah --}}
    @if (filled($desa->sejarah))
        <section data-fade-in>
            <h2 class="font-headline-lg text-3xl font-bold text-primary mb-6">Sejarah Desa</h2>
            <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-[12px] p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.02)]">
                <div class="prose-desa font-body-md text-base text-on-surface-variant leading-relaxed whitespace-pre-line">{{ $desa->sejarah }}</div>
            </div>
        </section>
    @endif

    {{-- Letak Geografis --}}
    @if (filled($desa->letak_geografis))
        <section data-fade-in>
            <h2 class="font-headline-lg text-3xl font-bold text-primary mb-6">Letak Geografis</h2>
            <div class="bg-surface-container-lowest border border-outline-variant/30 rounded-[12px] p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.02)]">
                <p class="font-body-md text-base text-on-surface-variant leading-relaxed whitespace-pre-line">{{ $desa->letak_geografis }}</p>
            </div>
        </section>
    @endif

    {{-- Peta Lokasi --}}
    @if (filled($desa->peta_embed))
        <section data-fade-in>
            <h2 class="font-headline-lg text-3xl font-bold text-primary mb-6">Peta Lokasi</h2>
            <div class="overflow-hidden rounded-[12px] shadow-[0px_10px_30px_rgba(0,0,0,0.1)] border border-outline-variant/30">
                <iframe src="{{ $desa->peta_embed }}" class="w-full h-96" style="border:0"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        title="Peta lokasi {{ $desa->nama_desa }}"></iframe>
            </div>
        </section>
    @endif

    {{-- Empty state --}}
    @if (! filled($desa->visi) && ! filled($desa->sejarah) && ! $desa->misiSebagaiDaftar())
        <div class="rounded-[12px] border border-dashed border-outline-variant px-6 py-16 text-center">
            <span class="material-symbols-outlined text-5xl text-primary/30 mx-auto block mb-4">draft</span>
            <p class="font-headline-md text-xl text-on-surface mb-2">Profil desa belum dilengkapi</p>
            <p class="max-w-md mx-auto text-sm text-on-surface-variant">
                Visi, misi, dan sejarah desa akan ditampilkan di sini setelah diisi aparat desa.
            </p>
        </div>
    @endif

</div>
@endsection
