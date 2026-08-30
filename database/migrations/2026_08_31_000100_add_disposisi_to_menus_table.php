<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $parentSurat = DB::table('menus')->where('id', 17)->orWhere('nama', 'Surat')->first();
        if (!$parentSurat) {
            $parentId = DB::table('menus')->insertGetId([
                'id'         => 17,
                'nama'       => 'Surat',
                'route'      => null,
                'icon'       => 'mail-opened',
                'parent_id'  => 1,
                'group'      => 'sdm',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $parentId = $parentSurat->id;
        }

        $newMenus = [
            [
                'id'          => 79,
                'nama'        => 'Disposisi',
                'route'       => 'kepegawaian.surat.disposisi.index',
                'icon'        => 'file-text',
                'parent_id'   => $parentId,
                'permission'  => json_encode(['view-kepegawaian-surat-disposisi']),
                'group'       => 'sdm',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => 80,
                'nama'        => 'Inbox Disposisi',
                'route'       => 'kepegawaian.surat.disposisi.inbox',
                'icon'        => 'inbox',
                'parent_id'   => $parentId,
                'permission'  => json_encode(['view-kepegawaian-surat-disposisi-inbox']),
                'group'       => 'sdm',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        foreach ($newMenus as $menu) {
            $exists = DB::table('menus')->where('route', $menu['route'])->exists();
            if (!$exists) {
                // Remove id constraint if primary key conflicts
                if (DB::table('menus')->where('id', $menu['id'])->exists()) {
                    unset($menu['id']);
                }
                DB::table('menus')->insert($menu);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menus')->whereIn('route', [
            'kepegawaian.surat.disposisi.index',
            'kepegawaian.surat.disposisi.inbox',
        ])->delete();
    }
};
