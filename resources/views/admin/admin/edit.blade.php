@extends('admin.layouts.app')

@section('konten')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-on-surface">Edit Admin</h1>
        <p class="text-on-surface/60 mt-1">Ubah informasi admin {{ $admin->nama }}</p>
    </div>

    <form method="POST" action="{{ route('admin.admin.update', $admin) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-admin.field
            label="Nama Admin"
            name="nama"
            type="text"
            placeholder="Contoh: Budi Santoso"
            required
            :value="old('nama', $admin->nama)"
            :error="$errors->first('nama')"
        />

        <div class="space-y-2">
            <label class="block text-sm font-medium text-on-surface">Email (tidak bisa diubah)</label>
            <input type="email" value="{{ $admin->email }}" disabled
                   class="w-full px-3 py-2 bg-surface-container border border-outline rounded-lg text-on-surface/50">
            <p class="text-xs text-on-surface/60">Email bersifat permanen dan tidak bisa diubah untuk keamanan</p>
        </div>

        <div class="space-y-2">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_super_admin" value="1"
                       {{ old('is_super_admin', $admin->is_super_admin) ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-outline text-primary">
                <span class="text-on-surface font-medium">Super Admin</span>
            </label>
            <p class="text-sm text-on-surface/60 ml-8">
                Berikan akses untuk membuat dan mengelola admin lain
            </p>
        </div>

        <div class="flex gap-3 pt-4">
            <x-admin.simpan>Simpan Perubahan</x-admin.simpan>
            <a href="{{ route('admin.admin.index') }}" class="px-4 py-2 text-on-surface hover:bg-surface-container rounded-lg">
                Batal
            </a>
        </div>
    </form>

    <div class="bg-surface-container border border-outline rounded-lg p-4 mt-8">
        <h3 class="font-semibold text-on-surface mb-2">Informasi Lainnya</h3>
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-on-surface/60">Status</dt>
                <dd class="font-medium">
                    @if($admin->is_active)
                        <span class="text-green-600">Aktif</span>
                    @else
                        <span class="text-red-600">Nonaktif</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-on-surface/60">Dibuat</dt>
                <dd class="font-medium">{{ $admin->created_at->format('d M Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-on-surface/60">Dibuat Oleh</dt>
                <dd class="font-medium">{{ $admin->kreatorAdmin?->nama ?? 'System' }}</dd>
            </div>
            <div>
                <dt class="text-on-surface/60">Terakhir Login</dt>
                <dd class="font-medium">
                    @if($admin->last_login_at)
                        {{ $admin->last_login_at->format('d M Y H:i') }}
                    @else
                        Belum pernah login
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>
@endsection
