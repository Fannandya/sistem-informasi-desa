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
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-slate-700">
        {{ $label }}
        @if ($required)
            <span class="text-red-500" aria-hidden="true">*</span>
        @endif
    </label>

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($kunciError, $value) }}"
        @if ($required) required @endif
        {{ $attributes->class([
            'w-full rounded-lg border px-3 py-2.5 text-sm shadow-sm focus:ring-2 focus:outline-none',
            'border-slate-300 focus:border-emerald-500 focus:ring-emerald-500/20' => ! $errors->has($kunciError),
            'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has($kunciError),
        ]) }}>

    @if ($keterangan)
        <p class="mt-1.5 text-xs text-slate-500">{{ $keterangan }}</p>
    @endif

    @error($kunciError)
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
