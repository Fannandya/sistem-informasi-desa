<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur statistik lanjutan — tabel referensi agama.
 *
 * Struktur identik dengan ref_pekerjaan: daftar bisa ditambah admin lewat
 * halaman Agama, dengan entri bawaan (is_template) yang tidak bisa dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_agama', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 60);
            $table->boolean('is_template')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_agama');
    }
};
