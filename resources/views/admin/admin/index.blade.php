@extends('admin.layouts.app')

@section('konten')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-on-surface">Kelola Admin</h1>
        @if(auth('admin')->user()->is_super_admin)
            <a href="{{ route('admin.admin.create') }}" class="bg-primary text-on-primary px-4 py-2 rounded-lg hover:bg-primary/90">
                + Tambah Admin
            </a>
        @endif
    </div>

    @if($admins->isEmpty())
        <div class="text-center py-12">
            <p class="text-on-surface/60">Belum ada admin terdaftar.</p>
        </div>
    @else
        <div class="overflow-x-auto bg-surface rounded-lg border border-outline">
            <table class="w-full text-sm">
                <thead class="bg-surface-container border-b border-outline">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-on-surface">Nama</th>
                        <th class="text-left px-4 py-3 font-semibold text-on-surface">Email</th>
                        <th class="text-left px-4 py-3 font-semibold text-on-surface">Type</th>
                        <th class="text-left px-4 py-3 font-semibold text-on-surface">Status</th>
                        <th class="text-left px-4 py-3 font-semibold text-on-surface">Last Login</th>
                        <th class="text-left px-4 py-3 font-semibold text-on-surface">Dibuat Oleh</th>
                        <th class="text-center px-4 py-3 font-semibold text-on-surface">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($admins as $admin)
                        <tr class="border-b border-outline hover:bg-surface-container/50">
                            <td class="px-4 py-3">{{ $admin->nama }}</td>
                            <td class="px-4 py-3">{{ $admin->email }}</td>
                            <td class="px-4 py-3">
                                @if($admin->is_super_admin)
                                    <span class="inline-block px-2 py-1 bg-primary/20 text-primary rounded text-xs font-semibold">Super Admin</span>
                                @else
                                    <span class="inline-block px-2 py-1 bg-secondary/20 text-secondary rounded text-xs font-semibold">Admin</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($admin->is_active)
                                    <span class="inline-block px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">Aktif</span>
                                @else
                                    <span class="inline-block px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-on-surface/60">
                                @if($admin->last_login_at)
                                    {{ $admin->last_login_at->diffForHumans() }}
                                @else
                                    Belum pernah login
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ $admin->kreatorAdmin?->nama ?? 'System' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2 justify-center">
                                    @if(auth('admin')->user()->is_super_admin)
                                        <a href="{{ route('admin.admin.edit', $admin) }}" class="text-primary hover:underline" title="Edit">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>

                                        @if($admin->is_active)
                                            <form method="POST" action="{{ route('admin.admin.nonaktifkan', $admin) }}" class="inline"
                                                  data-konfirmasi data-judul="Nonaktifkan Admin?"
                                                  data-pesan="Admin {{ $admin->nama }} tidak akan bisa login."
                                                  data-tombol="Nonaktifkan" data-jenis="bahaya">
                                                @csrf
                                                <button type="submit" class="text-error hover:underline" title="Nonaktifkan">
                                                    <span class="material-symbols-outlined text-lg">block</span>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.admin.aktifkan', $admin) }}" class="inline"
                                                  data-konfirmasi data-judul="Aktifkan Admin?"
                                                  data-pesan="Admin {{ $admin->nama }} akan bisa login kembali."
                                                  data-tombol="Aktifkan" data-jenis="sukses">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:underline" title="Aktifkan">
                                                    <span class="material-symbols-outlined text-lg">check_circle</span>
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.admin.reset-sandi', $admin) }}" class="inline"
                                              data-konfirmasi data-judul="Reset Sandi?"
                                              data-pesan="Anda akan diminta input sandi baru untuk {{ $admin->nama }}."
                                              data-tombol="Lanjutkan" data-jenis="tanya">
                                            @csrf
                                            <button type="submit" class="text-secondary hover:underline" title="Reset Sandi">
                                                <span class="material-symbols-outlined text-lg">key</span>
                                            </button>
                                        </form>

                                        @if($admin->id !== auth('admin')->id())
                                            <form method="POST" action="{{ route('admin.admin.destroy', $admin) }}" class="inline"
                                                  data-konfirmasi data-judul="Hapus Admin?"
                                                  data-pesan="Admin {{ $admin->nama }} akan dihapus permanen. Tindakan ini tidak bisa dibatalkan."
                                                  data-tombol="Hapus" data-bahaya>
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error hover:underline" title="Hapus">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $admins->links() }}
        </div>
    @endif
</div>
@endsection
