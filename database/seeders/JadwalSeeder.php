<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder untuk data master modul Jadwal Kerja (Fase 0).
 * Isi minimal agar fitur Generate Jadwal bisa langsung digunakan:
 *   - 4 shift dasar (REGULER, PAGI, SIANG, MALAM)
 *   - Aturan jadwal default untuk semua bagian yang ada
 */
class JadwalSeeder extends Seeder
{
    public function run(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            DB::table('sdm_jadwal_shift')->delete();
            DB::table('sdm_ruangan_shift')->delete();
            DB::table('sdm_jadwal_aturan')->delete();
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('sdm_jadwal_shift')->truncate();
            DB::table('sdm_ruangan_shift')->truncate();
            DB::table('sdm_jadwal_aturan')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // ── 1. Master Shift ──────────────────────────────────────────────
        $shifts = [
            [
                'kode'                  => 'REGULER',
                'nama'                  => 'Reguler (Jam Kantor)',
                'jam_masuk'             => '08:00:00',
                'jam_keluar'            => '16:00:00',
                'toleransi_telat_menit' => 15,
                'warna'                 => '#4CAF50',
                'lintas_hari'           => false,
                'aktif'                 => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'kode'                  => 'PAGI',
                'nama'                  => 'Shift Pagi',
                'jam_masuk'             => '07:00:00',
                'jam_keluar'            => '14:00:00',
                'toleransi_telat_menit' => 15,
                'warna'                 => '#2196F3',
                'lintas_hari'           => false,
                'aktif'                 => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'kode'                  => 'SIANG',
                'nama'                  => 'Shift Siang',
                'jam_masuk'             => '14:00:00',
                'jam_keluar'            => '21:00:00',
                'toleransi_telat_menit' => 15,
                'warna'                 => '#FF9800',
                'lintas_hari'           => false,
                'aktif'                 => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'kode'                  => 'MALAM',
                'nama'                  => 'Shift Malam',
                'jam_masuk'             => '21:00:00',
                'jam_keluar'            => '07:00:00',
                'toleransi_telat_menit' => 15,
                'warna'                 => '#9C27B0',
                'lintas_hari'           => true, // jam_keluar < jam_masuk
                'aktif'                 => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
        ];

        DB::table('sdm_jadwal_shift')->insert($shifts);

        $shiftIds = DB::table('sdm_jadwal_shift')->pluck('id', 'kode')->toArray();
        $ruanganIds = DB::table('ruangan')->pluck('id')->toArray();

        // ── 2. Ruangan Shift (whitelist shift mana yang valid per ruangan + override jam) ──
        // Default: semua ruangan bisa pakai semua shift dengan jam default.
        $ruanganShifts = [];
        foreach ($ruanganIds as $ruanganId) {
            foreach ($shiftIds as $shiftId) {
                $ruanganShifts[] = [
                    'ruangan_id'  => $ruanganId,
                    'shift_id'    => $shiftId,
                    'jam_masuk_override'  => null,
                    'jam_keluar_override' => null,
                    'toleransi_telat_menit_override' => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }
        if (!empty($ruanganShifts)) {
            DB::table('sdm_ruangan_shift')->insert($ruanganShifts);
        }

        // ── 3. Aturan Jadwal Default per Bagian ─────────────────────────
        // Nilai bisa diubah lewat menu Master > Aturan Jadwal.
        $aturanDefaults = [
            ['kode' => 'max_shift_malam_berturut',      'nilai' => '3',  'keterangan' => 'Maks. Shift Malam Berturut-turut'],
            ['kode' => 'min_istirahat_jam',              'nilai' => '10', 'keterangan' => 'Minimum Jeda Istirahat Antar Shift (jam)'],
            ['kode' => 'max_hari_kerja_berturut',        'nilai' => '6',  'keterangan' => 'Maks. Hari Kerja Berturut-turut'],
            ['kode' => 'izinkan_tukar_lintas_kategori',  'nilai' => '0',  'keterangan' => 'Izinkan Tukar Jadwal Lintas Kategori Kerja'],
        ];

        $aturans = [];
        $bagianIds = DB::table('bagian')->pluck('id')->toArray();
        foreach ($bagianIds as $bagianId) {
            foreach ($aturanDefaults as $aturan) {
                $aturans[] = [
                    'bagian_id'   => $bagianId,
                    'kode'        => $aturan['kode'],
                    'nilai'       => $aturan['nilai'],
                    'keterangan'  => $aturan['keterangan'],
                    'aktif'       => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }
        if (!empty($aturans)) {
            DB::table('sdm_jadwal_aturan')->insert($aturans);
        }
    }
}
