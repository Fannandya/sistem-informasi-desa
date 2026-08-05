<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use App\Http\Requests\Admin\ResetSandiAdminRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller {
    public function index() {
        $admins = Admin::with('kreatorAdmin')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.admin.index', compact('admins'));
    }

    public function create() {
        return view('admin.admin.create');
    }

    public function store(AdminRequest $request) {
        Admin::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_super_admin' => $request->boolean('is_super_admin'),
            'is_active' => true,
        ]);

        session()->flash('status', 'Admin berhasil ditambahkan');
        return redirect()->route('admin.admin.index');
    }

    public function edit(Admin $admin) {
        return view('admin.admin.edit', compact('admin'));
    }

    public function update(Admin $admin, AdminRequest $request) {
        $admin->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'is_super_admin' => $request->boolean('is_super_admin'),
        ]);

        session()->flash('status', 'Admin berhasil diperbarui');
        return redirect()->route('admin.admin.index');
    }

    public function destroy(Admin $admin) {
        $admin->delete();
        session()->flash('status', 'Admin berhasil dihapus');
        return redirect()->route('admin.admin.index');
    }

    public function aktifkan(Admin $admin) {
        $admin->update(['is_active' => true]);
        session()->flash('status', 'Admin diaktifkan');
        return redirect()->route('admin.admin.index');
    }

    public function nonaktifkan(Admin $admin) {
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'Tidak bisa nonaktifkan diri sendiri');
        }
        $admin->update(['is_active' => false]);
        session()->flash('status', 'Admin dinonaktifkan');
        return redirect()->route('admin.admin.index');
    }

    public function resetSandi(Admin $admin, ResetSandiAdminRequest $request) {
        $admin->update(['password' => Hash::make($request->password)]);

        session()->flash('status', 'Sandi admin berhasil direset');
        return redirect()->route('admin.admin.index');
    }
}
