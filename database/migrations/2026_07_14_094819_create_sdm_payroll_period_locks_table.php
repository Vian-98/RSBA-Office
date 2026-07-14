<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sdm_payroll_period_locks', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7)->unique(); // YYYY-MM
            $table->boolean('is_approved')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('surat_sp3', function (Blueprint $table) {
            $table->string('payroll_periode', 7)->nullable()->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('surat_sp3', function (Blueprint $table) {
            $table->dropColumn('payroll_periode');
        });
        Schema::dropIfExists('sdm_payroll_period_locks');
    }
};
