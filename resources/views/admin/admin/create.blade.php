@extends('admin.layouts.app')

@section('konten')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-on-surface">Tambah Admin Baru</h1>
        <p class="text-on-surface/60 mt-1">Buat akun admin baru untuk mengelola konten desa</p>
    </div>

    <form method="POST" action="{{ route('admin.admin.store') }}" class="space-y-6">
        @csrf

        <x-admin.field
            label="Nama Admin"
            name="nama"
            type="text"
            placeholder="Contoh: Budi Santoso"
            required
            :value="old('nama')"
            :error="$errors->first('nama')"
        />

        <x-admin.field
            label="Email"
            name="email"
            type="email"
            placeholder="contoh@desa.test"
            required
            :value="old('email')"
            :error="$errors->first('email')"
        />

        <x-admin.field
            label="Sandi"
            name="password"
            type="password"
            placeholder="Minimal 8 karakter"
            required
            :error="$errors->first('password')"
        />

        <div class="space-y-2">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_super_admin" value="1"
                       {{ old('is_super_admin') ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-outline text-primary">
                <span class="text-on-surface font-medium">Jadikan Super Admin</span>
            </label>
            <p class="text-sm text-on-surface/60 ml-8">
                Super admin bisa membuat dan mengelola admin lain, plus akses ke semua konten.
            </p>
        </div>

        <div class="flex gap-3 pt-4">
            <x-admin.simpan>Simpan Admin</x-admin.simpan>
            <a href="{{ route('admin.admin.index') }}" class="px-4 py-2 text-on-surface hover:bg-surface-container rounded-lg">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
