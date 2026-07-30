<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah kolom `data` dari `json` ke `longText`.
     *
     * MySQL menormalisasi kolom JSON (mengurutkan key secara alfabet
     * dan menambahkan spasi) sehingga data yang dibaca kembali berbeda
     * byte-per-byte dari data asli. Ini menyebabkan verifikasi
     * kriptografis RSA (openssl_verify) selalu gagal karena data
     * yang ditandatangani tidak sama dengan data yang disimpan.
     */
    public function up(): void
    {
        Schema::table('signature_logs', function (Blueprint $table) {
            $table->longText('data')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signature_logs', function (Blueprint $table) {
            $table->json('data')->change();
        });
    }
};
