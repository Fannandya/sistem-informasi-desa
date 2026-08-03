@props([
    'name',
    'label',
    'value' => null,
    'rows' => 4,
    'required' => false,
    'keterangan' => null,
])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700">
        {{ $label }}
        @if ($required)
            <span class="text-red-500" aria-hidden="true">*</span>
        @endif
    </label>

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->class([
            'w-full rounded-lg border px-3 py-2.5 text-sm shadow-sm focus:ring-2 focus:outline-none',
            'border-slate-300 focus:border-emerald-500 focus:ring-emerald-500/20' => ! $errors->has($name),
            'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has($name),
        ]) }}>{{ old($name, $value) }}</textarea>

    @if ($keterangan)
        <p class="mt-1.5 text-xs text-slate-500">{{ $keterangan }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
