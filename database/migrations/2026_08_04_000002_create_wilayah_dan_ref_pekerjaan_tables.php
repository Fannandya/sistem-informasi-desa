<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD 6.1 & 6.3 — tabel referensi.
 *
 * Wilayah dan pekerjaan dibuat sebagai tabel referensi, bukan kolom teks
 * bebas pada tabel penduduk. Alasannya agregasi: "Krajan", "krajan", dan
 * "Dusun Krajan" yang diketik berbeda akan terhitung sebagai tiga wilayah
 * berbeda pada statistik, dan angka desa jadi tidak bisa dipercaya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('dusun', 60);
            $table->string('rw', 3);
            $table->string('rt', 3);
            $table->smallInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique(['dusun', 'rw', 'rt']);
            $table->index('dusun');
        });

        Schema::create('ref_pekerjaan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 60)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_pekerjaan');
        Schema::dropIfExists('wilayah');
    }
};
