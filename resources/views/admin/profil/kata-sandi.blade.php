@extends('admin.layouts.app')

@section('judul', 'Ganti Kata Sandi')

@section('konten')
    <div class="max-w-lg rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">

        <h2 class="text-lg font-semibold text-slate-900">Ganti Kata Sandi</h2>
        <p class="mt-1 mb-6 text-sm text-slate-600">
            Setelah kata sandi diganti, sesi Anda di perangkat lain akan berakhir dan
            harus masuk ulang.
        </p>

        <form method="POST" action="{{ route('admin.kata-sandi.perbarui') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="kata_sandi_lama" class="mb-1.5 block text-sm font-medium text-slate-700">
                    Kata Sandi Lama
                </label>
                <input id="kata_sandi_lama" name="kata_sandi_lama" type="password" required autocomplete="current-password"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm
                              focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                @error('kata_sandi_lama')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kata_sandi_baru" class="mb-1.5 block text-sm font-medium text-slate-700">
                    Kata Sandi Baru
                </label>
                <input id="kata_sandi_baru" name="kata_sandi_baru" type="password" required autocomplete="new-password"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm
                              focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                <p class="mt-1.5 text-xs text-slate-500">Minimal 8 karakter, dan harus berbeda dari kata sandi lama.</p>
                @error('kata_sandi_baru')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kata_sandi_baru_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">
                    Konfirmasi Kata Sandi Baru
                </label>
                <input id="kata_sandi_baru_confirmation" name="kata_sandi_baru_confirmation" type="password" required
                       autocomplete="new-password"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm shadow-sm
                              focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
            </div>

            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white
                           transition hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500/40 focus:outline-none">
                Simpan Kata Sandi Baru
            </button>
        </form>
    </div>
@endsection
