<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 1, Task 1: Tambah kolom admin management ke tabel admins.
 *
 * Menambahkan:
 * - is_super_admin (boolean, default false)
 * - is_active (boolean, default true)
 * - created_by (unsigned big integer, nullable, FK ke admins.id)
 * - updated_by (unsigned big integer, nullable, FK ke admins.id)
 *
 * Semua admin existing di-set sebagai super admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Tambah kolom setelah password
            $table->boolean('is_super_admin')->default(false)->after('password');
            $table->boolean('is_active')->default(true)->after('is_super_admin');

            // Foreign key references untuk created_by dan updated_by
            $table->foreignId('created_by')->nullable()->after('is_active')->constrained('admins')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('admins')->nullOnDelete();

            // Index untuk pencarian super admin yang aktif
            $table->index(['is_super_admin', 'is_active']);
        });

        // Set semua admin existing menjadi super admin
        DB::table('admins')->update(['is_super_admin' => true]);
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Hapus foreign keys terlebih dahulu
            $table->dropForeignIdFor('created_by');
            $table->dropForeignIdFor('updated_by');

            // Hapus index
            $table->dropIndex(['is_super_admin', 'is_active']);

            // Hapus kolom dalam urutan sebaliknya
            $table->dropColumn(['updated_by', 'created_by', 'is_active', 'is_super_admin']);
        });
    }
};
