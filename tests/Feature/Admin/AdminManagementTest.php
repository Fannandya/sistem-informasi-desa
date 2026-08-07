<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->superAdmin = Admin::create([
            'nama' => 'Super Admin',
            'email' => 'super@test.test',
            'password' => bcrypt('password123'),
            'is_super_admin' => true,
            'is_active' => true,
        ]);
    }

    // Access Control Tests
    public function test_super_admin_dapat_akses_halaman_kelola_admin() {
        $this->actingAs($this->superAdmin, 'admin')
            ->get('/admin/admin')
            ->assertStatus(200)
            ->assertViewIs('admin.admin.index');
    }

    public function test_admin_biasa_tidak_dapat_akses_halaman_kelola_admin() {
        $adminBiasa = Admin::create([
            'nama' => 'Admin Biasa',
            'email' => 'biasa@test.test',
            'password' => bcrypt('password123'),
            'is_super_admin' => false,
            'is_active' => true,
        ]);

        $this->actingAs($adminBiasa, 'admin')
            ->get('/admin/admin')
            ->assertStatus(403);
    }

    public function test_tamu_tidak_dapat_akses_halaman_kelola_admin() {
        $this->get('/admin/admin')
            ->assertRedirect('/admin/login');
    }

    // Create Admin Tests
    public function test_super_admin_dapat_membuat_admin_baru() {
        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admin', [
                'nama' => 'Admin Baru',
                'email' => 'baru@test.test',
                'password' => 'password123',
                'is_super_admin' => false,
            ])
            ->assertRedirect('/admin/admin');

        $this->assertDatabaseHas('admins', [
            'nama' => 'Admin Baru',
            'email' => 'baru@test.test',
            'is_super_admin' => false,
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);
    }

    public function test_email_harus_unique_saat_create_admin() {
        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admin', [
                'nama' => 'Duplikat Email',
                'email' => $this->superAdmin->email,
                'password' => 'password123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_password_minimal_8_karakter() {
        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admin', [
                'nama' => 'Admin Baru',
                'email' => 'baru2@test.test',
                'password' => 'short',
            ])
            ->assertSessionHasErrors('password');
    }

    // Edit Admin Tests
    public function test_super_admin_dapat_edit_admin() {
        $admin = Admin::create([
            'nama' => 'Admin Lama',
            'email' => 'lama@test.test',
            'password' => bcrypt('password123'),
            'is_super_admin' => false,
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin, 'admin')
            ->put('/admin/admin/' . $admin->id, [
                'nama' => 'Admin Baru',
                'email' => 'lama@test.test',
                'is_super_admin' => true,
            ])
            ->assertRedirect('/admin/admin');

        $admin->refresh();
        $this->assertEquals('Admin Baru', $admin->nama);
        $this->assertTrue($admin->is_super_admin);
        $this->assertEquals($this->superAdmin->id, $admin->updated_by);
    }

    // Delete Admin Tests
    public function test_super_admin_dapat_hapus_admin() {
        $admin = Admin::create([
            'nama' => 'Admin Hapus',
            'email' => 'hapus@test.test',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($this->superAdmin, 'admin')
            ->delete('/admin/admin/' . $admin->id)
            ->assertRedirect('/admin/admin');

        $this->assertDatabaseMissing('admins', ['id' => $admin->id]);
    }

    // Toggle Active Tests
    public function test_super_admin_dapat_nonaktifkan_admin() {
        $admin = Admin::create([
            'nama' => 'Admin Nonaktif',
            'email' => 'nonaktif@test.test',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admin/' . $admin->id . '/nonaktifkan')
            ->assertRedirect('/admin/admin');

        $this->assertFalse($admin->refresh()->is_active);
    }

    public function test_admin_tidak_dapat_nonaktifkan_diri_sendiri() {
        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admin/' . $this->superAdmin->id . '/nonaktifkan')
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertTrue($this->superAdmin->refresh()->is_active);
    }

    public function test_super_admin_dapat_aktifkan_admin() {
        $admin = Admin::create([
            'nama' => 'Admin Aktifkan',
            'email' => 'aktifkan@test.test',
            'password' => bcrypt('password123'),
            'is_active' => false,
        ]);

        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admin/' . $admin->id . '/aktifkan')
            ->assertRedirect('/admin/admin');

        $this->assertTrue($admin->refresh()->is_active);
    }

    // Reset Password Tests
    public function test_super_admin_dapat_reset_sandi_admin() {
        $admin = Admin::create([
            'nama' => 'Admin Reset',
            'email' => 'reset@test.test',
            'password' => bcrypt('oldpassword'),
        ]);

        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admin/' . $admin->id . '/reset-sandi', [
                'password' => 'newpassword123',
            ])
            ->assertRedirect('/admin/admin');

        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('newpassword123', $admin->refresh()->password)
        );
    }

    // Audit Trail Tests
    public function test_created_by_auto_set_saat_create_admin() {
        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admin', [
                'nama' => 'Admin Audit',
                'email' => 'audit@test.test',
                'password' => 'password123',
            ]);

        $admin = Admin::where('email', 'audit@test.test')->first();
        $this->assertEquals($this->superAdmin->id, $admin->created_by);
    }

    public function test_updated_by_auto_set_saat_update_admin() {
        $admin = Admin::create([
            'nama' => 'Admin Update Audit',
            'email' => 'update@test.test',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($this->superAdmin, 'admin')
            ->put('/admin/admin/' . $admin->id, [
                'nama' => 'Admin Updated',
                'email' => 'update@test.test',
            ]);

        $this->assertEquals($this->superAdmin->id, $admin->refresh()->updated_by);
    }

    public function test_dapat_query_kreator_admin() {
        $admin = Admin::create([
            'nama' => 'Admin Creator Test',
            'email' => 'creator@test.test',
            'password' => bcrypt('password123'),
            'created_by' => $this->superAdmin->id,
        ]);

        $this->assertEquals($this->superAdmin->id, $admin->kreatorAdmin->id);
        $this->assertEquals('Super Admin', $admin->kreatorAdmin->nama);
    }

    // Scopes Tests
    public function test_scope_superAdmin() {
        Admin::create([
            'nama' => 'Admin Biasa',
            'email' => 'biasa2@test.test',
            'password' => bcrypt('password123'),
            'is_super_admin' => false,
        ]);

        $superAdmins = Admin::superAdmin()->count();
        $this->assertEquals(1, $superAdmins);
    }

    public function test_scope_aktif() {
        Admin::create([
            'nama' => 'Admin Nonaktif2',
            'email' => 'nonaktif2@test.test',
            'password' => bcrypt('password123'),
            'is_active' => false,
        ]);

        $activeAdmins = Admin::aktif()->count();
        $this->assertEquals(1, $activeAdmins);
    }

    // View Tests
    public function test_halaman_index_tampil_daftar_admin() {
        Admin::create([
            'nama' => 'Admin 2',
            'email' => 'admin2@test.test',
            'password' => bcrypt('password123'),
            'created_by' => $this->superAdmin->id,
        ]);

        $this->actingAs($this->superAdmin, 'admin')
            ->get('/admin/admin')
            ->assertSee('Admin 2')
            ->assertSee('admin2@test.test');
    }

    public function test_halaman_create_dapat_diakses() {
        $this->actingAs($this->superAdmin, 'admin')
            ->get('/admin/admin/tambah')
            ->assertStatus(200)
            ->assertViewIs('admin.admin.create');
    }

    public function test_halaman_edit_dapat_diakses() {
        $admin = Admin::create([
            'nama' => 'Admin Edit',
            'email' => 'edit@test.test',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($this->superAdmin, 'admin')
            ->get('/admin/admin/' . $admin->id . '/ubah')
            ->assertStatus(200)
            ->assertViewIs('admin.admin.edit')
            ->assertViewHas('admin', $admin);
    }

    // Model Methods Tests
    public function test_isSuperAdmin_method() {
        $this->assertTrue($this->superAdmin->isSuperAdmin());

        $adminBiasa = Admin::create([
            'nama' => 'Admin Method Test',
            'email' => 'method@test.test',
            'password' => bcrypt('password123'),
            'is_super_admin' => false,
        ]);

        $this->assertFalse($adminBiasa->isSuperAdmin());
    }

    public function test_isAktif_method() {
        $this->assertTrue($this->superAdmin->isAktif());

        $adminNonaktif = Admin::create([
            'nama' => 'Admin Nonaktif Method',
            'email' => 'nonaktif-method@test.test',
            'password' => bcrypt('password123'),
            'is_active' => false,
        ]);

        $this->assertFalse($adminNonaktif->isAktif());
    }

    // ------------------------------------------------------------------
    // Kata sandi wajib saat membuat admin
    //
    // Aturan lama memakai `required_if:method,POST`, padahal tidak ada field
    // bernama `method` dalam permintaan — Blade mengirim `_method`. Akibatnya
    // `nullable` yang berlaku dan admin bisa lahir tanpa kata sandi.
    // ------------------------------------------------------------------

    public function test_admin_tidak_bisa_dibuat_tanpa_kata_sandi() {
        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admin', [
                'nama' => 'Tanpa Sandi',
                'email' => 'tanpa-sandi@test.test',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('admins', ['email' => 'tanpa-sandi@test.test']);
    }

    public function test_admin_tidak_bisa_dibuat_dengan_kata_sandi_kosong() {
        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admin', [
                'nama' => 'Sandi Kosong',
                'email' => 'sandi-kosong@test.test',
                'password' => '',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('admins', ['email' => 'sandi-kosong@test.test']);
    }

    public function test_ubah_admin_tidak_wajib_mengisi_kata_sandi() {
        $admin = Admin::create([
            'nama' => 'Admin Ubah',
            'email' => 'ubah@test.test',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($this->superAdmin, 'admin')
            ->put('/admin/admin/'.$admin->id, [
                'nama' => 'Admin Ubah Nama',
                'email' => 'ubah@test.test',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/admin');

        $this->assertSame('Admin Ubah Nama', $admin->refresh()->nama);
    }

    // ------------------------------------------------------------------
    // Perlindungan super admin terakhir
    //
    // Panel ini tidak punya jalur pemulihan lewat email. Sekali super admin
    // terakhir terhapus, tercabut statusnya, atau dinonaktifkan, menu kelola
    // admin lenyap dan hanya bisa dikembalikan lewat perintah artisan di
    // server — yang belum tentu bisa dijangkau perangkat desa.
    // ------------------------------------------------------------------

    public function test_super_admin_tidak_bisa_menghapus_dirinya_sendiri() {
        $this->actingAs($this->superAdmin, 'admin')
            ->delete('/admin/admin/'.$this->superAdmin->id)
            ->assertSessionHas('error');

        $this->assertDatabaseHas('admins', ['id' => $this->superAdmin->id]);
    }

    public function test_super_admin_terakhir_tidak_bisa_mencabut_status_dirinya_sendiri() {
        $this->actingAs($this->superAdmin, 'admin')
            ->put('/admin/admin/'.$this->superAdmin->id, [
                'nama' => 'Super Admin',
                'email' => 'super@test.test',
                'is_super_admin' => false,
            ])
            ->assertSessionHas('error');

        $this->assertTrue($this->superAdmin->refresh()->is_super_admin);
    }

    public function test_super_admin_bisa_mencabut_status_dirinya_bila_ada_super_admin_aktif_lain() {
        Admin::create([
            'nama' => 'Super Kedua',
            'email' => 'super2@test.test',
            'password' => bcrypt('password123'),
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin, 'admin')
            ->put('/admin/admin/'.$this->superAdmin->id, [
                'nama' => 'Super Admin',
                'email' => 'super@test.test',
                'is_super_admin' => false,
            ])
            ->assertRedirect('/admin/admin');

        $this->assertFalse($this->superAdmin->refresh()->is_super_admin);
    }

    /**
     * Super admin yang nonaktif tidak bisa masuk, jadi ia tidak bisa
     * menyelamatkan siapa pun. Penjagaan harus menghitung yang aktif saja.
     */
    public function test_super_admin_nonaktif_tidak_dihitung_sebagai_penjaga_terakhir() {
        Admin::create([
            'nama' => 'Super Nonaktif',
            'email' => 'super-nonaktif@test.test',
            'password' => bcrypt('password123'),
            'is_super_admin' => true,
            'is_active' => false,
        ]);

        $this->actingAs($this->superAdmin, 'admin')
            ->put('/admin/admin/'.$this->superAdmin->id, [
                'nama' => 'Super Admin',
                'email' => 'super@test.test',
                'is_super_admin' => false,
            ])
            ->assertSessionHas('error');

        $this->assertTrue($this->superAdmin->refresh()->is_super_admin);
    }

    public function test_super_admin_masih_bisa_mencabut_status_super_admin_lain() {
        $lain = Admin::create([
            'nama' => 'Super Kedua',
            'email' => 'super2@test.test',
            'password' => bcrypt('password123'),
            'is_super_admin' => true,
        ]);

        $this->actingAs($this->superAdmin, 'admin')
            ->put('/admin/admin/'.$lain->id, [
                'nama' => 'Super Kedua',
                'email' => 'super2@test.test',
                'is_super_admin' => false,
            ])
            ->assertRedirect('/admin/admin');

        $this->assertFalse($lain->refresh()->is_super_admin);
    }
}
