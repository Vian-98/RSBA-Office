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
        Schema::table('surat_cuti', function (Blueprint $table) {
            $table->dropColumn('urgensi');
            $table->unsignedBigInteger('urgensi_id')->nullable()->after('lama_cuti');

            $table->foreign('urgensi_id')
                ->references('id')
                ->on('surat_cuti_jenis')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_cuti', function (Blueprint $table) {
            $table->dropForeign(['urgensi_id']);
            $table->dropColumn('urgensi_id');

            $table->enum('urgensi', ['tahunan', 'besar', 'sakit', 'bersalin', 'penting', 'lain'])->nullable()->after('id');
        });
    }
};
