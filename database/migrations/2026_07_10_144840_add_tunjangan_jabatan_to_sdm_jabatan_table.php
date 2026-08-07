<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('sdm_jabatan', 'tunjangan_jabatan')) {
            Schema::table('sdm_jabatan', function (Blueprint $table) {
                $table->decimal('tunjangan_jabatan', 15, 2)->default(0.00)->after('bagian_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_jabatan', function (Blueprint $table) {
            $table->dropColumn('tunjangan_jabatan');
        });
    }
};
