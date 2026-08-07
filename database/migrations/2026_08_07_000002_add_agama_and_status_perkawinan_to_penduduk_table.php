<?php

use App\Enums\StatusPerkawinan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penduduk', function (Blueprint $table) {
            $table->foreignId('agama_id')->nullable()->after('pekerjaan_id')
                ->constrained('ref_agama')->nullOnDelete();

            $table->enum('status_perkawinan', array_column(StatusPerkawinan::cases(), 'value'))
                ->nullable()->after('pendidikan');
        });
    }

    public function down(): void
    {
        Schema::table('penduduk', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agama_id');
            $table->dropColumn('status_perkawinan');
        });
    }
};
