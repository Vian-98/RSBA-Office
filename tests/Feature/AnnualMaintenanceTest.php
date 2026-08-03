<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\Sdm\Karyawan;
use App\Models\Assets\AssetBarang;
use App\Models\Master\Barang;
use App\Models\Ruangan;
use App\Models\Assets\AssetMaintenanceSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class AnnualMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected function createFullChain(): array
    {
        $karyawan = Karyawan::create([
            'nama' => 'Test Karyawan',
            'nip' => '12345' . rand(10, 99),
            'nik' => '1234567890123456',
            'tempat_lahir' => 'Bandar Lampung',
            'tgl_lahir' => '1995-01-01',
            'tgl_masuk' => '2020-01-01',
            'status' => 'tetap',
            'jk' => 'L',
            'agama' => 'Islam',
            'hp' => '08123456789',
            'prov' => '1', 'kab' => '1', 'kec' => '1', 'desa' => '1', 'alamat' => 'Test Address',
        ]);

        $user = User::create([
            'name' => 'System Admin',
            'email' => 'admin_' . rand(100, 999) . '@rsba.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawan->id,
        ]);

        $ruangan = Ruangan::create(['nama' => 'Ruang Server ' . rand(10, 99)]);

        $satuanId = DB::table('um_satuan')->insertGetId([
            'nama' => 'Unit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kategoriId = DB::table('um_kategori')->insertGetId([
            'nama' => 'Elektronik',
            'prefix' => 'ELK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $barang = Barang::create([
            'nama' => 'TV LED 32 Inch ' . rand(10, 99),
            'sku' => 'TV-' . rand(1000, 9999),
            'satuan_id' => $satuanId,
            'kategori_id' => $kategoriId,
        ]);

        $supplierId = DB::table('um_supplier')->insertGetId([
            'nama' => 'PT Supplier Test',
            'alamat' => 'Alamat Supplier',
            'telp' => '08123456789',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pembelianId = DB::table('um_pembelian')->insertGetId([
            'no' => 'PO-' . rand(1000, 9999),
            'tgl' => now()->toDateString(),
            'supplier_id' => $supplierId,
            'jenis' => 'langsung',
            'status_pembayaran' => 'lunas',
            'status' => 'selesai',
            'total' => 1000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pembelianDetId = DB::table('um_pembelian_det')->insertGetId([
            'pembelian_id' => $pembelianId,
            'barang_id' => $barang->id,
            'jumlah' => 10,
            'batch' => 'BATCH-1',
            'harga_satuan' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penerimaanId = DB::table('um_penerimaan_beli')->insertGetId([
            'tanggal' => now()->toDateString(),
            'no_faktur' => 'FAK-' . rand(1000, 9999),
            'penerima' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penerimaanDetId = DB::table('um_penerimaan_beli_det')->insertGetId([
            'penerimaan_id' => $penerimaanId,
            'pembelian_det_id' => $pembelianDetId,
            'jumlah' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stokId = DB::table('um_stok')->insertGetId([
            'barang_id' => $barang->id,
            'penerimaan_det_id' => $penerimaanDetId,
            'stok' => 10,
            'harga_satuan' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $distId = DB::table('um_distribusi')->insertGetId([
            'tanggal' => now()->toDateString(),
            'tujuan' => $ruangan->id,
            'pengirim' => $user->id,
            'penerima' => $user->id,
            'dist_as' => 'stok_gudang',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $distDetId = DB::table('um_distribusi_det')->insertGetId([
            'distribusi_id' => $distId,
            'stok_id' => $stokId,
            'jml' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'barang' => $barang,
            'ruangan' => $ruangan,
            'dist_det_id' => $distDetId,
            'user' => $user,
        ];
    }

    public function test_can_create_asset_maintenance_schedule()
    {
        $chain = $this->createFullChain();

        $asset = AssetBarang::create([
            'barang_id' => $chain['barang']->id,
            'ruangan_id' => $chain['ruangan']->id,
            'distribusi_det_id' => $chain['dist_det_id'],
            'kode' => 'AST-001',
            'tanggal_catat' => now()->toDateString(),
            'status' => 'baik',
        ]);

        $schedule = AssetMaintenanceSchedule::create([
            'asset_barang_id' => $asset->id,
            'judul' => 'Pengecekan Rutin Bulanan',
            'interval_unit' => 'month',
            'interval_value' => 1,
            'tgl_mulai' => now()->toDateString(),
            'tgl_berikutnya' => now()->toDateString(),
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('asset_maintenance_schedules', [
            'id' => $schedule->id,
            'asset_barang_id' => $asset->id,
            'judul' => 'Pengecekan Rutin Bulanan',
        ]);
    }

    public function test_artisan_command_generates_ticket_when_due()
    {
        $chain = $this->createFullChain();

        $asset = AssetBarang::create([
            'barang_id' => $chain['barang']->id,
            'ruangan_id' => $chain['ruangan']->id,
            'distribusi_det_id' => $chain['dist_det_id'],
            'kode' => 'AST-AC-001',
            'tanggal_catat' => now()->toDateString(),
            'status' => 'baik',
        ]);

        $schedule = AssetMaintenanceSchedule::create([
            'asset_barang_id' => $asset->id,
            'judul' => 'Service AC Rutin',
            'interval_unit' => 'month',
            'interval_value' => 1,
            'tgl_mulai' => now()->subMonth()->toDateString(),
            'tgl_berikutnya' => now()->subDays(1)->format('Y-m-d'), // Jatuh tempo kemarin
            'is_active' => true,
        ]);

        $this->artisan('maintenance:process-scheduled')
            ->expectsOutput('Memeriksa jadwal maintenance berkala aset yang jatuh tempo...')
            ->assertExitCode(0);

        $this->assertDatabaseHas('asset_maintc_requests', [
            'asset_id' => $asset->id,
            'status' => 'pending',
        ]);
    }
}
