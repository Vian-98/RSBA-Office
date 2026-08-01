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
        Schema::table('sdm_payroll_period_locks', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->after('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sdm_payroll_period_locks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
