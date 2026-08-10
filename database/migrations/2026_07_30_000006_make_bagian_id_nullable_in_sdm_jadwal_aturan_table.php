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
        if (!Schema::hasTable('sdm_jadwal_aturan')) {
            return;
        }
        Schema::table('sdm_jadwal_aturan', function (Blueprint $table) {
            $table->unsignedBigInteger('bagian_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_jadwal_aturan', function (Blueprint $table) {
            $table->unsignedBigInteger('bagian_id')->nullable(false)->change();
        });
    }
};
