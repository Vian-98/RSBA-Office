<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Permission::firstOrCreate(['name' => 'view-kepegawaian-cuti-bersama']);

        if (DB::table('menus')->where('id', 17)->exists()) {
            DB::table('menus')->insert([
                'nama' => 'Cuti Bersama',
                'route' => 'kepegawaian.cuti-bersama.index',
                'icon' => null,
                'permission' => json_encode(['view-kepegawaian-cuti-bersama']),
                'group' => 'sdm',
                'parent_id' => 17, // Dropdown 'Surat' di Sidebar SDM
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menus')->where('route', 'kepegawaian.cuti-bersama.index')->delete();
    }
};
