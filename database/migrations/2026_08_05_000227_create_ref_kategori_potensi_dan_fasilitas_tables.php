<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_kategori_potensi', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('ref_kategori_fasilitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        // Tambah kolom kategori_id ke tabel potensi_desa
        Schema::table('potensi_desa', function (Blueprint $table) {
            $table->dropIndex(['kategori']);
            $table->dropColumn('kategori');
            $table->foreignId('kategori_potensi_id')->nullable()->after('nama')->constrained('ref_kategori_potensi')->restrictOnDelete();
        });

        // Tambah kolom kategori_id ke tabel fasilitas_umum
        Schema::table('fasilitas_umum', function (Blueprint $table) {
            $table->dropIndex(['tampil', 'kategori']);
            $table->dropColumn('kategori');
            $table->foreignId('kategori_fasilitas_id')->nullable()->after('nama')->constrained('ref_kategori_fasilitas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('potensi_desa', function (Blueprint $table) {
            $table->dropForeignKeyConstraints();
            $table->dropColumn('kategori_potensi_id');
            $table->string('kategori')->nullable();
            $table->index('kategori');
        });

        Schema::table('fasilitas_umum', function (Blueprint $table) {
            $table->dropForeignKeyConstraints();
            $table->dropColumn('kategori_fasilitas_id');
            $table->string('kategori')->nullable();
            $table->index(['tampil', 'kategori']);
        });

        Schema::dropIfExists('ref_kategori_fasilitas');
        Schema::dropIfExists('ref_kategori_potensi');
    }
};
