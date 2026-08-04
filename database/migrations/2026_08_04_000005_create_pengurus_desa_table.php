<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD 6.3 & 6.7 — organisasi dan pengurus desa.
 *
 * Organisasi dibuat sebagai tabel referensi, bukan teks bebas pada tiap
 * pengurus: kolom `urutan` di sinilah yang menentukan susunan kelompok pada
 * halaman publik, sehingga admin bisa mengaturnya sendiri tanpa mengubah kode.
 *
 * Tabel pengurus sengaja TIDAK memakai soft delete (PRD 6.16). Kebutuhan
 * "sembunyikan tanpa hapus" sudah dijawab kolom `tampil`, dan penghapusan
 * permanen membuat fotonya boleh ikut dihapus dari penyimpanan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_organisasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 60)->unique();
            $table->smallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('pengurus_desa', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 120);
            $table->string('jabatan', 80);
            $table->foreignId('organisasi_id')->constrained('ref_organisasi')->restrictOnDelete();
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayah')->nullOnDelete();
            $table->string('nomor_hp', 25)->nullable();
            $table->string('foto')->nullable();
            $table->smallInteger('urutan')->default(0);
            $table->boolean('tampil')->default(true);
            $table->timestamps();

            $table->index(['organisasi_id', 'urutan']);
            $table->index('tampil');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengurus_desa');
        Schema::dropIfExists('ref_organisasi');
    }
};
