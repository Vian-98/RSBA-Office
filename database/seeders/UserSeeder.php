<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Sdm\Karyawan;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Rename old admin@admin.com user to admin@rsba.com if it exists to prevent constraint violations
        $oldAdmin = User::where('email', 'admin@admin.com')->first();
        if ($oldAdmin) {
            $oldAdmin->update(['email' => 'admin@rsba.com']);
        }

        $usersToSeed = [
            [
                'email' => 'admin@rsba.com',
                'nama' => 'Super Admin',
                'nip' => '0000000000',
                'nik' => '0000000000000000',
                'role' => 'Super-Admin',
            ],
            [
                'email' => 'dimasfaqih005@gmail.com',
                'nama' => 'Dimas Faqih',
                'nip' => '0000000001',
                'nik' => '0000000000000001',
                'role' => 'Super-Admin',
            ],
            [
                'email' => 'sdm@rsba.com',
                'nama' => 'Staff SDM',
                'nip' => '1111111111',
                'nik' => '1111111111111111',
                'role' => 'Staff-SDM',
            ],
            [
                'email' => 'umum@rsba.com',
                'nama' => 'Staff Umum',
                'nip' => '2222222222',
                'nik' => '2222222222222222',
                'role' => 'Bagian-Umum',
            ],
            [
                'email' => 'keuangan@rsba.com',
                'nama' => 'Staff Keuangan',
                'nip' => '3333333333',
                'nik' => '3333333333333333',
                'role' => 'Keuangan',
            ],
            [
                'email' => 'administrasi@rsba.com',
                'nama' => 'Staff Administrasi',
                'nip' => '4444444444',
                'nik' => '4444444444444444',
                'role' => 'Administrasi',
            ],
            [
                'email' => 'guest@rsba.com',
                'nama' => 'Guest User',
                'nip' => '5555555555',
                'nik' => '5555555555555555',
                'role' => 'Guest',
            ],
        ];

        foreach ($usersToSeed as $u) {
            $karyawan = Karyawan::firstOrCreate(
                ['nip' => $u['nip']],
                [
                    'nik' => $u['nik'],
                    'nama' => $u['nama'],
                    'tgl_lahir' => '1995-01-01',
                    'hp' => '-',
                    'prov' => '-',
                    'kab' => '-',
                    'kec' => '-',
                    'desa' => '-',
                    'alamat' => '-',
                    'agama' => 'islam',
                    'status' => 'tetap',
                    'tgl_masuk' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'password' => Hash::make('1234'),
                    'karyawan_id' => $karyawan->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Assign role safely
            $user->syncRoles([$u['role']]);
        }
    }
}
