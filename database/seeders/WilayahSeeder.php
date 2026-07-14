<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = 'public/wilayah.sql';

        // Increase max_allowed_packet globally to handle the large SQL file (~2.7MB)
        DB::statement("SET GLOBAL max_allowed_packet = 16777216"); // 16MB
        // Reconnect so the new global value takes effect for this session
        DB::reconnect();

        DB::unprepared(file_get_contents($path));
        $this->command->info('Wilayah table seeded!');
    }
}
