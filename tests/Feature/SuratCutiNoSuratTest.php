<?php

namespace Tests\Feature;

use App\Livewire\Forms\SuratCutiForm;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratCuti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratCutiNoSuratTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawan(): Karyawan
    {
        return Karyawan::forceCreate([
            'nip' => '10001',
            'nik' => '1234567890123456',
            'nama' => 'Test Karyawan',
            'tgl_lahir' => '1990-01-01',
            'hp' => '08123456789',
            'prov' => 'Lampung',
            'kab' => 'Bandar Lampung',
            'kec' => 'Kedaton',
            'desa' => 'Penengahan',
            'alamat' => 'Jl. Test',
            'agama' => 'islam',
            'tgl_masuk' => '2020-01-01',
        ]);
    }

    public function test_generate_no_surat_starts_at_0001(): void
    {
        $noSurat = SuratCutiForm::generateNoSurat();
        $tahun = date('Y');
        $this->assertEquals("C0001{$tahun}", $noSurat);
    }

    public function test_generate_no_surat_ignores_cuti_bersama_records(): void
    {
        $tahun = date('Y');
        $karyawan = $this->createKaryawan();
        
        // Insert existing regular cuti
        SuratCuti::create([
            'no_surat' => "C0001{$tahun}",
            'karyawan_id' => $karyawan->id,
            'tgl_surat' => date('Y-m-d'),
            'tgl_mulai' => date('Y-m-d'),
            'tgl_akhir' => date('Y-m-d'),
            'tgl_cuti' => json_encode([date('Y-m-d')]),
            'lama_cuti' => 1,
        ]);

        // Insert a Cuti Bersama record with higher ID
        SuratCuti::create([
            'no_surat' => 'CB01000015',
            'karyawan_id' => $karyawan->id,
            'tgl_surat' => date('Y-m-d'),
            'tgl_mulai' => date('Y-m-d'),
            'tgl_akhir' => date('Y-m-d'),
            'tgl_cuti' => json_encode([date('Y-m-d')]),
            'lama_cuti' => 1,
        ]);

        $noSurat = SuratCutiForm::generateNoSurat();
        $this->assertEquals("C0002{$tahun}", $noSurat);
    }
}
