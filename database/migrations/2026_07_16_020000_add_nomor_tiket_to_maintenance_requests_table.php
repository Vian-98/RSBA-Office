<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_maintc_requests', function (Blueprint $table) {
            $table->string('nomor_tiket', 30)->nullable()->unique()->after('id')
                ->comment('Format: TKT-MNT-YYYYMMDD-XXX');
        });
    }

    public function down(): void
    {
        Schema::table('asset_maintc_requests', function (Blueprint $table) {
            $table->dropUnique(['nomor_tiket']);
            $table->dropColumn('nomor_tiket');
        });
    }
};
