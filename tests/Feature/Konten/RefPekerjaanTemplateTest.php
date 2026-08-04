<?php

namespace Tests\Feature\Konten;

use App\Models\Admin;
use App\Models\RefPekerjaan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefPekerjaanTemplateTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
    }

    public function test_template_pekerjaan_tidak_bisa_dihapus(): void
    {
        $template = RefPekerjaan::factory()->create(['is_template' => true]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.pekerjaan.destroy', $template));

        $response->assertSessionHasErrors(['nama']);
        $this->assertDatabaseHas('ref_pekerjaan', ['id' => $template->id]);
    }

    public function test_custom_pekerjaan_bisa_dihapus(): void
    {
        $custom = RefPekerjaan::factory()->create(['is_template' => false]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.pekerjaan.destroy', $custom));

        $response->assertRedirectToRoute('admin.pekerjaan.index');
        $this->assertDatabaseMissing('ref_pekerjaan', ['id' => $custom->id]);
    }

    public function test_pekerjaan_dipakai_tidak_bisa_dihapus(): void
    {
        $pekerjaan = RefPekerjaan::factory()->create(['is_template' => false]);
        \App\Models\Penduduk::factory()->create(['pekerjaan_id' => $pekerjaan->id]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.pekerjaan.destroy', $pekerjaan));

        $response->assertSessionHasErrors(['nama']);
        $this->assertDatabaseHas('ref_pekerjaan', ['id' => $pekerjaan->id]);
    }

    public function test_template_pekerjaan_ditampilkan_di_dropdown(): void
    {
        RefPekerjaan::factory()->create(['nama' => 'Petani', 'is_template' => true]);
        RefPekerjaan::factory()->create(['nama' => 'Custom Job', 'is_template' => false]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.penduduk.create'));

        $response->assertOk()
            ->assertSee('Petani')
            ->assertSee('Custom Job');
    }
}
