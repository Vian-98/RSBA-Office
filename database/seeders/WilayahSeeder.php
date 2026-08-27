<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path('public/wilayah.sql');

        if (file_exists($path)) {
            DB::unprepared(file_get_contents($path));
            $this->command->info('Wilayah table seeded successfully!');
        } else {
            $this->command->warn("File {$path} tidak ditemukan.");
        }
    }
}
