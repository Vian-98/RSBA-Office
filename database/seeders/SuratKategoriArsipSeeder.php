<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Surat\SuratKategoriArsip;

class SuratKategoriArsipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'kode'        => 'balasan_pkl',
                'nama'        => 'Balasan PKL',
                'deskripsi'   => 'Surat balasan permohonan izin Praktik Kerja Lapangan (PKL) / Kunjungan Rumah Sakit',
                'icon'        => 'school',
                'is_system'   => true,
                'is_active'   => true,
            ],
            [
                'kode'        => 'balasan_penelitian',
                'nama'        => 'Balasan Penelitian',
                'deskripsi'   => 'Surat balasan permohonan izin penelitian & studi pendahuluan mahasiswa',
                'icon'        => 'microscope',
                'is_system'   => true,
                'is_active'   => true,
            ],
            [
                'kode'        => 'cuti',
                'nama'        => 'Izin dan Cuti',
                'deskripsi'   => 'Surat permohonan izin & cuti tahunan, sakit, alesan penting, melahirkan SDM',
                'icon'        => 'file-text',
                'is_system'   => true,
                'is_active'   => true,
            ],
            [
                'kode'        => 'perintah_tugas',
                'nama'        => 'Perintah Tugas',
                'deskripsi'   => 'Surat Perintah Tugas (SPT) penugasan karyawan / dinas luar RSBA',
                'icon'        => 'clipboard-list',
                'is_system'   => true,
                'is_active'   => true,
            ],
            [
                'kode'        => 'sp3',
                'nama'        => 'SP3 / Pembelian',
                'deskripsi'   => 'Surat Permintaan Penawaran & Pembelian (SP3) barang / jasa',
                'icon'        => 'file-alert',
                'is_system'   => true,
                'is_active'   => true,
            ],
            [
                'kode'        => 'kuitansi',
                'nama'        => 'Kuitansi & Keuangan',
                'deskripsi'   => 'Kuitansi bukti pembayaran resmi RSBA',
                'icon'        => 'receipt-2',
                'is_system'   => true,
                'is_active'   => true,
            ],
            [
                'kode'        => 'surat_disposisi',
                'nama'        => 'Disposisi Direktur',
                'deskripsi'   => 'Surat lembar penerus & disposisi instruksi Direktur RS Bintang Amin',
                'icon'        => 'send',
                'is_system'   => true,
                'is_active'   => true,
            ],
        ];

        foreach ($categories as $cat) {
            SuratKategoriArsip::updateOrCreate(
                ['kode' => $cat['kode']],
                $cat
            );
        }
    }
}
