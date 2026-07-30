<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maint_ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('user_id')->nullable(); // null = system log
            $table->text('body');
            $table->enum('type', ['comment', 'log'])->default('comment')
                ->comment('comment = dari user/teknisi, log = log otomatis sistem');
            $table->timestamps();

            $table->foreign('request_id')->references('id')->on('asset_maintc_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->index(['request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maint_ticket_comments');
    }
};
