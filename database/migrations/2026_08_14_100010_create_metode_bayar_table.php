<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metode_bayar', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->timestamps();
        });

        // Seed initial payment methods
        $initialMethods = ['Tunai', 'Transfer', 'EDC', 'QRIS'];
        foreach ($initialMethods as $method) {
            DB::table('metode_bayar')->insert([
                'nama' => $method,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('metode_bayar');
    }
};
