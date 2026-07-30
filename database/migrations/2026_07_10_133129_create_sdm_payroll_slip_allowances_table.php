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
        Schema::create('sdm_payroll_slip_allowances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_slip_id');
            $table->unsignedBigInteger('allowance_type_id');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('payroll_slip_id')->references('id')->on('sdm_payroll_slips')->onDelete('cascade');
            $table->foreign('allowance_type_id')->references('id')->on('sdm_payroll_allowance_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_payroll_slip_allowances');
    }
};
