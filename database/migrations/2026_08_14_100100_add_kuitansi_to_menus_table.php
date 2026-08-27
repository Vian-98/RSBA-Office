<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'view-keuangan-kuitansi',
            'create-keuangan-kuitansi',
            'print-keuangan-kuitansi',
            'void-keuangan-kuitansi',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $rootMenu = DB::table('menus')->whereNull('parent_id')->orWhere('id', 1)->first();
        $parentId = $rootMenu ? $rootMenu->id : null;

        if (!DB::table('menus')->where('route', 'keuangan.kuitansi.index')->exists()) {
            DB::table('menus')->insert([
                'nama'       => 'Kuitansi',
                'route'      => 'keuangan.kuitansi.index',
                'icon'       => 'receipt',
                'permission' => json_encode(['view-keuangan-kuitansi']),
                'group'      => 'keu',
                'parent_id'  => $parentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('menus')->where('route', 'keuangan.kuitansi.index')->delete();
    }
};
