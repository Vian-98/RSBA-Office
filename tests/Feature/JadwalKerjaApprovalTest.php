<?php

namespace Tests\Feature;

use App\Enums\StatusJadwalKerja;
use App\Models\Ruangan;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JadwalKerjaApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions and roles
        Permission::firstOrCreate(['name' => 'view-kepegawaian-jadwal-kerja']);
        Permission::firstOrCreate(['name' => 'add-kepegawaian-jadwal-kerja']);
        Permission::firstOrCreate(['name' => 'edit-kepegawaian-jadwal-kerja']);
        Permission::firstOrCreate(['name' => 'approve-jadwal-kabid']);
        Permission::firstOrCreate(['name' => 'approve-jadwal-wadir']);

        $roleSuperAdmin = Role::firstOrCreate(['name' => 'Super-Admin']);
        $roleKabid = Role::firstOrCreate(['name' => 'Kepala-Bidang']);
        $roleWadir = Role::firstOrCreate(['name' => 'Wakil-Direktur']);
        Role::firstOrCreate(['name' => 'Wadir-Medis-Keperawatan']);
        Role::firstOrCreate(['name' => 'Wadir-SDM-Umum']);
        Role::firstOrCreate(['name' => 'Wadir-Keuangan']);
        Role::firstOrCreate(['name' => 'Direktur']);
        Role::firstOrCreate(['name' => 'Staff-SDM']);
        Role::firstOrCreate(['name' => 'Koordinator-Dokter']);

        $roleKabid->givePermissionTo(['view-kepegawaian-jadwal-kerja', 'approve-jadwal-kabid']);
        $roleWadir->givePermissionTo(['view-kepegawaian-jadwal-kerja', 'approve-jadwal-wadir']);
    }

    private function createDummyKaryawan(string $nip, string $nama, int $ruanganId): Karyawan
    {
        return Karyawan::forceCreate([
            'nip' => $nip,
            'nik' => '1234567890' . substr($nip, -4),
            'nama' => $nama,
            'ruangan_id' => $ruanganId,
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

    public function test_karu_dapat_mengajukan_jadwal_draf_ke_kabid(): void
    {
        $ruangan = Ruangan::create(['nama' => 'Ruang UGD Test']);
        $karyawan = $this->createDummyKaryawan('1001', 'Karu UGD', $ruangan->id);
        $user = User::create([
            'name' => 'Karu UGD',
            'email' => 'karu@test.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawan->id,
        ]);
        $user->givePermissionTo(['view-kepegawaian-jadwal-kerja', 'edit-kepegawaian-jadwal-kerja']);

        DB::table('sdm_ruangan_koordinator')->insert([
            'user_id' => $user->id,
            'karyawan_id' => $karyawan->id,
            'ruangan_id' => $ruangan->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $jadwal = JadwalKerja::create([
            'ruangan_id' => $ruangan->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => StatusJadwalKerja::DRAFT,
            'dibuat_oleh' => $karyawan->id,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Kepegawaian\JadwalKerja\Kelola::class, ['id' => $jadwal->id])
            ->call('ajukanKeKabid')
            ->assertRedirect(route('kepegawaian.jadwal-kerja.index'));

        $this->assertEquals(StatusJadwalKerja::MENUNGGU_KABID, $jadwal->fresh()->status);
    }

    public function test_kabid_dapat_mengkonfirmasi_diketahui_dan_meneruskan_ke_wadir(): void
    {
        $ruangan = Ruangan::create(['nama' => 'Ruang Rawat Inap Test']);
        $karyawanKabid = $this->createDummyKaryawan('1002', 'dr. Kabid Medis', $ruangan->id);
        $userKabid = User::create([
            'name' => 'Kabid Medis',
            'email' => 'kabid@test.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawanKabid->id,
        ]);
        $userKabid->assignRole('Kepala-Bidang');

        $jadwal = JadwalKerja::create([
            'ruangan_id' => $ruangan->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => StatusJadwalKerja::MENUNGGU_KABID,
            'dibuat_oleh' => $karyawanKabid->id,
        ]);

        Livewire::actingAs($userKabid)
            ->test(\App\Livewire\Kepegawaian\JadwalKerja\Kelola::class, ['id' => $jadwal->id])
            ->call('konfirmasiKabid')
            ->assertRedirect(route('kepegawaian.jadwal-kerja.index'));

        $freshJadwal = $jadwal->fresh();
        $this->assertEquals(StatusJadwalKerja::MENUNGGU_WADIR, $freshJadwal->status);
        $this->assertEquals($karyawanKabid->id, $freshJadwal->diketahui_oleh);
        $this->assertNotNull($freshJadwal->diketahui_at);
    }

    public function test_wadir_dapat_menyetujui_dan_mempublikasikan_jadwal(): void
    {
        $ruangan = Ruangan::create(['nama' => 'Ruang Poliklinik Test']);
        $karyawanWadir = $this->createDummyKaryawan('1003', 'dr. Wadir Pelayanan', $ruangan->id);
        $userWadir = User::create([
            'name' => 'Wadir Pelayanan',
            'email' => 'wadir@test.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawanWadir->id,
        ]);
        $userWadir->assignRole('Wakil-Direktur');

        $jadwal = JadwalKerja::create([
            'ruangan_id' => $ruangan->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => StatusJadwalKerja::MENUNGGU_WADIR,
            'diketahui_oleh' => $karyawanWadir->id,
            'diketahui_at' => now(),
            'dibuat_oleh' => $karyawanWadir->id,
        ]);

        Livewire::actingAs($userWadir)
            ->test(\App\Livewire\Kepegawaian\JadwalKerja\Kelola::class, ['id' => $jadwal->id])
            ->call('setujuiWadir')
            ->assertRedirect(route('kepegawaian.jadwal-kerja.index'));

        $freshJadwal = $jadwal->fresh();
        $this->assertEquals(StatusJadwalKerja::PUBLISHED, $freshJadwal->status);
        $this->assertEquals($karyawanWadir->id, $freshJadwal->disetujui_oleh);
        $this->assertNotNull($freshJadwal->disetujui_at);
        $this->assertNotNull($freshJadwal->published_at);
    }

    public function test_kabid_dapat_mengembalikan_jadwal_dengan_catatan_revisi(): void
    {
        $ruangan = Ruangan::create(['nama' => 'Ruang Bedah Test']);
        $karyawanKabid = $this->createDummyKaryawan('1004', 'dr. Kabid Bedah', $ruangan->id);
        $userKabid = User::create([
            'name' => 'Kabid Bedah',
            'email' => 'kabidbedah@test.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawanKabid->id,
        ]);
        $userKabid->assignRole('Kepala-Bidang');

        $jadwal = JadwalKerja::create([
            'ruangan_id' => $ruangan->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => StatusJadwalKerja::MENUNGGU_KABID,
            'dibuat_oleh' => $karyawanKabid->id,
        ]);

        Livewire::actingAs($userKabid)
            ->test(\App\Livewire\Kepegawaian\JadwalKerja\Kelola::class, ['id' => $jadwal->id])
            ->set('catatanRevisiInput', 'Tolong sesuaikan jumlah shift malam pada tanggal 15.')
            ->call('confirmKembalikanDraft')
            ->assertRedirect(route('kepegawaian.jadwal-kerja.index'));

        $freshJadwal = $jadwal->fresh();
        $this->assertEquals(StatusJadwalKerja::DITOLAK, $freshJadwal->status);
        $this->assertEquals('Tolong sesuaikan jumlah shift malam pada tanggal 15.', $freshJadwal->catatan_revisi);
    }

    public function test_koordinator_dokter_dapat_mengajukan_jadwal_langsung_ke_wadir(): void
    {
        $ruangan = Ruangan::create(['nama' => 'Ruang Dokter Spesialis Test']);
        $karyawanKoor = $this->createDummyKaryawan('1005', 'dr. Koordinator Dokter', $ruangan->id);
        $userKoor = User::create([
            'name' => 'Koordinator Dokter',
            'email' => 'koordokter@test.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawanKoor->id,
        ]);
        $userKoor->assignRole('Koordinator-Dokter');
        $userKoor->givePermissionTo(['view-kepegawaian-jadwal-kerja', 'edit-kepegawaian-jadwal-kerja']);

        DB::table('sdm_ruangan_koordinator')->insert([
            'user_id' => $userKoor->id,
            'karyawan_id' => $karyawanKoor->id,
            'ruangan_id' => $ruangan->id,
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $jadwal = JadwalKerja::create([
            'ruangan_id' => $ruangan->id,
            'bulan' => 8,
            'tahun' => 2026,
            'tipe' => 'dokter',
            'status' => StatusJadwalKerja::DRAFT,
            'dibuat_oleh' => $karyawanKoor->id,
        ]);

        $this->assertTrue($jadwal->isDokterSchedule());

        Livewire::actingAs($userKoor)
            ->test(\App\Livewire\Kepegawaian\JadwalKerja\Kelola::class, ['id' => $jadwal->id])
            ->call('ajukanKeWadirLangsung')
            ->assertRedirect(route('kepegawaian.jadwal-kerja.index'));

        $this->assertEquals(StatusJadwalKerja::MENUNGGU_WADIR, $jadwal->fresh()->status);
    }

    public function test_staff_keuangan_atau_pekerja_reguler_tidak_dapat_generate_jadwal(): void
    {
        $ruangan = Ruangan::create(['nama' => 'Bagian Keuangan & Akuntansi']);
        $karyawanKeuangan = $this->createDummyKaryawan('33333', 'Staff Keuangan Test', $ruangan->id);
        $userKeuangan = User::create([
            'name' => 'Staff Keuangan Test',
            'email' => 'keuangan_test@rsba.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawanKeuangan->id,
        ]);

        Role::firstOrCreate(['name' => 'Keuangan']);
        $userKeuangan->assignRole('Keuangan');

        // User Keuangan bukan Super-Admin, bukan Staff-SDM, dan bukan Koordinator Ruangan
        $this->assertFalse($userKeuangan->can('generate', JadwalKerja::class));

        Livewire::actingAs($userKeuangan)
            ->test(\App\Livewire\Kepegawaian\JadwalKerja\Generate::class)
            ->assertStatus(403);
    }
}
