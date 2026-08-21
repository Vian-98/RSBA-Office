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
        Schema::table('sdm_karyawan', function (Blueprint $table) {
            $table->string('ihs_number', 30)->nullable()->index()->after('nik');
            $table->string('no_str', 60)->nullable()->after('bpjs_tk');
            $table->string('jenis_str', 50)->nullable()->after('no_str');
            $table->date('str_terbit')->nullable()->after('jenis_str');
            $table->date('str_berakhir')->nullable()->after('str_terbit');
            $table->string('jenis_profesi', 100)->nullable()->after('str_berakhir');
            $table->string('kompetensi', 150)->nullable()->after('jenis_profesi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_karyawan', function (Blueprint $table) {
            $table->dropColumn([
                'ihs_number',
                'no_str',
                'jenis_str',
                'str_terbit',
                'str_berakhir',
                'jenis_profesi',
                'kompetensi'
            ]);
        });
    }
};
