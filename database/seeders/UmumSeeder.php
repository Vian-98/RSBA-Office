<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UmumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mendapatkan referensi IDs yang diperlukan
        $userReq = User::where('email', 'admin@rsba.com')->first();
        $userVerif = User::where('email', 'superadmin@rsba.com')->first();
        
        $barangIds = DB::table('um_barang')->pluck('id', 'nama')->toArray();
        $satuanIds = DB::table('um_satuan')->pluck('id', 'nama')->toArray();
        
        if (empty($barangIds)) {
            // Jika belum ada barang, tidak usah lanjut karena akan error foreign key
            return;
        }

        $barang1 = array_values($barangIds)[0] ?? 1;
        $barang2 = array_values($barangIds)[1] ?? 1;
        
        $userId = $userReq ? $userReq->id : 1;
        $verifId = $userVerif ? $userVerif->id : 1;

        // 1. Seed Permintaan Barang (Pembelian Request) - Dummy Data
        for ($i = 1; $i <= 5; $i++) {
            $reqId = DB::table('um_pembelian_requests')->insertGetId([
                'note' => 'Permintaan Barang Dummy ' . $i,
                'user_req_id' => $userId,
                'status' => 'pending',
                'lampirans' => json_encode(['dummy_lampiran.pdf']),
                'created_at' => now()->subDays(rand(1, 10)),
                'updated_at' => now(),
            ]);

            // Details Permintaan
            DB::table('um_pembelian_requests_det')->insert([
                [
                    'pembelian_req_id' => $reqId,
                    'barang_id' => $barang1,
                    'jml_req' => rand(5, 50),
                    'jml_disetujui' => 0,
                    'harga_est' => 25000,
                    'keterangan' => 'Butuh cepat',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'pembelian_req_id' => $reqId,
                    'barang_id' => $barang2,
                    'jml_req' => rand(2, 20),
                    'jml_disetujui' => 0,
                    'harga_est' => 50000,
                    'keterangan' => 'Stok menipis',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        // 2. Seed Permintaan Barang yang sudah disetujui (Approved)
        for ($i = 6; $i <= 10; $i++) {
            $reqId = DB::table('um_pembelian_requests')->insertGetId([
                'note' => 'Permintaan Barang Disetujui ' . $i,
                'user_req_id' => $userId,
                'user_verify_id' => $verifId,
                'status' => 'approved',
                'created_at' => now()->subDays(rand(11, 20)),
                'updated_at' => now()->subDays(rand(1, 10)),
            ]);

            DB::table('um_pembelian_requests_det')->insert([
                [
                    'pembelian_req_id' => $reqId,
                    'barang_id' => $barang1,
                    'jml_req' => rand(10, 100),
                    'jml_disetujui' => rand(10, 100),
                    'harga_est' => 25000,
                    'keterangan' => 'Disetujui',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        // 3. Tambahan Dummy Pembelian Langsung
        $supplierId = DB::table('um_supplier')->inRandomOrder()->first()?->id ?? 1;
        
        for ($i = 1; $i <= 3; $i++) {
            $pembelianId = DB::table('um_pembelian')->insertGetId([
                'no' => 'PO-DUMMY-' . rand(1000, 9999),
                'tgl' => now()->subDays(rand(1, 10))->format('Y-m-d'),
                'supplier_id' => $supplierId,
                'jenis' => 'langsung',
                'status_pembayaran' => 'lunas',
                'tgl_pembayaran' => now()->format('Y-m-d'),
                'status' => 'selesai',
                'total' => rand(500000, 2000000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('um_pembelian_det')->insert([
                'pembelian_id' => $pembelianId,
                'barang_id' => $barang1,
                'jumlah' => rand(5, 20),
                'batch' => 'B-DUMMY-' . rand(10, 99),
                'harga_satuan' => 25000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
