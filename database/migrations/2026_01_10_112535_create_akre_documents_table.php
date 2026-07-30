<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('akre_documents', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('filename');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('uploaded_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('deleted_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Fulltext index untuk search nama dokumen
            if (DB::getDriverName() !== 'sqlite') {
                $table->fullText('nama', 'ft_documents_nama');
            }

            // Composite index untuk query utama: filter dokumen aktif per user
            $table->index(['is_deleted', 'uploaded_by'], 'idx_deleted_uploader');

            // Index untuk sort/filter berdasarkan waktu upload (jika sering digunakan)
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akre_documents');
    }
};
