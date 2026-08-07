<?php

namespace Tests\Feature;

use App\Enums\KategoriKerja;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\JadwalShift;
use App\Models\Sdm\Karyawan;
use App\Models\Ruangan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryawanKategoriKerjaSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_changing_kategori_kerja_to_reguler_auto_syncs_schedule()
    {
        $ruangan = Ruangan::create(['nama' => 'Kantor SDM', 'is_active' => true]);
        $shiftReguler = JadwalShift::create([
            'kode' => 'REGULER',
            'nama' => 'Reguler (Jam Kantor)',
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '16:00:00',
            'aktif' => true,
        ]);
        $shiftPagi = JadwalShift::create([
            'kode' => 'PAGI',
            'nama' => 'Shift Pagi',
            'jam_masuk' => '07:00:00',
            'jam_keluar' => '14:00:00',
            'aktif' => true,
        ]);

        $karyawan = Karyawan::create([
            'nama' => 'Test Employee',
            'nip' => '123456',
            'nik' => '1234567890123456',
            'ruangan_id' => $ruangan->id,
            'kategori_kerja' => KategoriKerja::SHIFT->value,
            'status' => 'tetap',
            'tgl_masuk' => now()->subYear(),
            'tgl_lahir' => '1990-01-01',
            'jk' => 'L',
            'hp' => '08123456789',
            'prov' => '31',
            'kab' => '3171',
            'kec' => '317101',
            'desa' => '31710101',
            'alamat' => 'Jl. Test No. 1',
            'agama' => 'islam',
        ]);

        $bulan = (int) now()->format('n');
        $tahun = (int) now()->format('Y');

        $jadwalKerja = JadwalKerja::create([
            'ruangan_id' => $ruangan->id,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dibuat_oleh' => 1,
        ]);

        // Create detail with old Pagi shift
        $detailSenin = JadwalKerjaDetail::create([
            'jadwal_kerja_id' => $jadwalKerja->id,
            'karyawan_id' => $karyawan->id,
            'shift_id' => $shiftPagi->id,
            'tanggal' => now()->startOfWeek()->format('Y-m-d'), // Monday
            'status_kehadiran' => 'belum_dicek',
        ]);

        $detailSabtu = JadwalKerjaDetail::create([
            'jadwal_kerja_id' => $jadwalKerja->id,
            'karyawan_id' => $karyawan->id,
            'shift_id' => $shiftPagi->id,
            'tanggal' => now()->startOfWeek()->addDays(5)->format('Y-m-d'), // Saturday
            'status_kehadiran' => 'belum_dicek',
        ]);

        // Change employee category to REGULER
        $karyawan->update(['kategori_kerja' => KategoriKerja::REGULER->value]);

        $detailSenin->refresh();
        $detailSabtu->refresh();

        // Monday should now have REGULER shift
        $this->assertEquals($shiftReguler->id, $detailSenin->shift_id);

        // Saturday should now be LIBUR (null shift_id)
        $this->assertNull($detailSabtu->shift_id);
    }
}
