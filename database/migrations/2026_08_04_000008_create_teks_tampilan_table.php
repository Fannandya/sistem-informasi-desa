<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CMS Teks Tampilan — tabel key-value generik untuk teks & kuota tampilan
 * yang sebelumnya hardcode di kode publik (lihat ANALISIS-CMS.md). Kosong
 * di tabel ini berarti "pakai default dari config/teks-tampilan.php", bukan
 * berarti teksnya kosong — lihat App\Support\Teks::ambil().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teks_tampilan', function (Blueprint $table) {
            $table->id();
            $table->string('kunci', 150)->unique();
            $table->text('nilai')->nullable();
            $table->string('grup', 30);
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teks_tampilan');
    }
};
