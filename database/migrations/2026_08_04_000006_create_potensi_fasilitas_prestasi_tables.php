<?php

use App\Enums\TingkatPrestasi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD 6.8, 6.9, 6.10 — tiga tabel konten desa yang bentuknya serupa.
 *
 * Ketiganya memakai hard delete (PRD 6.16) dengan kolom `tampil` sebagai cara
 * menyembunyikan tanpa menghapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('potensi_desa', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 120);
            $table->string('slug', 140)->unique();
            $table->foreignId('kategori_potensi_id')->nullable()->constrained('ref_kategori_potensi')->restrictOnDelete();
            $table->string('deskripsi_singkat', 255)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->string('kontak', 120)->nullable();
            $table->boolean('tampil')->default(true);
            $table->smallInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['tampil', 'urutan']);
            $table->index('kategori_potensi_id');
        });

        Schema::create('fasilitas_umum', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 120);
            $table->foreignId('kategori_fasilitas_id')->nullable()->constrained('ref_kategori_fasilitas')->restrictOnDelete();
            $table->text('deskripsi')->nullable();
            $table->string('alamat', 255)->nullable();
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayah')->nullOnDelete();
            $table->string('foto')->nullable();
            $table->boolean('tampil')->default(true);
            $table->timestamps();

            $table->index(['tampil', 'kategori_fasilitas_id']);
        });

        Schema::create('prestasi_desa', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->year('tahun');
            $table->enum('tingkat', array_column(TingkatPrestasi::cases(), 'value'))->nullable();
            $table->string('penyelenggara', 120)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->boolean('tampil')->default(true);
            $table->timestamps();

            $table->index(['tampil', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestasi_desa');
        Schema::dropIfExists('fasilitas_umum');
        Schema::dropIfExists('potensi_desa');
    }
};
