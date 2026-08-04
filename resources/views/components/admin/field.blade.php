@props([
    'name',
    'label',
    'value' => null,
    'type' => 'text',
    'required' => false,
    'keterangan' => null,
    'error' => null,
])

@php
    // Nama berkurung seperti medsos[facebook] perlu dipetakan ke kunci
    // kesalahan bertitik: medsos.facebook
    $kunciError = $error ?? str_replace(['[', ']'], ['.', ''], $name);
    $id = str_replace(['[', ']'], ['_', ''], $name);
@endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-on-surface">
        {{ $label }}
        @if ($required)
            <span class="text-error" aria-hidden="true">*</span>
        @endif
    </label>

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($kunciError, $value) }}"
        @if ($required) required @endif
        {{ $attributes->class([
            'w-full rounded-[12px] border px-3 py-2.5 text-sm shadow-sm focus:ring-2 focus:outline-none',
            'border-outline-variant focus:border-primary focus:ring-primary/20' => ! $errors->has($kunciError),
            'border-error focus:border-error focus:ring-error/20' => $errors->has($kunciError),
        ]) }}>

    @if ($keterangan)
        <p class="mt-1.5 text-xs text-on-surface-variant">{{ $keterangan }}</p>
    @endif

    @error($kunciError)
        <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
    @enderror
</div>
