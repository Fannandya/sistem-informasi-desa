<?php

namespace Tests\Feature\Konten;

use App\Models\Admin;
use App\Models\CarouselImage;
use Illuminate\Foundation\Testing\LazyLoadingViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CarouselImageTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
    }

    public function test_halaman_carousel_memerlukan_autentikasi(): void
    {
        $this->get(route('admin.carousel.index'))
            ->assertRedirectToRoute('admin.login');
    }

    public function test_tampil_daftar_carousel(): void
    {
        CarouselImage::factory()->count(3)->create();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.carousel.index'))
            ->assertOk()
            ->assertViewIs('admin.carousel.index')
            ->assertViewHas('gambar');
    }

    public function test_tampil_form_tambah(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.carousel.create'))
            ->assertOk()
            ->assertViewIs('admin.carousel.create');
    }

    public function test_simpan_gambar_carousel(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('carousel.jpg', 1920, 1080);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.carousel.store'), [
                'gambar' => $file,
                'judul' => 'Pemandangan Desa',
                'deskripsi' => 'Pemandangan indah di pagi hari',
            ]);

        $response->assertRedirectToRoute('admin.carousel.index');
        $this->assertDatabaseHas('carousel_images', [
            'judul' => 'Pemandangan Desa',
            'deskripsi' => 'Pemandangan indah di pagi hari',
            'tampil' => true,
        ]);
    }

    public function test_validasi_gambar_wajib(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.carousel.store'), [
                'judul' => 'Tanpa Gambar',
            ]);

        $response->assertSessionHasErrors('gambar');
    }

    public function test_tampil_form_ubah(): void
    {
        $carousel = CarouselImage::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.carousel.edit', $carousel))
            ->assertOk()
            ->assertViewIs('admin.carousel.edit')
            ->assertViewHas('carouselImage', $carousel);
    }

    public function test_ubah_gambar_carousel(): void
    {
        Storage::fake('local');
        $carousel = CarouselImage::factory()->create();
        $file = UploadedFile::fake()->image('baru.jpg');

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.carousel.update', $carousel), [
                'gambar' => $file,
                'judul' => 'Judul Baru',
                'deskripsi' => 'Deskripsi Baru',
                'tampil' => '0',
            ])
            ->assertRedirectToRoute('admin.carousel.index');

        $carousel->refresh();
        $this->assertEquals('Judul Baru', $carousel->judul);
        $this->assertEquals('Deskripsi Baru', $carousel->deskripsi);
        $this->assertFalse($carousel->tampil);
    }

    public function test_ubah_tanpa_gambar_baru(): void
    {
        $carousel = CarouselImage::factory()->create();
        $pathLama = $carousel->path;

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.carousel.update', $carousel), [
                'judul' => 'Judul Baru',
                'deskripsi' => 'Deskripsi Baru',
            ])
            ->assertRedirectToRoute('admin.carousel.index');

        $carousel->refresh();
        $this->assertEquals($pathLama, $carousel->path);
        $this->assertEquals('Judul Baru', $carousel->judul);
    }

    public function test_hapus_gambar_carousel(): void
    {
        $carousel = CarouselImage::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.carousel.destroy', $carousel))
            ->assertRedirectToRoute('admin.carousel.index');

        $this->assertDatabaseMissing('carousel_images', ['id' => $carousel->id]);
    }

    public function test_geser_gambar_ke_atas(): void
    {
        $carousel1 = CarouselImage::factory()->create(['urutan' => 1]);
        $carousel2 = CarouselImage::factory()->create(['urutan' => 2]);
        $carousel3 = CarouselImage::factory()->create(['urutan' => 3]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.carousel.geser', $carousel2), ['arah' => 'atas'])
            ->assertRedirectToRoute('admin.carousel.index');

        $carousel1->refresh();
        $carousel2->refresh();

        $this->assertEquals(2, $carousel1->urutan);
        $this->assertEquals(1, $carousel2->urutan);
    }

    public function test_geser_gambar_ke_bawah(): void
    {
        $carousel1 = CarouselImage::factory()->create(['urutan' => 1]);
        $carousel2 = CarouselImage::factory()->create(['urutan' => 2]);
        $carousel3 = CarouselImage::factory()->create(['urutan' => 3]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.carousel.geser', $carousel2), ['arah' => 'bawah'])
            ->assertRedirectToRoute('admin.carousel.index');

        $carousel2->refresh();
        $carousel3->refresh();

        $this->assertEquals(3, $carousel2->urutan);
        $this->assertEquals(2, $carousel3->urutan);
    }

    public function test_carousel_filter_tampil_di_halaman_publik(): void
    {
        CarouselImage::factory()->create(['tampil' => true, 'urutan' => 1]);
        CarouselImage::factory()->create(['tampil' => false, 'urutan' => 2]);

        $response = $this->get(route('beranda'))->assertOk();
        $carousel = $response->viewData('carousel');

        $this->assertCount(1, $carousel);
        $this->assertTrue($carousel[0]->tampil);
    }

    public function test_carousel_urut_di_halaman_publik(): void
    {
        CarouselImage::query()->delete();
        CarouselImage::factory()->create(['urutan' => 3, 'tampil' => true]);
        CarouselImage::factory()->create(['urutan' => 1, 'tampil' => true]);
        CarouselImage::factory()->create(['urutan' => 2, 'tampil' => true]);

        $response = $this->get(route('beranda'))->assertOk();
        $carousel = $response->viewData('carousel');

        $this->assertCount(3, $carousel);
        $this->assertEquals(1, $carousel[0]->urutan);
        $this->assertEquals(2, $carousel[1]->urutan);
        $this->assertEquals(3, $carousel[2]->urutan);
    }
}
