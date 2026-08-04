<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRD 6.4 — tabel `rekap_kk`.
 *
 * Sumber tunggal angka "Jumlah KK". Diinput manual per RT karena nomor Kartu
 * Keluarga sengaja tidak disimpan (PRD 2.4), sehingga jumlah keluarga tidak
 * bisa diturunkan dari data penduduk.
 *
 * Baris yang tidak ada berarti "belum diisi", berbeda dari jumlah_kk = 0 yang
 * berarti "sudah diperiksa, memang tidak ada KK". Perbedaan ini yang membuat
 * dashboard bisa memberi tahu bahwa totalnya belum lengkap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_kk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wilayah_id')->constrained('wilayah')->cascadeOnDelete();
            $table->year('tahun');
            $table->unsignedInteger('jumlah_kk');
            $table->string('catatan', 255)->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->unique(['wilayah_id', 'tahun']);
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_kk');
    }
};
