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
        Schema::table('um_pembelian', function (Blueprint $table) {
            $table->decimal('subtotal', 16, 2)->default(0)->after('status');
            $table->decimal('total_diskon', 16, 2)->nullable()->after('subtotal');
            $table->integer('total_ppn')->nullable()->after('total_diskon');
            $table->json('lampirans')->nullable()->after('total');
            $table->unsignedBigInteger('created_by')->nullable()->after('lampirans');
            $table->unsignedBigInteger('sp3_id')->nullable()->after('created_by');

            // relations user
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('sp3_id')
                ->references('id')
                ->on('surat_sp3')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('um_pembelian', function (Blueprint $table) {
            $table->dropForeign(['created_by', 'sp3_id']);
            $table->dropColumn(['subtotal', 'total_diskon', 'total_ppn', 'lampirans', 'created_by']);
        });
    }
};
