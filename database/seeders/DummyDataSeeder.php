<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Sdm\Karyawan;
use App\Enums\StatusApproval;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Disable Foreign Key Checks
        Schema::disableForeignKeyConstraints();

        // 2. Truncate Tables
        $tables = [
            'sdm_kary_jabatan',
            'sdm_kary_pendidikan',
            'sdm_jabatan',
            'bagian',
            'ruangan',
            'surat_cuti_jenis',
            'surat_cuti',
            'surat_cuti_approval',
            'surat_sp3',
            'surat_sp3_details',
            'surat_sp3_approval',
            'um_supplier',
            'um_kategori',
            'um_penyimpanan',
            'um_satuan',
            'um_barang',
            'um_stok',
            'um_pembelian',
            'um_pembelian_det',
            'um_penerimaan_beli',
            'um_penerimaan_beli_det',
            'um_distribusi',
            'um_distribusi_det',
            'asset_barang',
            'asset_maintc_requests',
            'asset_maintc_jadwal',
            'asset_maintc_teknisi_assigment',
            'asset_maintc_work',
            'asset_maintc_work_parts',
            'dokter_spesialisasi',
            'dokter',
            'jm_pasien',
            'jm_dokter',
            'um_pembelian_requests',
            'um_pembelian_requests_det',
            'akre_element_documents',
            'akre_elements',
            'akre_files',
            'akre_documents',
            'akre_bab_elements',
            'akre_chapter',
            'akre_kegiatan',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        // 3. Re-enable Foreign Key Checks
        Schema::enableForeignKeyConstraints();

        // 4. Seed Ruangan
        $this->call(RuanganDummySeeder::class);
        $ruanganIds = DB::table('ruangan')->pluck('id')->toArray();

        // 5. Seed Bagian
        $bagians = [
            ['nama' => 'Medis', 'is_active' => 1, 'group' => 'medis', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Keperawatan', 'is_active' => 1, 'group' => 'medis', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'SDM', 'is_active' => 1, 'group' => 'manajemen', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Umum & Logistik', 'is_active' => 1, 'group' => 'penunjang', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Keuangan', 'is_active' => 1, 'group' => 'manajemen', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('bagian')->insert($bagians);
        $bagianIds = DB::table('bagian')->pluck('id', 'nama')->toArray();

        // 6. Seed Jabatan (Needs self-referencing disable/enable)
        Schema::disableForeignKeyConstraints();
        $jabatans = [
            ['id' => 1, 'nama' => 'Direktur Utama', 'kode_surat' => 'DIR', 'parent_id' => 1, 'bagian_id' => $bagianIds['SDM'], 'tunjangan_jabatan' => 5000000, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nama' => 'Kepala Bagian SDM', 'kode_surat' => 'KABAG-SDM', 'parent_id' => 1, 'bagian_id' => $bagianIds['SDM'], 'tunjangan_jabatan' => 2500000, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nama' => 'Kepala Bagian Umum', 'kode_surat' => 'KABAG-UM', 'parent_id' => 1, 'bagian_id' => $bagianIds['Umum & Logistik'], 'tunjangan_jabatan' => 2500000, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nama' => 'Kepala Bagian Keuangan', 'kode_surat' => 'KABAG-KEU', 'parent_id' => 1, 'bagian_id' => $bagianIds['Keuangan'], 'tunjangan_jabatan' => 2500000, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nama' => 'Staff Pelaksana SDM', 'kode_surat' => 'STF-SDM', 'parent_id' => 2, 'bagian_id' => $bagianIds['SDM'], 'tunjangan_jabatan' => 500000, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'nama' => 'Staff Pelaksana Umum', 'kode_surat' => 'STF-UM', 'parent_id' => 3, 'bagian_id' => $bagianIds['Umum & Logistik'], 'tunjangan_jabatan' => 500000, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'nama' => 'Staff Pelaksana Keuangan', 'kode_surat' => 'STF-KEU', 'parent_id' => 4, 'bagian_id' => $bagianIds['Keuangan'], 'tunjangan_jabatan' => 500000, 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('sdm_jabatan')->insert($jabatans);
        Schema::enableForeignKeyConstraints();

        // 7. Map existing seeded users to Karyawan Jabatans
        $karyawanSdm = Karyawan::where('nama', 'Staff SDM')->first();
        $karyawanUmum = Karyawan::where('nama', 'Staff Umum')->first();
        $karyawanKeuangan = Karyawan::where('nama', 'Staff Keuangan')->first();
        $karyawanAdmin = Karyawan::where('nama', 'Super Admin')->first();

        // 7b. Create Kepala Bagian karyawan so persetujuan atasan is populated
        $kabagSdm = Karyawan::firstOrCreate(
            ['nip' => '6060606060'],
            [
                'nik' => '6060606060606060', 'nama' => 'Kepala Bagian SDM',
                'tgl_lahir' => '1980-05-15', 'hp' => '081200000001',
                'prov' => '-', 'kab' => '-', 'kec' => '-', 'desa' => '-', 'alamat' => 'Kota A',
                'agama' => 'islam', 'status' => 'tetap',
                'tgl_masuk' => '2015-01-01', 'cuti' => 0,
            ]
        );
        $kabagUmum = Karyawan::firstOrCreate(
            ['nip' => '7070707070'],
            [
                'nik' => '7070707070707070', 'nama' => 'Kepala Bagian Umum',
                'tgl_lahir' => '1979-03-20', 'hp' => '081200000002',
                'prov' => '-', 'kab' => '-', 'kec' => '-', 'desa' => '-', 'alamat' => 'Kota B',
                'agama' => 'islam', 'status' => 'tetap',
                'tgl_masuk' => '2014-06-01', 'cuti' => 0,
            ]
        );
        $kabagKeuangan = Karyawan::firstOrCreate(
            ['nip' => '8080808080'],
            [
                'nik' => '8080808080808080', 'nama' => 'Kepala Bagian Keuangan',
                'tgl_lahir' => '1982-11-10', 'hp' => '081200000003',
                'prov' => '-', 'kab' => '-', 'kec' => '-', 'desa' => '-', 'alamat' => 'Kota C',
                'agama' => 'islam', 'status' => 'tetap',
                'tgl_masuk' => '2016-03-01', 'cuti' => 0,
            ]
        );

        $karyawanDimas = Karyawan::where('nama', 'Dimas Faqih')->first();

        $karyawanJabatans = [];
        if ($karyawanAdmin) {
            $karyawanJabatans[] = ['jabatan_id' => 1, 'karyawan_id' => $karyawanAdmin->id, 'tgl_mulai' => '2020-01-01', 'created_at' => now(), 'updated_at' => now()];
        }
        if ($karyawanDimas) {
            $karyawanJabatans[] = ['jabatan_id' => 1, 'karyawan_id' => $karyawanDimas->id, 'tgl_mulai' => '2026-01-01', 'created_at' => now(), 'updated_at' => now()];
        }
        // Kepala Bagian
        $karyawanJabatans[] = ['jabatan_id' => 2, 'karyawan_id' => $kabagSdm->id, 'tgl_mulai' => '2015-01-01', 'created_at' => now(), 'updated_at' => now()];
        $karyawanJabatans[] = ['jabatan_id' => 3, 'karyawan_id' => $kabagUmum->id, 'tgl_mulai' => '2014-06-01', 'created_at' => now(), 'updated_at' => now()];
        $karyawanJabatans[] = ['jabatan_id' => 4, 'karyawan_id' => $kabagKeuangan->id, 'tgl_mulai' => '2016-03-01', 'created_at' => now(), 'updated_at' => now()];
        // Staff
        if ($karyawanSdm) {
            $karyawanJabatans[] = ['jabatan_id' => 5, 'karyawan_id' => $karyawanSdm->id, 'tgl_mulai' => '2021-06-01', 'created_at' => now(), 'updated_at' => now()];
        }
        if ($karyawanUmum) {
            $karyawanJabatans[] = ['jabatan_id' => 6, 'karyawan_id' => $karyawanUmum->id, 'tgl_mulai' => '2022-03-15', 'created_at' => now(), 'updated_at' => now()];
        }
        if ($karyawanKeuangan) {
            $karyawanJabatans[] = ['jabatan_id' => 7, 'karyawan_id' => $karyawanKeuangan->id, 'tgl_mulai' => '2023-01-10', 'created_at' => now(), 'updated_at' => now()];
        }
        DB::table('sdm_kary_jabatan')->insert($karyawanJabatans);

        // --- Tambahan: Dummy Pegawai Ruangan untuk UGD dan Poli Anak ---
        // Cari ID ruangan berdasarkan nama agar tidak tergantung pada urutan array
        $ruanganUgd = DB::table('ruangan')->where('nama', 'like', '%UGD%')->value('id') 
            ?? (DB::table('ruangan')->where('nama', 'like', '%IGD%')->value('id') ?? 1);
        $ruanganPoliAnak = DB::table('ruangan')->where('nama', 'like', '%Poli Anak%')->value('id') ?? 2;
        $ruanganMelati = DB::table('ruangan')->where('nama', 'like', '%Melati%')->value('id') ?? 4;
        
        $dummyPegawai = [];
        $kategoriKerja = \App\Enums\KategoriKerja::class;
        
        $karyawanAbsensi = [
            ['nip' => '21220175', 'nama' => 'Afrizal'],
            ['nip' => '22200233', 'nama' => 'Rosiana'],
            ['nip' => '21220077', 'nama' => 'Agustina'],
            ['nip' => '22210264', 'nama' => 'Arif Pamungkas'],
            ['nip' => '21220131', 'nama' => 'Dwi Septiana'],
            ['nip' => '21220082', 'nama' => 'Leni Kristina'],
            ['nip' => '22200240', 'nama' => 'Farouk Alfero'],
            ['nip' => '21220189', 'nama' => 'Maya Kurniawati'],
            ['nip' => '21220087', 'nama' => 'Nurma novianti'],
            ['nip' => '21220133', 'nama' => 'Risnawati'],
            ['nip' => '21220118', 'nama' => 'Rurin Astiar'],
            ['nip' => '21190039', 'nama' => 'Mira Novita'],
            ['nip' => '21220134', 'nama' => 'Septian Riadi'],
            ['nip' => '22220327', 'nama' => 'Zirki Orlanda'],
            ['nip' => '21220083', 'nama' => 'Etik Nurhayati'],
            ['nip' => '21220132', 'nama' => 'Herdinan'],
            ['nip' => '22190203', 'nama' => 'I Ketut Kariawan'],
            ['nip' => '21220171', 'nama' => 'Rahmad Arifin'],
            ['nip' => '21220085', 'nama' => 'Rini Alamia'],
            ['nip' => '22220312', 'nama' => 'Yeni Erawati'],
            ['nip' => '21220119', 'nama' => 'Titin'],
            ['nip' => '21220090', 'nama' => 'Andi Apriyasyah'],
            ['nip' => '21080013', 'nama' => 'Jhon Aziz'],
            ['nip' => '21220092', 'nama' => 'Budi Setia Utomo'],
            ['nip' => '21220129', 'nama' => 'Dian'],
            ['nip' => '21190054', 'nama' => 'Desky Hendra'],
            ['nip' => '21220108', 'nama' => 'Fitria Eka'],
            ['nip' => '22230337', 'nama' => 'Ahmad Efendri'],
            ['nip' => '21220112', 'nama' => 'Martina Eka'],
            ['nip' => '21080004', 'nama' => 'Afridawati'],
            ['nip' => '24230005', 'nama' => 'Riska Absari'],
            ['nip' => '21190067', 'nama' => 'Era Zulfia'],
            ['nip' => '22220309', 'nama' => 'M Rizki Genio'],
            ['nip' => '22220323', 'nama' => 'Rohida'],
            ['nip' => '21190056', 'nama' => 'Sisi Natalia'],
            ['nip' => '22210269', 'nama' => 'Rolly Alvares'],
            ['nip' => '22200243', 'nama' => 'Yulia Atika'],
            ['nip' => '22200241', 'nama' => 'Siti Setiani'],
            ['nip' => '22220324', 'nama' => 'Wahyu Putri'],
            ['nip' => '22190214', 'nama' => 'Fakhri'],
            ['nip' => '21220165', 'nama' => 'Dea'],
            ['nip' => '22200245', 'nama' => 'Fahri Ramadona'],
            ['nip' => '21220149', 'nama' => 'Fera'],
            ['nip' => '21220183', 'nama' => 'Jefta'],
            ['nip' => '21220168', 'nama' => 'Lay Rizka'],
            ['nip' => '21220191', 'nama' => 'Vilandari'],
            ['nip' => '21220190', 'nama' => 'Nur Khotinah'],
            ['nip' => '21220117', 'nama' => 'Rischa Pratiwi'],
            ['nip' => '21190029', 'nama' => 'Yuniati'],
            ['nip' => '22220326', 'nama' => 'Yunita Shara'],
            ['nip' => '21220138', 'nama' => 'Suci Yuliansari'],
            ['nip' => '22140040', 'nama' => 'Dwi Ariyanti'],
            ['nip' => '22220311', 'nama' => 'Andri Darmawan'],
            ['nip' => '22220316', 'nama' => 'Dhyas'],
            ['nip' => '22200232', 'nama' => 'Enjelina'],
            ['nip' => '22210265', 'nama' => 'Erlina'],
            ['nip' => '21220155', 'nama' => 'Mega Mustika'],
            ['nip' => '21220182', 'nama' => 'Rio Ardi Prayoga'],
            ['nip' => '22210274', 'nama' => 'Oktaria'],
            ['nip' => '22220319', 'nama' => 'Nailul'],
            ['nip' => '21220089', 'nama' => 'Yanti Fitria'],
            ['nip' => '22210268', 'nama' => 'Yoga Erixa'],
            ['nip' => '22190201', 'nama' => 'Selvy Sari'],
            ['nip' => '22200246', 'nama' => 'Aditya'],
            ['nip' => '21220076', 'nama' => 'Marlena'],
            ['nip' => '22220315', 'nama' => 'Dandy'],
            ['nip' => '21220120', 'nama' => 'Tri Ayu R'],
            ['nip' => '22210258', 'nama' => 'Desi Nur Fitri'],
            ['nip' => '22200235', 'nama' => 'Gilda Puspita'],
            ['nip' => '22190199', 'nama' => 'Meilinda'],
            ['nip' => '21220172', 'nama' => 'Raluki'],
            ['nip' => '21220156', 'nama' => 'Qory'],
            ['nip' => '22210267', 'nama' => 'Ratnasari'],
            ['nip' => '22190202', 'nama' => 'Susilo Sudarman'],
            ['nip' => '21220158', 'nama' => 'Siti Rohima'],
            ['nip' => '21220157', 'nama' => 'Septi Dwi Ariyawati'],
            ['nip' => '22210262', 'nama' => 'Arief Ginanjar'],
            ['nip' => '22210266', 'nama' => 'Zamayra'],
            ['nip' => '22200234', 'nama' => 'Ike Teresia'],
            ['nip' => '22200239', 'nama' => 'Anggita Dwi Puspitarini'],
            ['nip' => '22210260', 'nama' => 'Juliati'],
            ['nip' => '21220185', 'nama' => 'Fentri'],
            ['nip' => '22220321', 'nama' => 'Nopa'],
            ['nip' => '21220115', 'nama' => 'Rendi Prayoga'],
            ['nip' => '22190206', 'nama' => 'Vania Rachmania'],
            ['nip' => '21220146', 'nama' => 'Junarispep'],
            ['nip' => '21220080', 'nama' => 'Herliza'],
            ['nip' => '21220169', 'nama' => 'Fitrirahma'],
            ['nip' => '21080016', 'nama' => 'Munawaroh'],
            ['nip' => '21080007', 'nama' => 'Cecilia'],
            ['nip' => '21220121', 'nama' => 'Yunidha'],
            ['nip' => '21220107', 'nama' => 'Feli Handayani'],
            ['nip' => '21220091', 'nama' => 'Feni Fransina'],
            ['nip' => '21220125', 'nama' => 'Evi Septiyani'],
            ['nip' => '21220127', 'nama' => 'Rina Okawinda'],
            ['nip' => '21220101', 'nama' => 'Siska Vertika'],
            ['nip' => '21220113', 'nama' => 'Neng Safitri'],
            ['nip' => '21220143', 'nama' => 'Raliyan'],
            ['nip' => '24230006', 'nama' => 'Tuti Alawiyah'],
            ['nip' => '21190028', 'nama' => 'Sucipto'],
        ];

        foreach ($karyawanAbsensi as $index => $k) {
            $rId = $ruanganUgd;
            if ($index % 3 == 1) $rId = $ruanganPoliAnak;
            if ($index % 3 == 2) $rId = $ruanganMelati;
            
            $dummyPegawai[] = [
                'nip' => $k['nip'],
                'nik' => '320' . rand(1000000000000, 9999999999999),
                'nama' => $k['nama'],
                'ruangan_id' => $rId,
                'kategori_kerja' => 'shift', // shift
                'tgl_lahir' => '1995-01-01', 'hp' => '0812' . rand(10000000, 99999999),
                'prov' => '-', 'kab' => '-', 'kec' => '-', 'desa' => '-', 'alamat' => 'Alamat ' . $index,
                'agama' => 'islam', 'status' => 'tetap', 'tgl_masuk' => '2022-01-01', 'cuti' => 12,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }

        DB::table('sdm_karyawan')->insert($dummyPegawai);

        // --- Create User for Perawat UGD 1 ---
        $karyawanPerawat = Karyawan::where('nip', '22210264')->first(); // Arif Pamungkas
        if ($karyawanPerawat) {
            $userPerawat = User::updateOrCreate(
                ['email' => 'perawat@rsba.com'],
                [
                    'password' => Hash::make('1234'),
                    'karyawan_id' => $karyawanPerawat->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $userPerawat->syncRoles(['Guest']);
            
            // Berikan permission view-profile-jadwal-tugas-saya
            $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view-profile-jadwal-tugas-saya']);
            $userPerawat->givePermissionTo($perm);
        }
        // -----------------------------------------------------------

        // 8. Seed Karyawan Pendidikan
        $pendidikans = [];
        if ($karyawanSdm) {
            $pendidikans[] = [
                'karyawan_id' => $karyawanSdm->id,
                'nama' => 'Universitas Indonesia',
                'tahun_lulus' => '2018-08-20',
                'instansi' => 'Fakultas Psikologi',
                'gelar' => 'S.Psi',
                'set_gelar' => 'suffix',
                'tingkat' => 's1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if ($karyawanUmum) {
            $pendidikans[] = [
                'karyawan_id' => $karyawanUmum->id,
                'nama' => 'Politeknik Negeri',
                'tahun_lulus' => '2020-09-10',
                'instansi' => 'Teknik Logistik',
                'gelar' => 'A.Md.Log',
                'set_gelar' => 'suffix',
                'tingkat' => 'd3',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('sdm_kary_pendidikan')->insert($pendidikans);

        // 9. Seed CutiJenis
        $cutiJenis = [
            ['nama' => 'Cuti Tahunan', 'lama' => 12, 'periode' => 'Y', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Cuti Sakit', 'lama' => 3, 'periode' => 'M', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Cuti Melahirkan', 'lama' => 90, 'periode' => 'Y', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Cuti Alasan Penting', 'lama' => 5, 'periode' => 'Y', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('surat_cuti_jenis')->insert($cutiJenis);
        $cutiJenisIds = DB::table('surat_cuti_jenis')->pluck('id')->toArray();

        // 10. Seed SuratCuti
        $suratCutis = [];
        if ($karyawanUmum) {
            $suratCutis[] = [
                'karyawan_id' => $karyawanUmum->id,
                'no_surat' => 'CT-001',
                'tgl_surat' => '2026-07-01',
                'tgl_mulai' => '2026-07-10',
                'tgl_akhir' => '2026-07-12',
                'tgl_cuti' => '2026-07-10,2026-07-11,2026-07-12',
                'lama_cuti' => 3,
                'urgensi_id' => $cutiJenisIds[0], // Cuti Tahunan
                'keterangan' => 'Liburan keluarga',
                'alamat' => 'Bandung',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if ($karyawanKeuangan) {
            $suratCutis[] = [
                'karyawan_id' => $karyawanKeuangan->id,
                'no_surat' => 'CT-002',
                'tgl_surat' => '2026-07-02',
                'tgl_mulai' => '2026-07-15',
                'tgl_akhir' => '2026-07-15',
                'tgl_cuti' => '2026-07-15',
                'lama_cuti' => 1,
                'urgensi_id' => $cutiJenisIds[1], // Cuti Sakit
                'keterangan' => 'Pemeriksaan kesehatan tahunan',
                'alamat' => 'Jakarta',
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('surat_cuti')->insert($suratCutis);

        // Seed approvals for seeded cuti
        $cutiCt001 = DB::table('surat_cuti')->where('no_surat', 'CT-001')->first();
        if ($cutiCt001 && $karyawanSdm) {
            DB::table('surat_cuti_approval')->insert([
                'surat_cuti_id' => $cutiCt001->id,
                'disetujui_oleh' => $karyawanSdm->id,
                'status' => 'waiting',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $cutiCt002 = DB::table('surat_cuti')->where('no_surat', 'CT-002')->first();
        if ($cutiCt002 && $karyawanSdm) {
            DB::table('surat_cuti_approval')->insert([
                'surat_cuti_id' => $cutiCt002->id,
                'disetujui_oleh' => $karyawanSdm->id,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
                'signature_hash' => hash('sha256', 'dummy-signature-cuti-002'),
                'approved_at' => now()->toIso8601String()
            ]);
        }

        // 11. Seed SuratSp3 (Surat Perintah Pembayaran)
        $userAdmin = User::where('email', 'admin@rsba.com')->first();
        if ($userAdmin) {
            $sp3Id = DB::table('surat_sp3')->insertGetId([
                'no' => 'SP3-2026-0001',
                'tahun' => 2026,
                'tgl' => '2026-07-05',
                'rekanan' => 'CV. Jaya Abadi',
                'bayar' => 'trf',
                'keterangan' => 'Pembayaran tagihan ATK dinas umum',
                'status' => 'pending',
                'jabatan_id' => 1, // Dirut
                'created_by' => $userAdmin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('surat_sp3_details')->insert([
                [
                    'sp3_id' => $sp3Id,
                    'keterangan' => 'Kertas A4 Sinar Dunia 10 Rim',
                    'nominal' => 550000.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'sp3_id' => $sp3Id,
                    'keterangan' => 'Pena Pilot Ballpoint 5 Box',
                    'nominal' => 375000.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // 12. Seed Master Barang / Inventory
        $barangKategori = [
            ['nama' => 'Alat Tulis Kantor', 'prefix' => 'ATK', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Alat Kesehatan', 'prefix' => 'ALK', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Obat-obatan', 'prefix' => 'OBT', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Elektronik & IT', 'prefix' => 'ELK', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Furniture & Rumah Tangga', 'prefix' => 'FUR', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('um_kategori')->insert($barangKategori);
        $kategoriIds = DB::table('um_kategori')->pluck('id')->toArray();

        $barangPenyimpanan = [
            ['nama' => 'Gudang Utama', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Apotek UGD', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Gudang IT & Elektronik', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Gudang Alat Kesehatan', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Gudang Farmasi', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Apotek Rawat Jalan', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Apotek Rawat Inap', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('um_penyimpanan')->insert($barangPenyimpanan);
        $penyimpananIds = DB::table('um_penyimpanan')->pluck('id', 'nama')->toArray();

        $barangSatuan = [
            ['nama' => 'Pcs', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Box', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Botol', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('um_satuan')->insert($barangSatuan);
        $satuanIds = DB::table('um_satuan')->pluck('id')->toArray();

        $suppliers = [
            [
                'nama' => 'PT. Medika Sejahtera',
                'alamat' => 'Jl. Industri Farmasi No. 45, Karawang',
                'telp' => '021-89765432',
                'email' => 'sales@medikasejahtera.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT. Global IT Solution',
                'alamat' => 'Sudirman Central Business District Lt. 12, Jakarta',
                'telp' => '021-5551234',
                'email' => 'sales@globalit.co.id',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CV. Jaya ATK & Office',
                'alamat' => 'Jl. Pemuda No. 88, Semarang',
                'telp' => '024-7654321',
                'email' => 'info@jayaatk.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT. Furniture Indah Lestari',
                'alamat' => 'Kawasan Industri Cikarang Blok C-10, Bekasi',
                'telp' => '021-8901234',
                'email' => 'contact@furnitureindah.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT. Bio Farma Persada',
                'alamat' => 'Jl. Pasteur No. 28, Bandung',
                'telp' => '022-2033755',
                'email' => 'info@biofarmapersada.co.id',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
        DB::table('um_supplier')->insert($suppliers);
        $supplierIds = DB::table('um_supplier')->pluck('id', 'nama')->toArray();
        $supplierId = $supplierIds['PT. Medika Sejahtera'];

        // Seed Barang (Umum & Asset)
        $barangs = [
            [
                'sku' => 'SKU-001-KRT',
                'nama' => 'Kertas A4 80gr',
                'satuan_id' => $satuanIds[0], // Pcs/Rim
                'kategori_id' => $kategoriIds[0], // ATK
                'tipe' => 'umum',
                'min_stok' => 10,
                'bhp' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'SKU-002-TNS',
                'nama' => 'Tensimeter Digital',
                'satuan_id' => $satuanIds[0], // Pcs
                'kategori_id' => $kategoriIds[1], // Alkes
                'tipe' => 'asset',
                'min_stok' => 2,
                'bhp' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'SKU-003-PCM',
                'nama' => 'Paracetamol 500mg',
                'satuan_id' => $satuanIds[1], // Box
                'kategori_id' => $kategoriIds[2], // Obat
                'tipe' => 'umum',
                'min_stok' => 20,
                'bhp' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'SKU-004-DIA',
                'nama' => 'Obat Diare (Attapulgite)',
                'satuan_id' => $satuanIds[1], // Box
                'kategori_id' => $kategoriIds[2], // Obat
                'tipe' => 'umum',
                'min_stok' => 10,
                'bhp' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'SKU-005-RAM',
                'nama' => 'RAM DDR4 8GB',
                'satuan_id' => $satuanIds[0], // Pcs
                'kategori_id' => $kategoriIds[3], // Elektronik & IT
                'tipe' => 'umum',
                'min_stok' => 5,
                'bhp' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'SKU-006-TVL',
                'nama' => 'Televisi LED 32 Inch',
                'satuan_id' => $satuanIds[0], // Pcs
                'kategori_id' => $kategoriIds[3], // Elektronik & IT
                'tipe' => 'asset',
                'min_stok' => 1,
                'bhp' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'SKU-007-KEY',
                'nama' => 'Keyboard Logitech USB',
                'satuan_id' => $satuanIds[0], // Pcs
                'kategori_id' => $kategoriIds[3], // Elektronik & IT
                'tipe' => 'umum',
                'min_stok' => 5,
                'bhp' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'SKU-008-MJA',
                'nama' => 'Meja Kantor 1/2 Biro',
                'satuan_id' => $satuanIds[0], // Pcs
                'kategori_id' => $kategoriIds[4], // Furniture & Rumah Tangga
                'tipe' => 'asset',
                'min_stok' => 2,
                'bhp' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'SKU-009-KRS',
                'nama' => 'Kursi Kerja Staff',
                'satuan_id' => $satuanIds[0], // Pcs
                'kategori_id' => $kategoriIds[4], // Furniture & Rumah Tangga
                'tipe' => 'asset',
                'min_stok' => 5,
                'bhp' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'SKU-010-LMR',
                'nama' => 'Lemari Arsip Besi',
                'satuan_id' => $satuanIds[0], // Pcs
                'kategori_id' => $kategoriIds[4], // Furniture & Rumah Tangga
                'tipe' => 'asset',
                'min_stok' => 1,
                'bhp' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'SKU-011-PEN',
                'nama' => 'Pena Ballpoint Hitam',
                'satuan_id' => $satuanIds[0], // Pcs
                'kategori_id' => $kategoriIds[0], // ATK
                'tipe' => 'umum',
                'min_stok' => 20,
                'bhp' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
        DB::table('um_barang')->insert($barangs);
        $barangIds = DB::table('um_barang')->pluck('id', 'nama')->toArray();

        // 13. Seed Pembelian & Penerimaan
        $pembelianId = DB::table('um_pembelian')->insertGetId([
            'no' => 'PO-26-0001',
            'tgl' => '2026-06-25',
            'supplier_id' => $supplierId,
            'jenis' => 'langsung',
            'status_pembayaran' => 'tempo',
            'tgl_pembayaran' => '2026-07-25',
            'status' => 'selesai',
            'total' => 1250000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pemDetId = DB::table('um_pembelian_det')->insertGetId([
            'pembelian_id' => $pembelianId,
            'barang_id' => $barangIds['Tensimeter Digital'],
            'jumlah' => 5,
            'batch' => 'BATCH-TNS-01',
            'harga_satuan' => 250000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pemDetKrtId = DB::table('um_pembelian_det')->insertGetId([
            'pembelian_id' => $pembelianId,
            'barang_id' => $barangIds['Kertas A4 80gr'],
            'jumlah' => 50,
            'batch' => 'BATCH-KRT-01',
            'harga_satuan' => 45000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pemDetPcmId = DB::table('um_pembelian_det')->insertGetId([
            'pembelian_id' => $pembelianId,
            'barang_id' => $barangIds['Paracetamol 500mg'],
            'jumlah' => 100,
            'batch' => 'BATCH-PCM-01',
            'harga_satuan' => 12000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pemDetDiaId = DB::table('um_pembelian_det')->insertGetId([
            'pembelian_id' => $pembelianId,
            'barang_id' => $barangIds['Obat Diare (Attapulgite)'],
            'jumlah' => 40,
            'batch' => 'BATCH-DIA-01',
            'harga_satuan' => 15000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pemDetRamId = DB::table('um_pembelian_det')->insertGetId([
            'pembelian_id' => $pembelianId,
            'barang_id' => $barangIds['RAM DDR4 8GB'],
            'jumlah' => 15,
            'batch' => 'BATCH-RAM-01',
            'harga_satuan' => 350000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pemDetTvlId = DB::table('um_pembelian_det')->insertGetId([
            'pembelian_id' => $pembelianId,
            'barang_id' => $barangIds['Televisi LED 32 Inch'],
            'jumlah' => 3,
            'batch' => 'BATCH-TVL-01',
            'harga_satuan' => 1800000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pemDetKeyId = DB::table('um_pembelian_det')->insertGetId([
            'pembelian_id' => $pembelianId,
            'barang_id' => $barangIds['Keyboard Logitech USB'],
            'jumlah' => 20,
            'batch' => 'BATCH-KEY-01',
            'harga_satuan' => 120000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pemDetMjaId = DB::table('um_pembelian_det')->insertGetId([
            'pembelian_id' => $pembelianId,
            'barang_id' => $barangIds['Meja Kantor 1/2 Biro'],
            'jumlah' => 4,
            'batch' => 'BATCH-MJA-01',
            'harga_satuan' => 750000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pemDetKrsId = DB::table('um_pembelian_det')->insertGetId([
            'pembelian_id' => $pembelianId,
            'barang_id' => $barangIds['Kursi Kerja Staff'],
            'jumlah' => 10,
            'batch' => 'BATCH-KRS-01',
            'harga_satuan' => 450000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pemDetLmrId = DB::table('um_pembelian_det')->insertGetId([
            'pembelian_id' => $pembelianId,
            'barang_id' => $barangIds['Lemari Arsip Besi'],
            'jumlah' => 2,
            'batch' => 'BATCH-LMR-01',
            'harga_satuan' => 1500000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pemDetPenId = DB::table('um_pembelian_det')->insertGetId([
            'pembelian_id' => $pembelianId,
            'barang_id' => $barangIds['Pena Ballpoint Hitam'],
            'jumlah' => 200,
            'batch' => 'BATCH-PEN-01',
            'harga_satuan' => 3000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userVerify = User::where('email', 'admin@rsba.com')->first();
        $penerimaanId = DB::table('um_penerimaan_beli')->insertGetId([
            'tanggal' => '2026-06-28',
            'no_faktur' => 'INV-MS-98765',
            'keterangan' => 'Penerimaan alat kesehatan, BHP, meubel, IT dan obat-obatan',
            'penerima' => $userVerify->id ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penDetId = DB::table('um_penerimaan_beli_det')->insertGetId([
            'penerimaan_id' => $penerimaanId,
            'pembelian_det_id' => $pemDetId,
            'jumlah' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penDetKrtId = DB::table('um_penerimaan_beli_det')->insertGetId([
            'penerimaan_id' => $penerimaanId,
            'pembelian_det_id' => $pemDetKrtId,
            'jumlah' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penDetPcmId = DB::table('um_penerimaan_beli_det')->insertGetId([
            'penerimaan_id' => $penerimaanId,
            'pembelian_det_id' => $pemDetPcmId,
            'jumlah' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penDetDiaId = DB::table('um_penerimaan_beli_det')->insertGetId([
            'penerimaan_id' => $penerimaanId,
            'pembelian_det_id' => $pemDetDiaId,
            'jumlah' => 40,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penDetRamId = DB::table('um_penerimaan_beli_det')->insertGetId([
            'penerimaan_id' => $penerimaanId,
            'pembelian_det_id' => $pemDetRamId,
            'jumlah' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penDetTvlId = DB::table('um_penerimaan_beli_det')->insertGetId([
            'penerimaan_id' => $penerimaanId,
            'pembelian_det_id' => $pemDetTvlId,
            'jumlah' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penDetKeyId = DB::table('um_penerimaan_beli_det')->insertGetId([
            'penerimaan_id' => $penerimaanId,
            'pembelian_det_id' => $pemDetKeyId,
            'jumlah' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penDetMjaId = DB::table('um_penerimaan_beli_det')->insertGetId([
            'penerimaan_id' => $penerimaanId,
            'pembelian_det_id' => $pemDetMjaId,
            'jumlah' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penDetKrsId = DB::table('um_penerimaan_beli_det')->insertGetId([
            'penerimaan_id' => $penerimaanId,
            'pembelian_det_id' => $pemDetKrsId,
            'jumlah' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penDetLmrId = DB::table('um_penerimaan_beli_det')->insertGetId([
            'penerimaan_id' => $penerimaanId,
            'pembelian_det_id' => $pemDetLmrId,
            'jumlah' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penDetPenId = DB::table('um_penerimaan_beli_det')->insertGetId([
            'penerimaan_id' => $penerimaanId,
            'pembelian_det_id' => $pemDetPenId,
            'jumlah' => 200,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 14. Seed Stock
        $stokId = DB::table('um_stok')->insertGetId([
            'penerimaan_det_id' => $penDetId,
            'barang_id' => $barangIds['Tensimeter Digital'],
            'stok' => 5,
            'batch' => 'BATCH-TNS-01',
            'harga_satuan' => 250000.00,
            'penyimpanan_id' => $penyimpananIds['Gudang Alat Kesehatan'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stock Kertas A4 80gr (BHP) di Gudang Utama
        DB::table('um_stok')->insert([
            'penerimaan_det_id' => $penDetKrtId,
            'barang_id' => $barangIds['Kertas A4 80gr'],
            'stok' => 50,
            'batch' => 'BATCH-KRT-01',
            'harga_satuan' => 45000.00,
            'penyimpanan_id' => $penyimpananIds['Gudang Utama'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stock Paracetamol 500mg (Obat) di Gudang Farmasi
        DB::table('um_stok')->insert([
            'penerimaan_det_id' => $penDetPcmId,
            'barang_id' => $barangIds['Paracetamol 500mg'],
            'stok' => 100,
            'batch' => 'BATCH-PCM-01',
            'harga_satuan' => 12000.00,
            'penyimpanan_id' => $penyimpananIds['Gudang Farmasi'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stock Obat Diare (Attapulgite) di Gudang Farmasi
        DB::table('um_stok')->insert([
            'penerimaan_det_id' => $penDetDiaId,
            'barang_id' => $barangIds['Obat Diare (Attapulgite)'],
            'stok' => 40,
            'batch' => 'BATCH-DIA-01',
            'harga_satuan' => 15000.00,
            'penyimpanan_id' => $penyimpananIds['Gudang Farmasi'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stock RAM DDR4 8GB di Gudang Utama
        DB::table('um_stok')->insert([
            'penerimaan_det_id' => $penDetRamId,
            'barang_id' => $barangIds['RAM DDR4 8GB'],
            'stok' => 15,
            'batch' => 'BATCH-RAM-01',
            'harga_satuan' => 350000.00,
            'penyimpanan_id' => $penyimpananIds['Gudang IT & Elektronik'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stock Televisi LED 32 Inch di Gudang Utama
        DB::table('um_stok')->insert([
            'penerimaan_det_id' => $penDetTvlId,
            'barang_id' => $barangIds['Televisi LED 32 Inch'],
            'stok' => 3,
            'batch' => 'BATCH-TVL-01',
            'harga_satuan' => 1800000.00,
            'penyimpanan_id' => $penyimpananIds['Gudang IT & Elektronik'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stock Keyboard Logitech USB di Gudang Utama
        DB::table('um_stok')->insert([
            'penerimaan_det_id' => $penDetKeyId,
            'barang_id' => $barangIds['Keyboard Logitech USB'],
            'stok' => 20,
            'batch' => 'BATCH-KEY-01',
            'harga_satuan' => 120000.00,
            'penyimpanan_id' => $penyimpananIds['Gudang IT & Elektronik'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stock Meja Kantor 1/2 Biro di Gudang Utama
        DB::table('um_stok')->insert([
            'penerimaan_det_id' => $penDetMjaId,
            'barang_id' => $barangIds['Meja Kantor 1/2 Biro'],
            'stok' => 4,
            'batch' => 'BATCH-MJA-01',
            'harga_satuan' => 750000.00,
            'penyimpanan_id' => $penyimpananIds['Gudang Utama'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stock Kursi Kerja Staff di Gudang Utama
        DB::table('um_stok')->insert([
            'penerimaan_det_id' => $penDetKrsId,
            'barang_id' => $barangIds['Kursi Kerja Staff'],
            'stok' => 10,
            'batch' => 'BATCH-KRS-01',
            'harga_satuan' => 450000.00,
            'penyimpanan_id' => $penyimpananIds['Gudang Utama'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stock Lemari Arsip Besi di Gudang Utama
        DB::table('um_stok')->insert([
            'penerimaan_det_id' => $penDetLmrId,
            'barang_id' => $barangIds['Lemari Arsip Besi'],
            'stok' => 2,
            'batch' => 'BATCH-LMR-01',
            'harga_satuan' => 1500000.00,
            'penyimpanan_id' => $penyimpananIds['Gudang Utama'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Stock Pena Ballpoint Hitam di Gudang Utama
        DB::table('um_stok')->insert([
            'penerimaan_det_id' => $penDetPenId,
            'barang_id' => $barangIds['Pena Ballpoint Hitam'],
            'stok' => 200,
            'batch' => 'BATCH-PEN-01',
            'harga_satuan' => 3000.00,
            'penyimpanan_id' => $penyimpananIds['Gudang Utama'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 15. Seed Distribusi
        $distribusiId = DB::table('um_distribusi')->insertGetId([
            'tanggal' => '2026-06-30',
            'tujuan' => $ruanganIds[0], // UGD
            'pengirim' => $userVerify->id ?? 1,
            'penerima' => $karyawanUmum->id ?? 1,
            'dist_as' => 'medis',
            'keterangan' => 'Alokasi tensimeter baru untuk operasional UGD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $distDetId = DB::table('um_distribusi_det')->insertGetId([
            'distribusi_id' => $distribusiId,
            'stok_id' => $stokId,
            'jml' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 16. Seed Asset Barang
        $assetId = DB::table('asset_barang')->insertGetId([
            'distribusi_det_id' => $distDetId,
            'barang_id' => $barangIds['Tensimeter Digital'],
            'ruangan_id' => $ruanganIds[0], // UGD
            'kode' => 'AST-TNS-UGD-001',
            'tanggal_catat' => '2026-06-30',
            'nilai' => 250000.00,
            'status' => 'baik',
            'keterangan' => 'Unit Tensimeter Digital Kamar Tindakan 1 UGD',
            'jenis' => 'main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Asset Specs
        DB::table('asset_specs')->insert([
            ['asset_id' => $assetId, 'label' => 'Brand', 'value' => 'Omron', 'created_at' => now(), 'updated_at' => now()],
            ['asset_id' => $assetId, 'label' => 'Model', 'value' => 'HEM-7156', 'created_at' => now(), 'updated_at' => now()],
            ['asset_id' => $assetId, 'label' => 'SN', 'value' => 'SN-OMR-7156-88902', 'created_at' => now(), 'updated_at' => now()],
            ['asset_id' => $assetId, 'label' => 'Power', 'value' => 'Baterai AA', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 17. Seed Maintenance & Work
        $reqId = DB::table('asset_maintc_requests')->insertGetId([
            'asset_id' => $assetId,
            'user_req_id' => $userVerify->id ?? 1,
            'note' => 'Manset tensimeter bocor halus saat memompa tekanan tinggi',
            'priority' => 'penting',
            'ket_priority' => 'Mengganggu pelayanan pasien UGD',
            'status' => 'approved',
            'user_verify_id' => $userVerify->id ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $jadwalId = DB::table('asset_maintc_jadwal')->insertGetId([
            'asset_id' => $assetId,
            'maintc_request_id' => $reqId,
            'tanggal' => '2026-07-06',
            'priority' => 'penting',
            'note' => 'Penjadwalan teknisi elektro medis',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Teknisi Assignment
        $userSdm = User::where('email', 'sdm@rsba.com')->first();
        DB::table('asset_maintc_teknisi_assigment')->insert([
            'maintc_jadwal_id' => $jadwalId,
            'teknisi_id' => $userSdm->id ?? 1,
            'role' => 'leader',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $workId = DB::table('asset_maintc_work')->insertGetId([
            'asset_id' => $assetId,
            'maintc_jadwal_id' => $jadwalId,
            'mulai' => '2026-07-06 09:00:00',
            'mulai_by' => $userSdm->id ?? 1,
            'selesai' => '2026-07-06 10:30:00',
            'selesai_by' => $userSdm->id ?? 1,
            'catatan' => 'Mengganti komponen manset yang robek dengan unit cadangan',
            'status' => 'done',
            'total_biaya' => 75000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Work Parts
        DB::table('asset_maintc_work_parts')->insert([
            'maintc_work_id' => $workId,
            'barang_id' => $barangIds['Kertas A4 80gr'], // Dummy part
            'for' => 'bhp',
            'qty' => 1.00,
            'harga_satuan' => 75000.00,
            'status' => 'distributed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 18. Seed Dokter & Jasa Medis (Payroll / Doctor Fees)
        $dokterSpesialisasi = [
            ['nama' => 'Spesialis Anak', 'singkatan' => 'Sp.A', 'kategori' => 'spesialis', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Spesialis Bedah', 'singkatan' => 'Sp.B', 'kategori' => 'spesialis', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('dokter_spesialisasi')->insert($dokterSpesialisasi);
        $spesialisIds = DB::table('dokter_spesialisasi')->pluck('id')->toArray();

        // Create Dokter Karyawan record
        $karyawanDokter = Karyawan::firstOrCreate(
            ['nip' => '9999999999'],
            [
                'nik' => '9999999999999999',
                'nama' => 'Dr. John Doe, Sp.A',
                'tgl_lahir' => '1980-05-15',
                'hp' => '081234567890',
                'prov' => 'Jawa Barat',
                'kab' => 'Bandung',
                'kec' => 'Coblong',
                'desa' => 'Dago',
                'alamat' => 'Jl. Dago Asri No. 10',
                'agama' => 'islam',
                'status' => 'tetap',
                'tgl_masuk' => '2015-01-01',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $dokterId = DB::table('dokter')->insertGetId([
            'karyawan_id' => $karyawanDokter->id,
            'spesialis_id' => $spesialisIds[0], // Anak
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed JmPasien
        $jmPasienId = DB::table('jm_pasien')->insertGetId([
            'nama_pasien' => 'An. Budi Prasetyo',
            'no_rekmedis' => 'RM-26-04122',
            'tgl_checkin' => '2026-07-01',
            'tgl_checkout' => '2026-07-04',
            'dpjp' => 'Dr. John Doe, Sp.A',
            'layanan' => 'ranap',
            'cabar' => 'bpjs',
            'sep' => 'SEP-0023411-2026',
            'klaim' => 4500000,
            'tarif_rs' => 4200000,
            'kelas_rawat' => 1,
            'diaglist' => 'A90 (Demam Berdarah)',
            'proclist' => '99.18 (Infus Cairan)',
            'deskripsi_inacbg' => 'Penyakit Infeksi Sistemik Ringan',
            'disetujui' => 1,
            'batch' => 1,
            'kelompok' => 'ri_no',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed JmDokter
        DB::table('jm_dokter')->insert([
            'jm_pasien_id' => $jmPasienId,
            'dokter' => 'Dr. John Doe, Sp.A',
            'jumlah' => 1500000,
            'status' => 'dpjp',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 19. Seed Pembelian Requests
        $userSdm = User::where('email', 'sdm@rsba.com')->first();
        
        $req1Id = DB::table('um_pembelian_requests')->insertGetId([
            'user_req_id' => $userSdm->id ?? 1,
            'note' => 'Pengajuan Kertas A4 untuk operasional kantor SDM',
            'priority' => 'normal',
            'status' => 'pending',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
        
        DB::table('um_pembelian_requests_det')->insert([
            'pembelian_req_id' => $req1Id,
            'barang_id' => $barangIds['Kertas A4 80gr'],
            'jml_req' => 10,
            'jml_disetujui' => 0,
            'harga_est' => 50000.00,
            'keterangan' => 'Stok kertas menipis',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $req2Id = DB::table('um_pembelian_requests')->insertGetId([
            'user_req_id' => $userSdm->id ?? 1,
            'note' => 'Kebutuhan darurat obat Paracetamol untuk apotek',
            'priority' => 'darurat',
            'status' => 'pending',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);
        
        DB::table('um_pembelian_requests_det')->insert([
            'pembelian_req_id' => $req2Id,
            'barang_id' => $barangIds['Paracetamol 500mg'],
            'jml_req' => 50,
            'jml_disetujui' => 0,
            'harga_est' => 15000.00,
            'keterangan' => 'Permintaan mendesak',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        // 20. Seed Data Dummy Akreditasi
        $userAdmin = User::where('email', 'admin@rsba.com')->first();
        $userSdm   = User::where('email', 'sdm@rsba.com')->first();

        // 20a. Kegiatan Akreditasi
        $kegiatanId = DB::table('akre_kegiatan')->insertGetId([
            'uuid'        => \Illuminate\Support\Str::uuid(),
            'tanggal'     => '2026-07-01',
            'nama'        => 'Akreditasi RS - Siklus 2026',
            'standard'    => 'SNARS Edisi 1.1',
            'lembaga'     => 'KARS',
            'total_nilai' => 0,
            'folder_path' => 'akreditasi/2026',
            'created_by'  => $userAdmin->id ?? 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // 20b. Chapters (BAB Utama)
        $chapters = [
            [
                'kegiatan_id'    => $kegiatanId,
                'nama'           => 'Kelompok Standar Pelayanan Berfokus pada Pasien',
                'singkatan'      => 'KPBP',
                'deskripsi'      => 'Standar yang mengatur pelayanan langsung kepada pasien di rumah sakit.',
                'pic_id'         => $userAdmin->id ?? 1,
                'assesor_id'     => $userSdm->id ?? 1,
                'total_element'  => 0,
                'total_nilai'    => 0,
                'folder_path'    => 'akreditasi/2026/kpbp',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'kegiatan_id'    => $kegiatanId,
                'nama'           => 'Kelompok Standar Manajemen Rumah Sakit',
                'singkatan'      => 'KMRS',
                'deskripsi'      => 'Standar yang mengatur tata kelola dan manajemen operasional rumah sakit.',
                'pic_id'         => $userSdm->id ?? 1,
                'assesor_id'     => $userAdmin->id ?? 1,
                'total_element'  => 0,
                'total_nilai'    => 0,
                'folder_path'    => 'akreditasi/2026/kmrs',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ];
        DB::table('akre_chapter')->insert($chapters);
        $chapterIds = DB::table('akre_chapter')->pluck('id')->toArray();
        [$chapterKpbpId, $chapterKmrsId] = $chapterIds;

        // 20c. Bab & Sub-Bab (akre_bab_elements)
        // -- BAB 1: Akses ke Rumah Sakit dan Kontinuitas Pelayanan (ARK)
        $bab1Id = DB::table('akre_bab_elements')->insertGetId([
            'chapter_id'     => $chapterKpbpId,
            'no'             => 'A',
            'nama'           => 'Akses ke Rumah Sakit dan Kontinuitas Pelayanan (ARK)',
            'deskripsi'      => 'Standar terkait akses pasien ke layanan dan kesinambungan perawatan.',
            'maksud_tujuan'  => 'Memastikan pasien mendapatkan akses layanan yang tepat dan berkesinambungan.',
            'bab'            => 'bab',
            'parent_id'      => null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Sub-Bab ARK
        $subArk1Id = DB::table('akre_bab_elements')->insertGetId([
            'chapter_id'     => $chapterKpbpId,
            'no'             => '1',
            'nama'           => 'Skrining dan Triase Pasien',
            'deskripsi'      => 'Proses skrining untuk menentukan kebutuhan pelayanan pasien.',
            'maksud_tujuan'  => 'Memastikan pasien menerima skrining yang tepat saat pertama kali datang.',
            'bab'            => 'sub',
            'parent_id'      => $bab1Id,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $subArk2Id = DB::table('akre_bab_elements')->insertGetId([
            'chapter_id'     => $chapterKpbpId,
            'no'             => '2',
            'nama'           => 'Registrasi dan Admisi Pasien',
            'deskripsi'      => 'Proses pendaftaran dan penerimaan pasien rawat inap maupun rawat jalan.',
            'maksud_tujuan'  => 'Memastikan proses registrasi pasien terstandar dan terdokumentasi.',
            'bab'            => 'sub',
            'parent_id'      => $bab1Id,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // -- BAB 2: Manajemen Sumber Daya Manusia (MSDM)
        $bab2Id = DB::table('akre_bab_elements')->insertGetId([
            'chapter_id'     => $chapterKmrsId,
            'no'             => 'B',
            'nama'           => 'Manajemen Sumber Daya Manusia (MSDM)',
            'deskripsi'      => 'Standar terkait pengelolaan SDM klinis dan non-klinis rumah sakit.',
            'maksud_tujuan'  => 'Memastikan RS memiliki SDM yang kompeten dan cukup untuk mendukung pelayanan.',
            'bab'            => 'bab',
            'parent_id'      => null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $subMsdm1Id = DB::table('akre_bab_elements')->insertGetId([
            'chapter_id'     => $chapterKmrsId,
            'no'             => '1',
            'nama'           => 'Perencanaan dan Rekrutmen SDM',
            'deskripsi'      => 'Proses perencanaan kebutuhan dan rekrutmen tenaga kesehatan.',
            'maksud_tujuan'  => 'Memastikan RS memiliki jumlah dan kualifikasi SDM yang memadai.',
            'bab'            => 'sub',
            'parent_id'      => $bab2Id,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $subMsdm2Id = DB::table('akre_bab_elements')->insertGetId([
            'chapter_id'     => $chapterKmrsId,
            'no'             => '2',
            'nama'           => 'Orientasi dan Pelatihan Staf',
            'deskripsi'      => 'Program orientasi untuk staf baru dan pelatihan berkelanjutan.',
            'maksud_tujuan'  => 'Memastikan seluruh staf mendapatkan pelatihan kompetensi yang diperlukan.',
            'bab'            => 'sub',
            'parent_id'      => $bab2Id,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // 20d. Elemen Penilaian (akre_elements)
        $elements = [
            // ARK 1 - Skrining
            [
                'akre_bab_id'   => $subArk1Id,
                'nomor'         => '1',
                'element'       => 'RS menetapkan regulasi tentang skrining pasien baik di dalam maupun di luar RS.',
                'methode'       => json_encode(['R', 'D']),
                'kelengkapan'   => 'Regulasi skrining, SOP triase, Dokumen hasil skrining pasien UGD',
                'target_nilai'  => 10,
                'nilai'         => 8,
                'tdd'           => false,
                'catatan'       => 'Regulasi sudah ada, perlu update SOP triase terbaru',
                'validated_by'  => $userAdmin->id ?? 1,
                'is_corection'  => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'akre_bab_id'   => $subArk1Id,
                'nomor'         => '2',
                'element'       => 'Pasien diskrining sesuai dengan kebutuhan pelayanan sebelum ditetapkan sebagai pasien rawat inap.',
                'methode'       => json_encode(['W', 'O', 'D']),
                'kelengkapan'   => 'Formulir skrining awal, RM pasien, Bukti edukasi skrining',
                'target_nilai'  => 10,
                'nilai'         => 10,
                'tdd'           => false,
                'catatan'       => null,
                'validated_by'  => $userAdmin->id ?? 1,
                'is_corection'  => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            // ARK 2 - Registrasi
            [
                'akre_bab_id'   => $subArk2Id,
                'nomor'         => '1',
                'element'       => 'Ada regulasi tentang penerimaan pasien rawat inap dan pendaftaran pasien rawat jalan.',
                'methode'       => json_encode(['R', 'D']),
                'kelengkapan'   => 'Regulasi admisi, SOP pendaftaran, Formulir admisi pasien',
                'target_nilai'  => 10,
                'nilai'         => 10,
                'tdd'           => false,
                'catatan'       => null,
                'validated_by'  => $userAdmin->id ?? 1,
                'is_corection'  => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'akre_bab_id'   => $subArk2Id,
                'nomor'         => '2',
                'element'       => 'Pasien dan keluarga diberikan informasi tentang pelayanan yang ditawarkan dan biaya.',
                'methode'       => json_encode(['W', 'D', 'S']),
                'kelengkapan'   => 'Bukti edukasi pasien, Formulir persetujuan umum, Informasi tarif RS',
                'target_nilai'  => 10,
                'nilai'         => 5,
                'tdd'           => false,
                'catatan'       => 'Perlu peningkatan dokumentasi edukasi kepada pasien/keluarga',
                'validated_by'  => null,
                'is_corection'  => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            // MSDM 1 - Perencanaan
            [
                'akre_bab_id'   => $subMsdm1Id,
                'nomor'         => '1',
                'element'       => 'RS mempunyai regulasi tentang pengelolaan SDM yang sesuai dengan peraturan perundang-undangan.',
                'methode'       => json_encode(['R']),
                'kelengkapan'   => 'Regulasi pengelolaan SDM, Struktur organisasi RS, Uraian jabatan',
                'target_nilai'  => 10,
                'nilai'         => 10,
                'tdd'           => false,
                'catatan'       => null,
                'validated_by'  => $userAdmin->id ?? 1,
                'is_corection'  => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'akre_bab_id'   => $subMsdm1Id,
                'nomor'         => '2',
                'element'       => 'RS melakukan analisis dan perencanaan kebutuhan SDM secara berkala.',
                'methode'       => json_encode(['R', 'D', 'W']),
                'kelengkapan'   => 'Dokumen analisis beban kerja, Rencana kebutuhan SDM tahunan',
                'target_nilai'  => 10,
                'nilai'         => 8,
                'tdd'           => false,
                'catatan'       => 'Dokumen analisis beban kerja perlu diperbarui untuk tahun berjalan',
                'validated_by'  => $userSdm->id ?? 1,
                'is_corection'  => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            // MSDM 2 - Pelatihan
            [
                'akre_bab_id'   => $subMsdm2Id,
                'nomor'         => '1',
                'element'       => 'Setiap staf baru mengikuti orientasi umum dan orientasi khusus di unit kerja.',
                'methode'       => json_encode(['D', 'W']),
                'kelengkapan'   => 'Materi orientasi, Daftar hadir orientasi, Hasil evaluasi orientasi staf baru',
                'target_nilai'  => 10,
                'nilai'         => 10,
                'tdd'           => false,
                'catatan'       => null,
                'validated_by'  => $userSdm->id ?? 1,
                'is_corection'  => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'akre_bab_id'   => $subMsdm2Id,
                'nomor'         => '2',
                'element'       => 'RS mempunyai program pelatihan dan pendidikan yang berkelanjutan bagi seluruh staf.',
                'methode'       => json_encode(['R', 'D', 'W']),
                'kelengkapan'   => 'Program diklat tahunan, Sertifikat pelatihan staf, Laporan realisasi diklat',
                'target_nilai'  => 10,
                'nilai'         => null,
                'tdd'           => false,
                'catatan'       => 'Belum divalidasi',
                'validated_by'  => null,
                'is_corection'  => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ];
        DB::table('akre_elements')->insert($elements);
    }
}
