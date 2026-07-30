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
        Schema::create('akre_elements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('akre_bab_id');
            $table->string('nomor', 4);
            $table->longText('element');
            if (DB::getDriverName() !== 'sqlite') {
                $table->fullText('element');
            }
            $table->json('methode')->comment("R = Regulasi W = Wawancara O = Observasi T = Telusur D = Dokumen S = Simulasi");
            $table->longText('kelengkapan');
            $table->integer('target_nilai');
            $table->tinyInteger('nilai')->nullable();
            $table->boolean('tdd')->default(false);
            $table->string('catatan')->nullable();
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->boolean('is_corection')->default(false);
            $table->timestamps();

            $table->foreign('akre_bab_id')
                ->references('id')
                ->on('akre_bab_elements')
                ->onDelete('cascade');

            $table->foreign('validated_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('akre_bab_id');
            $table->index('nilai');
            $table->index('validated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akre_elements');
    }
};
