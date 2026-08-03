@props(['label' => 'Simpan Perubahan'])

<div class="flex items-center gap-3 border-t border-slate-200 pt-5">
    <button type="submit"
            class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition
                   hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500/40 focus:outline-none">
        {{ $label }}
    </button>
    {{ $slot }}
</div>
