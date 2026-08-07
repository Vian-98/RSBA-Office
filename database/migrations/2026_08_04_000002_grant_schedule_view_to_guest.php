<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'view-kepegawaian-jadwal-kerja',
            'guard_name' => 'web',
        ]);

        $guest = Role::where('name', 'Guest')
            ->where('guard_name', 'web')
            ->first();

        if ($guest) {
            $guest->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        $guest = Role::where('name', 'Guest')
            ->where('guard_name', 'web')
            ->first();

        $permission = Permission::where('name', 'view-kepegawaian-jadwal-kerja')
            ->where('guard_name', 'web')
            ->first();

        if ($guest && $permission) {
            $guest->revokePermissionTo($permission);
        }
    }
};
