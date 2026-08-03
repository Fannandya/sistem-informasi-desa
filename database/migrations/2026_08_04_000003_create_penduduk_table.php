<?php

use App\Enums\AlasanKeluar;
use App\Enums\JenisMasuk;
use App\Enums\Pendidikan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD 6.2 — tabel `penduduk`.
 *
 * Yang sengaja TIDAK ada di sini, dan tidak boleh ditambahkan:
 * NIK, nomor Kartu Keluarga, nomor telepon warga, dan alamat rumah detail
 * (PRD 2.4). Ada pengujian otomatis yang gagal bila kolom-kolom itu muncul.
 *
 * Umur juga tidak disimpan. Yang disimpan tanggal lahir; umur dihitung saat
 * dibutuhkan, supaya komposisi demografi tidak basi seiring waktu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penduduk', function (Blueprint $table) {
            $table->id();

            $table->string('nama_lengkap', 120);

            // Nullable hanya untuk menampung data impor yang tidak memuatnya;
            // formulir manual tetap mewajibkan. Warga tanpa tanggal lahir
            // masuk kelompok "Tidak Diketahui" pada statistik.
            $table->date('tanggal_lahir')->nullable();

            $table->enum('jenis_kelamin', ['L', 'P']);

            $table->foreignId('wilayah_id')->constrained('wilayah')->restrictOnDelete();
            $table->foreignId('pekerjaan_id')->nullable()->constrained('ref_pekerjaan')->nullOnDelete();

            $table->enum('pendidikan', array_column(Pendidikan::cases(), 'value'))->nullable();

            // Mutasi masuk — menentukan statistik kelahiran (PRD 2.7).
            $table->enum('jenis_masuk', array_column(JenisMasuk::cases(), 'value'))
                ->default(JenisMasuk::PendataanAwal->value);
            $table->date('tanggal_masuk');

            // Mutasi keluar — menentukan statistik kematian (PRD 2.7).
            $table->enum('alasan_keluar', array_column(AlasanKeluar::cases(), 'value'))->nullable();
            $table->date('tanggal_keluar')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('nama_lengkap');
            $table->index('jenis_kelamin');
            $table->index('tanggal_lahir');
            $table->index('deleted_at');
            // Statistik kelahiran & kematian per tahun menyaring pasangan
            // kolom ini bersamaan.
            $table->index(['jenis_masuk', 'tanggal_masuk']);
            $table->index(['alasan_keluar', 'tanggal_keluar']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penduduk');
    }
};
