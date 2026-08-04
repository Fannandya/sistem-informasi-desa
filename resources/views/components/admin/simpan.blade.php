@props(['label' => 'Simpan Perubahan'])

<div class="flex items-center gap-3 border-t border-outline-variant pt-5">
    <button type="submit"
            class="rounded-[12px] bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary transition
                   hover:bg-primary-container focus:ring-2 focus:ring-primary/40 focus:outline-none">
        {{ $label }}
    </button>
    {{ $slot }}
</div>
