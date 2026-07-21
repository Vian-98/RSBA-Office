<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update Surat parent menu to point to kepegawaian.surat.index
        DB::table('menus')->where('id', 17)->update([
            'route' => 'kepegawaian.surat.index',
            'permission' => json_encode(['view-kepegawaian-cuti-bersama', 'view-kepegawaian-surat-cuti', 'view-kepegawaian-surat-sp3', 'view-surat-verification']),
            'updated_at' => now(),
        ]);

        // 2. Remove children from sidebar by setting parent_id = null or deleting submenus
        DB::table('menus')->whereIn('id', [19, 36, 43, 532])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menus')->where('id', 17)->update([
            'route' => null,
            'updated_at' => now(),
        ]);
    }
};
