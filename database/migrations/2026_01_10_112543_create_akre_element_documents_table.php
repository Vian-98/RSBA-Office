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
        Schema::create('akre_element_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('element_id');
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('source_element_id')->nullable();
            $table->boolean('is_original')->default(false);
            $table->boolean('source_deleted')->default(false);
            $table->timestamps();


            $table->foreign('element_id')
                ->references('id')
                ->on('akre_elements')
                ->onDelete('cascade');

            $table->foreign('document_id')
                ->references('id')
                ->on('akre_documents')
                ->onDelete('cascade');

            $table->foreign('source_element_id')
                ->references('id')
                ->on('akre_elements')
                ->onDelete('set null');

            // Unique constraint (auto-create index)
            $table->unique(['element_id', 'document_id'], 'uniq_element_document');

            // Index untuk source_element_id (foreign key sudah auto-create index di beberapa DB, tapi explicit lebih baik)
            $table->index('source_element_id', 'idx_source_element');

            // Composite index untuk query filtering berdasarkan status
            // Index ini bisa cover query: WHERE element_id = ? AND is_original = ? AND source_deleted = ?
            $table->index(['element_id', 'is_original', 'source_deleted'], 'idx_element_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akre_element_documents');
    }
};
