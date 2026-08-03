<?php

namespace Tests\Feature\Unggahan;

use App\Models\Concerns\PunyaGambar;
use App\Services\LayananUnggahGambar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Model uji: hard delete — gambar harus ikut terhapus. */
class ContohBerfoto extends Model
{
    use PunyaGambar;

    protected $table = 'contoh_berfoto';

    protected $guarded = [];

    public $timestamps = false;

    protected array $kolomGambar = ['foto', 'gambar_sampul'];
}

/** Model uji: soft delete — gambar TIDAK boleh terhapus (PRD 6.16). */
class ContohBerfotoArsip extends Model
{
    use PunyaGambar, SoftDeletes;

    protected $table = 'contoh_berfoto_arsip';

    protected $guarded = [];

    public $timestamps = false;

    protected array $kolomGambar = ['foto'];
}

class PunyaGambarTest extends TestCase
{
    private LayananUnggahGambar $layanan;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config()->set('unggahan.disk', 'public');

        Schema::create('contoh_berfoto', function (Blueprint $t) {
            $t->id();
            $t->string('foto')->nullable();
            $t->string('gambar_sampul')->nullable();
        });

        Schema::create('contoh_berfoto_arsip', function (Blueprint $t) {
            $t->id();
            $t->string('foto')->nullable();
            $t->softDeletes();
        });

        $this->layanan = app(LayananUnggahGambar::class);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('contoh_berfoto');
        Schema::dropIfExists('contoh_berfoto_arsip');

        parent::tearDown();
    }

    private function unggah(string $jenis = 'potensi'): string
    {
        return $this->layanan->simpan(UploadedFile::fake()->image('f.jpg', 800, 600), $jenis);
    }

    public function test_menghapus_data_ikut_menghapus_gambar_dan_thumbnail(): void
    {
        $foto = $this->unggah();
        $model = ContohBerfoto::create(['foto' => $foto]);

        $model->delete();

        Storage::disk('public')->assertMissing($foto);
        Storage::disk('public')->assertMissing($this->layanan->pathThumbnail($foto));
    }

    public function test_seluruh_kolom_gambar_ikut_terhapus(): void
    {
        $foto = $this->unggah();
        $sampul = $this->unggah('berita');

        ContohBerfoto::create(['foto' => $foto, 'gambar_sampul' => $sampul])->delete();

        Storage::disk('public')->assertMissing($foto);
        Storage::disk('public')->assertMissing($sampul);
    }

    public function test_data_tanpa_gambar_tidak_menimbulkan_error(): void
    {
        ContohBerfoto::create(['foto' => null])->delete();

        $this->assertDatabaseCount('contoh_berfoto', 0);
    }

    public function test_soft_delete_tidak_menghapus_gambar(): void
    {
        // PRD 6.16: data yang masih bisa dipulihkan tidak boleh kehilangan
        // gambarnya. Trait wajib menolak menghapus berkas untuk model
        // bersoft-delete, meski keduanya dipasang bersamaan.
        $foto = $this->unggah();
        $model = ContohBerfotoArsip::create(['foto' => $foto]);

        $model->delete();

        Storage::disk('public')->assertExists($foto);
        Storage::disk('public')->assertExists($this->layanan->pathThumbnail($foto));
    }
}
