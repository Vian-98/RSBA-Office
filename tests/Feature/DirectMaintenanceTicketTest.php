<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Sdm\Karyawan;
use App\Models\Assets\AssetBarang;
use App\Models\Master\Barang;
use App\Models\Ruangan;
use App\Livewire\Maintenance\Permintaan\DirectCreate;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DirectMaintenanceTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('PRAGMA foreign_keys = OFF;');
    }

    protected function createFullChain(): array
    {
        $karyawan = Karyawan::create([
            'nama' => 'Koordinator Maintenance',
            'nip' => '9999' . rand(10, 99),
            'nik' => '1234567890123456',
            'tempat_lahir' => 'Bandar Lampung',
            'tgl_lahir' => '1990-01-01',
            'tgl_masuk' => '2018-01-01',
            'status' => 'tetap',
            'jk' => 'L',
            'agama' => 'Islam',
            'hp' => '08123456789',
            'prov' => '1', 'kab' => '1', 'kec' => '1', 'desa' => '1', 'alamat' => 'Test Address',
        ]);

        $user = User::create([
            'name' => 'Koordinator Admin',
            'email' => 'koor_' . rand(100, 999) . '@rsba.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawan->id,
        ]);

        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'approval-maintenance', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);

        $ruangan = Ruangan::create(['nama' => 'Poli Utama ' . rand(10, 99)]);

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
            'nama' => 'AC Central 3PK ' . rand(10, 99),
            'sku' => 'AC-' . rand(1000, 9999),
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

        $asset = AssetBarang::create([
            'barang_id' => $barang->id,
            'ruangan_id' => $ruangan->id,
            'distribusi_det_id' => $distDetId,
            'kode' => 'AST-AC-99',
            'tanggal_catat' => now()->toDateString(),
            'status' => 'baik',
        ]);

        return [
            'barang' => $barang,
            'ruangan' => $ruangan,
            'dist_det_id' => $distDetId,
            'user' => $user,
            'asset' => $asset,
        ];
    }

    public function test_can_create_direct_maintenance_ticket_pending()
    {
        $chain = $this->createFullChain();
        $user = $chain['user'];
        $asset = $chain['asset'];

        $this->actingAs($user);

        Livewire::test(DirectCreate::class)
            ->set('asset_id', $asset->id)
            ->set('workflow_type', 'pending')
            ->set('priority', 'normal')
            ->set('note', 'AC tidak dingin / perlu cuci filter')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('asset_maintc_requests', [
            'asset_id' => $asset->id,
            'status' => 'pending',
            'note' => 'AC tidak dingin / perlu cuci filter',
        ]);

        $asset->refresh();
        $this->assertEquals('diperbaiki', $asset->status);
    }

    public function test_can_create_direct_maintenance_ticket_approved_and_scheduled()
    {
        $chain = $this->createFullChain();
        $user = $chain['user'];
        $asset = $chain['asset'];

        $this->actingAs($user);

        Livewire::test(DirectCreate::class)
            ->set('asset_id', $asset->id)
            ->set('workflow_type', 'approved')
            ->set('priority', 'penting')
            ->set('ket_priority', 'Ac Mati Total di Ruang Operasi')
            ->set('note', 'Ganti kompresor & isi freon')
            ->set('jadwal', now()->format('Y-m-d'))
            ->set('teknisi_id', [$user->id])
            ->set('catatan_teknisi', 'Harap dikerjakan sebelum siang')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('asset_maintc_requests', [
            'asset_id' => $asset->id,
            'status' => 'approved',
            'priority' => 'penting',
            'user_verify_id' => $user->id,
        ]);

        $this->assertDatabaseHas('asset_maintc_jadwal', [
            'asset_id' => $asset->id,
            'tanggal' => now()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('asset_maintc_teknisi_assigment', [
            'teknisi_id' => $user->id,
            'role' => 'leader',
        ]);
    }

    public function test_cannot_create_duplicate_active_maintenance_ticket_for_same_asset()
    {
        $chain = $this->createFullChain();
        $user = $chain['user'];
        $asset = $chain['asset'];

        $this->actingAs($user);

        // 1. Create first active ticket
        Livewire::test(DirectCreate::class)
            ->set('asset_id', $asset->id)
            ->set('workflow_type', 'pending')
            ->set('priority', 'normal')
            ->set('note', 'Perbaikan Pertama')
            ->call('submit')
            ->assertHasNoErrors();

        // 2. Attempt to create second ticket for same asset while first is active
        Livewire::test(DirectCreate::class)
            ->set('asset_id', $asset->id)
            ->set('workflow_type', 'approved')
            ->set('priority', 'normal')
            ->set('note', 'Perbaikan Kedua (Harus Ditolak)')
            ->set('jadwal', now()->format('Y-m-d'))
            ->set('teknisi_id', [$user->id])
            ->call('submit')
            ->assertHasErrors(['asset_id']);

        // Assert only 1 request exists
        $this->assertEquals(1, \App\Models\Maintenance\Request::where('asset_id', $asset->id)->count());
    }
}
