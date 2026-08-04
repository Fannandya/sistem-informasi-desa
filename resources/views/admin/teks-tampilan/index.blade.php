@extends('admin.layouts.app')

@section('judul', 'Teks Tampilan')

@php
    $grupAktif = request('grup', 'beranda');

    // Bila validasi gagal, buka tab tempat kesalahannya berada agar admin
    // tidak melihat halaman yang seolah tidak bereaksi.
    foreach (collect(config('teks-tampilan'))->groupBy('grup', preserveKeys: true) as $grup => $meta) {
        $kunciError = $meta->keys()->map(fn ($k) => 'nilai.'.str_replace('.', '__', $k))->all();

        if ($errors->hasAny($kunciError)) {
            $grupAktif = $grup;
            break;
        }
    }

    $tab = [
        'beranda' => 'Beranda',
        'footer' => 'Footer',
        'menu' => 'Menu',
        'kuota' => 'Kuota Tampilan',
    ];

    $semuaKunci = collect(config('teks-tampilan'));
@endphp

@section('konten')
<div x-data="{ grup: @js($grupAktif) }" class="max-w-3xl">

    <div class="mb-6 overflow-x-auto">
        <nav class="flex gap-1 border-b border-slate-200" role="tablist">
            @foreach ($tab as $kunciTab => $label)
                <button type="button" role="tab" @click="grup = @js($kunciTab)"
                        class="shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition"
                        :class="grup === @js($kunciTab)
                            ? 'border-emerald-600 text-emerald-700'
                            : 'border-transparent text-slate-500 hover:text-slate-800'">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    @foreach ($tab as $kunciTab => $labelTab)
        <section x-show="grup === @js($kunciTab)" x-cloak
                 class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <form method="POST" action="{{ route('admin.teks-tampilan.perbarui', $kunciTab) }}"
                  data-lacak-perubahan class="space-y-6">
                @csrf @method('PUT')

                @php
                    $kunciGrupIni = $semuaKunci->filter(fn ($meta) => $meta['grup'] === $kunciTab);
                    $perBagian = $kunciGrupIni->groupBy(fn ($meta) => $meta['bagian'] ?? '', preserveKeys: true);
                @endphp

                @foreach ($perBagian as $namaBagian => $kunciBagian)
                    <div class="space-y-4">
                        @if ($namaBagian !== '')
                            <h3 class="text-sm font-semibold tracking-wider text-slate-500 uppercase">{{ $namaBagian }}</h3>
                        @endif

                        <div class="grid gap-5 sm:grid-cols-2">
                            @foreach ($kunciBagian as $kunci => $meta)
                                @php
                                    $namaField = 'nilai['.str_replace('.', '__', $kunci).']';
                                    $keterangan = ($meta['placeholder_tersedia'] ?? []) !== []
                                        ? 'Placeholder yang tersedia: '.implode(', ', $meta['placeholder_tersedia'])
                                        : null;
                                @endphp

                                @if ($meta['tipe'] === 'angka')
                                    <x-admin.field :name="$namaField" :label="$meta['label']" type="number"
                                                   :value="$nilai[$kunci] ?? null" :placeholder="(string) $meta['default']"
                                                   :min="$meta['min']" :max="$meta['max']" :keterangan="$keterangan" />
                                @else
                                    <x-admin.field :name="$namaField" :label="$meta['label']"
                                                   :value="$nilai[$kunci] ?? null" :placeholder="$meta['default']"
                                                   :keterangan="$keterangan" />
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <x-admin.simpan />
            </form>
        </section>
    @endforeach
</div>
@endsection
