@props([
    'name',
    'label' => null,
    'value' => null,
    'pilihan' => [],
    'kosong' => '— Pilih —',
    'required' => false,
    'keterangan' => null,
])

@php
    $kunciError = str_replace(['[', ']'], ['.', ''], $name);
    $id = str_replace(['[', ']'], ['_', ''], $name);
    $terpilih = (string) old($kunciError, $value);
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-on-surface">
            {{ $label }}
            @if ($required)
                <span class="text-error" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <select id="{{ $id }}" name="{{ $name }}" @if ($required) required @endif
        {{ $attributes->class([
            'w-full rounded-[12px] border bg-surface-container-lowest px-3 py-2.5 text-sm shadow-sm focus:ring-2 focus:outline-none',
            'border-outline-variant focus:border-primary focus:ring-primary/20' => ! $errors->has($kunciError),
            'border-error focus:border-error focus:ring-error/20' => $errors->has($kunciError),
        ]) }}>

        @if ($kosong !== false)
            <option value="">{{ $kosong }}</option>
        @endif

        @foreach ($pilihan as $nilai => $teks)
            <option value="{{ $nilai }}" @selected($terpilih === (string) $nilai)>{{ $teks }}</option>
        @endforeach
    </select>

    @if ($keterangan)
        <p class="mt-1.5 text-xs text-on-surface-variant">{{ $keterangan }}</p>
    @endif

    @error($kunciError)
        <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
    @enderror
</div>
