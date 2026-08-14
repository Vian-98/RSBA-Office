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
                'nama'       => 'Surat',
                'route'      => null,
                'parent_id'  => null,
                'group'      => 'sdm',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $parentId = $parentSurat->id;
        }

        $newMenus = [
            [
                'nama'        => 'Balasan PKL',
                'route'       => 'kepegawaian.surat.balasan-pkl',
                'icon'        => 'school',
                'parent_id'   => $parentId,
                'permission'  => json_encode(['view-kepegawaian-surat-balasan-pkl']),
                'group'       => 'sdm',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama'        => 'Balasan Penelitian',
                'route'       => 'kepegawaian.surat.balasan-penelitian',
                'icon'        => 'microscope',
                'parent_id'   => $parentId,
                'permission'  => json_encode(['view-kepegawaian-surat-balasan-penelitian']),
                'group'       => 'sdm',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama'        => 'Perintah Tugas',
                'route'       => 'kepegawaian.surat.perintah-tugas',
                'icon'        => 'clipboard-list',
                'parent_id'   => $parentId,
                'permission'  => json_encode(['view-kepegawaian-surat-perintah-tugas']),
                'group'       => 'sdm',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        foreach ($newMenus as $menu) {
            $exists = DB::table('menus')->where('route', $menu['route'])->exists();
            if (!$exists) {
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
            'kepegawaian.surat.balasan-pkl',
            'kepegawaian.surat.balasan-penelitian',
            'kepegawaian.surat.perintah-tugas',
        ])->delete();
    }
};
