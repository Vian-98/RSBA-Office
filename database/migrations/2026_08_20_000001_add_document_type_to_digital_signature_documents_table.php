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
            $table->string('document_type', 50)->default('digital_signature')->after('document_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digital_signature_documents', function (Blueprint $table) {
            $table->dropColumn(['document_type']);
        });
    }
};
