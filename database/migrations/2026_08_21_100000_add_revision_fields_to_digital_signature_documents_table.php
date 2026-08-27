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
        Schema::table('digital_signature_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('revises_document_id')->nullable()->after('docstore_key')->index();
            $table->string('revised_from_number', 100)->nullable()->after('revises_document_id');
            $table->text('catatan_revisi')->nullable()->after('revised_from_number');

            $table->foreign('revises_document_id')
                  ->references('id')
                  ->on('digital_signature_documents')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digital_signature_documents', function (Blueprint $table) {
            $table->dropForeign(['revises_document_id']);
            $table->dropColumn(['revises_document_id', 'revised_from_number', 'catatan_revisi']);
        });
    }
};
