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
        Schema::table('surat_sp3', function (Blueprint $table) {
            //rubah type no menjadi string
            $table->string('no', '35')->change();
            // tambah status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('keterangan');


            // hapus disetujui dan jabatan
            $table->dropForeign(['disetujui']);
            $table->dropColumn('disetujui');
            $table->dropColumn('jabatan');

            // create mengetahui
            $table->unsignedBigInteger('jabatan_id')->default(1)->after('status'); // atau ->notNullable()
            $table->foreign('jabatan_id')->references('id')->on('sdm_jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_sp3', function (Blueprint $table) {
            $table->integer('no')->change();
            $table->dropColumn('status');

            $table->unsignedBigInteger('disetujui')->default(1);
            $table->foreign('disetujui')->references('id')->on('sdm_karyawan')->onDelete('cascade');
            $table->string('jabatan');


            $table->dropForeign(['jabatan_id']);
            $table->dropColumn('jabatan_id');
        });
    }
};
