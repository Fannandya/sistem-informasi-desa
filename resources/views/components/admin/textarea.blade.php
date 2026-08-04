@props([
    'name',
    'label',
    'value' => null,
    'rows' => 4,
    'required' => false,
    'keterangan' => null,
])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-on-surface">
        {{ $label }}
        @if ($required)
            <span class="text-error" aria-hidden="true">*</span>
        @endif
    </label>

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->class([
            'w-full rounded-[12px] border px-3 py-2.5 text-sm shadow-sm focus:ring-2 focus:outline-none',
            'border-outline-variant focus:border-primary focus:ring-primary/20' => ! $errors->has($name),
            'border-error focus:border-error focus:ring-error/20' => $errors->has($name),
        ]) }}>{{ old($name, $value) }}</textarea>

    @if ($keterangan)
        <p class="mt-1.5 text-xs text-on-surface-variant">{{ $keterangan }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 text-sm text-error">{{ $message }}</p>
    @enderror
</div>
