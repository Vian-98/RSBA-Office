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
        Schema::create('sdm_payroll_slip_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_slip_id');
            $table->unsignedBigInteger('allowance_allocation_id');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('payroll_slip_id', 'fk_slip_allocations_slip')->references('id')->on('sdm_payroll_slips')->onDelete('cascade');
            $table->foreign('allowance_allocation_id', 'fk_slip_allocations_alloc')->references('id')->on('sdm_payroll_allowance_allocations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_payroll_slip_allocations');
    }
};
