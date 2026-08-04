<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ref_kategori_potensi')) {
            Schema::create('ref_kategori_potensi', function (Blueprint $table) {
                $table->id();
                $table->string('nama')->unique();
                $table->unsignedInteger('urutan')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ref_kategori_fasilitas')) {
            Schema::create('ref_kategori_fasilitas', function (Blueprint $table) {
                $table->id();
                $table->string('nama')->unique();
                $table->unsignedInteger('urutan')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_kategori_fasilitas');
        Schema::dropIfExists('ref_kategori_potensi');
    }
};
