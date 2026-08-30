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
        Schema::table('asset_maintc_jadwal', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id')->nullable()->change();
        });

        Schema::table('asset_maintc_work', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_maintc_jadwal', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id')->nullable(false)->change();
        });

        Schema::table('asset_maintc_work', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id')->nullable(false)->change();
        });
    }
};
