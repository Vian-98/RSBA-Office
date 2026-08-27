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
        Schema::create('digital_signature_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('digital_signature_document_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('step_order')->default(1);
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->text('rejection_reason')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_hash')->nullable();
            $table->timestamps();

            $table->foreign('digital_signature_document_id', 'ds_appr_doc_id_foreign')
                  ->references('id')->on('digital_signature_documents')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_signature_approvals');
    }
};
