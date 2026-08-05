<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_desa', function (Blueprint $table) {
            $table->string('hero_text_color')->default('#ffffff')->after('peta_embed');
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_desa', function (Blueprint $table) {
            $table->dropColumn('hero_text_color');
        });
    }
};
