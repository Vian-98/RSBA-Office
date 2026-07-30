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
        Schema::create('sdm_absensi_raw_punch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_log_id')->nullable()->constrained('sdm_absensi_import_log')->nullOnDelete();
            $table->integer('no_urut')->nullable();
            $table->string('employee_id', 20);
            $table->string('nama_mentah', 100)->nullable();
            $table->string('departemen', 100)->nullable();
            $table->date('tanggal');
            $table->time('jam');
            $table->dateTime('punch_datetime')->nullable();
            $table->string('punch_state', 20)->nullable();
            $table->string('data_source', 50)->nullable();
            $table->date('assigned_date')->nullable();
            $table->boolean('is_discarded')->default(false);
            $table->string('discard_reason', 100)->nullable();
            $table->unsignedBigInteger('duplicate_reference_id')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'tanggal'], 'idx_employee_date');
            $table->index('import_log_id', 'idx_import_log');
            $table->foreign('duplicate_reference_id')->references('id')->on('sdm_absensi_raw_punch')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_absensi_raw_punch');
    }
};
