<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('signature_logs', function (Blueprint $table) {
            $table->renameColumn('signature_hash', 'signature');
            $table->string('data_hash', 64)->nullable()->after('signature');
            $table->string('algorithm', 20)->default('sha256')->after('data_hash');
            $table->string('sign_type', 100)->index('idx_signable_type')->comment('type dokumen / data tandatangani')->after('algorithm');
            $table->unsignedBigInteger('sign_id')->index('idx_signable_id')->comment('Id table yang ditandatangani')->after('sign_type');
            $table->string('ip_address', 20)->after('sign_id');
            $table->text('user_agent')->after('ip_address');
        });

        // Clean up duplicate/empty data
        DB::table('signature_logs')
            ->whereNull('data_hash')
            ->orWhere('data_hash', '')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('signature_logs')
                        ->where('id', $row->id)
                        ->update([
                            'data_hash' => md5($row->id . '_' . mt_rand())
                        ]);
                }
            });

        // Now add unique constraint
        Schema::table('signature_logs', function (Blueprint $table) {
            $table->unique('data_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signature_logs', function (Blueprint $table) {
            $table->renameColumn('signature', 'signature_hash');
            $table->dropColumn(['data_hash', 'algorithm', 'sign_type', 'sign_id', 'ip_address', 'user_agent']);
        });
    }
};
