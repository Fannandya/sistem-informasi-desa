<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD 6.6 — tabel `pengaturan_desa`.
 *
 * Tabel singleton: hanya berisi satu baris dengan id = 1. Seluruh identitas,
 * profil, kontak, dan pengaturan SEO desa berasal dari sini, sehingga halaman
 * publik tidak pernah memuat nama desa atau kontak yang ditulis di dalam kode.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_desa', function (Blueprint $table) {
            $table->id();

            // Identitas
            $table->string('nama_desa', 80)->default('Desa');
            $table->string('logo')->nullable();
            $table->string('foto_sampul')->nullable();
            $table->string('kecamatan', 80)->nullable();
            $table->string('kabupaten', 80)->nullable();
            $table->string('provinsi', 80)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->year('tahun_berdiri')->nullable();
            $table->decimal('luas_desa', 10, 2)->nullable();

            // Profil
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->text('nilai_tujuan')->nullable();
            $table->text('sejarah')->nullable();

            // Letak geografis
            $table->text('letak_geografis')->nullable();
            // Hanya menyimpan URL src hasil ekstraksi, bukan HTML mentah dari
            // admin. Lihat App\Support\EmbedPeta.
            $table->text('peta_embed')->nullable();

            // Kontak
            $table->string('alamat_kantor', 255)->nullable();
            $table->string('telepon_kantor', 25)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('website', 120)->nullable();
            $table->json('medsos')->nullable();

            // SEO
            $table->string('meta_description', 255)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_desa');
    }
};
