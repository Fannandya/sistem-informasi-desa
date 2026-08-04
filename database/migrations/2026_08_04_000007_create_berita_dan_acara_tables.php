<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD 6.11–6.14 — berita dan dokumentasi acara.
 *
 * Keduanya sengaja dipisah (PRD 3.1): berita adalah artikel teks berkategori
 * dengan satu gambar sampul, acara adalah kumpulan foto kegiatan dengan
 * narasi singkat. Nilai kolom `status` dibuat sama persis agar logika
 * penyaringan dan pelabelan dapat dipakai ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_berita', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 60)->unique();
            $table->string('slug', 70)->unique();
            $table->timestamps();
        });

        Schema::create('berita_desa', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 180);
            $table->string('slug', 200)->unique();
            $table->foreignId('kategori_id')->nullable()->constrained('kategori_berita')->nullOnDelete();
            $table->string('ringkasan', 300)->nullable();
            $table->longText('isi');
            $table->string('gambar_sampul')->nullable();
            $table->enum('status', ['draf', 'terbit'])->default('draf');
            $table->boolean('utama')->default(false);
            $table->dateTime('tanggal_terbit')->nullable();
            $table->unsignedInteger('dilihat')->default(0);
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'tanggal_terbit']);
            $table->index('utama');
        });

        Schema::create('acara_desa', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 180);
            $table->string('slug', 200)->unique();
            $table->date('tanggal_acara');
            $table->string('lokasi', 150)->nullable();
            $table->text('narasi')->nullable();
            $table->enum('status', ['draf', 'terbit'])->default('draf');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'tanggal_acara']);
        });

        Schema::create('foto_acara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acara_id')->constrained('acara_desa')->cascadeOnDelete();
            $table->string('path');
            $table->string('keterangan', 200)->nullable();
            $table->boolean('sampul')->default(false);
            $table->smallInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['acara_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_acara');
        Schema::dropIfExists('acara_desa');
        Schema::dropIfExists('berita_desa');
        Schema::dropIfExists('kategori_berita');
    }
};
