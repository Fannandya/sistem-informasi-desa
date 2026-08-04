<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ref_pekerjaan', function (Blueprint $table) {
            $table->boolean('is_template')->default(false)->after('nama');
        });

        // Mark default pekerjaan as template
        DB::update("UPDATE ref_pekerjaan SET is_template = 1 WHERE id <= 10");
    }

    public function down(): void
    {
        Schema::table('ref_pekerjaan', function (Blueprint $table) {
            $table->dropColumn('is_template');
        });
    }
};
