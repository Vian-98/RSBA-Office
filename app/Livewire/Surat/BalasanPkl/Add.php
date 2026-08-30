<?php

namespace App\Livewire\Surat\BalasanPkl;

use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratBalasanPkl;
use App\Models\Surat\SuratBalasanPklMahasiswa;
use App\Models\Surat\SuratTarifPkl;
use App\Models\Surat\SuratTemplateNomor;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

class Add extends Component
{
    use Interactions;

    public $tgl;
    public $tujuan_nama = '';
    public $tujuan_universitas = '';
    public $tujuan_alamat = '';
    public $nomor_surat_masuk = '';
    public $tgl_surat_masuk;
    public $prodi = '';
    public $jumlah_mahasiswa = 1;
    public $lama_praktik_bulan = 1;
    public bool $is_manual_bulan = false;
    public $tgl_mulai;
    public $tgl_selesai;

    // Snapshot Tarif
    public $snap_biaya_praktik = 150000;
    public $snap_biaya_orientasi = 50000;
    public $snap_nomor_sk = '';

    // Direktur Signatory
    public array $direkturOptions = [];
    public ?int $selectedDirekturIndex = 0;
    public ?int $jabatan_id = null;
    public ?int $disetujui_oleh = null;

    // Daftar Mahasiswa
    public array $mahasiswaList = [
        ['nama' => '', 'npm' => '']
    ];

    public int $total_hari = 30;

    protected $rules = [
        'tgl'                => 'required|date',
        'tujuan_universitas' => 'required|string|max:150',
        'prodi'              => 'required|string|max:100',
        'jumlah_mahasiswa'   => 'required|integer|min:1',
        'lama_praktik_bulan' => 'required|integer|min:1',
        'tgl_mulai'          => 'required|date',
        'tgl_selesai'        => 'required|date|after_or_equal:tgl_mulai',
        'snap_biaya_praktik' => 'required|numeric|min:0',
        'mahasiswaList'      => 'required|array|min:1',
        'mahasiswaList.*.nama' => 'required|string|max:150',
    ];

    protected $messages = [
        'tujuan_universitas.required' => 'Nama universitas wajib diisi.',
        'prodi.required'              => 'Program studi wajib diisi.',
        'mahasiswaList.*.nama.required' => 'Nama mahasiswa wajib diisi.',
    ];

    public function mount()
    {
        $this->tgl = date('Y-m-d');
        $this->tgl_surat_masuk = date('Y-m-d');
        $this->tgl_mulai = date('Y-m-d');
        $this->tgl_selesai = date('Y-m-d', strtotime('+29 days'));

        $this->recalculateBulan();

        // Load active tariff snapshot
        $tarif = SuratTarifPkl::getAktif();
        $this->snap_biaya_praktik   = $tarif->biaya_praktik_per_bulan;
        $this->snap_biaya_orientasi = $tarif->biaya_orientasi_per_orang;
        $this->snap_nomor_sk        = $tarif->nomor_sk;

        // Load Direktur options
        $this->direkturOptions = Jabatan::getDirekturList();
        if (!empty($this->direkturOptions)) {
            $this->selectDirektur(0);
        }
    }

    public function updatedTglMulai()
    {
        if (!$this->is_manual_bulan) {
            $this->recalculateBulan();
        }
    }

    public function updatedTglSelesai()
    {
        if (!$this->is_manual_bulan) {
            $this->recalculateBulan();
        }
    }

    public function setPresetBulan(int $bulan)
    {
        $start = $this->tgl_mulai ? \Carbon\Carbon::parse($this->tgl_mulai) : now();
        $days = ($bulan * 30) - 1;
        $this->tgl_selesai = $start->copy()->addDays($days)->format('Y-m-d');
        $this->is_manual_bulan = false;
        $this->recalculateBulan();
    }

    public function toggleManualBulan()
    {
        $this->is_manual_bulan = !$this->is_manual_bulan;
        if (!$this->is_manual_bulan) {
            $this->recalculateBulan();
        }
    }

    public function recalculateBulan()
    {
        if (!$this->tgl_mulai || !$this->tgl_selesai) {
            $this->total_hari = 30;
            $this->lama_praktik_bulan = 1;
            return;
        }

        try {
            $start = \Carbon\Carbon::parse($this->tgl_mulai)->startOfDay();
            $end = \Carbon\Carbon::parse($this->tgl_selesai)->startOfDay();

            if ($end->lt($start)) {
                $this->total_hari = 1;
                $this->lama_praktik_bulan = 1;
                return;
            }

            // Hitung total hari inklusif (tanggal mulai & tanggal selesai keduanya dihitung)
            $this->total_hari = (int) ($start->diffInDays($end) + 1);

            // Rumus kelipatan 30 hari:
            // <= 30 hari = 1 bulan, 31-60 hari = 2 bulan, 61-90 hari = 3 bulan, dst.
            $this->lama_praktik_bulan = (int) max(1, ceil($this->total_hari / 30));
        } catch (\Throwable $e) {
            $this->total_hari = 30;
            $this->lama_praktik_bulan = 1;
        }
    }

    public function selectDirektur(int $index)
    {
        $this->selectedDirekturIndex = $index;
        if (isset($this->direkturOptions[$index])) {
            $this->jabatan_id     = $this->direkturOptions[$index]['jabatan_id'] ?? null;
            $this->disetujui_oleh = $this->direkturOptions[$index]['karyawan_id'] ?? null;
        }
    }

    public function addMahasiswa()
    {
        $this->mahasiswaList[] = ['nama' => '', 'npm' => ''];
        $this->jumlah_mahasiswa = count($this->mahasiswaList);
    }

    public function removeMahasiswa(int $index)
    {
        if (count($this->mahasiswaList) > 1) {
            unset($this->mahasiswaList[$index]);
            $this->mahasiswaList = array_values($this->mahasiswaList);
            $this->jumlah_mahasiswa = count($this->mahasiswaList);
        }
    }

    public function updatedMahasiswaList()
    {
        $this->jumlah_mahasiswa = max(1, count($this->mahasiswaList));
    }

    public function submit()
    {
        $this->validate();

        // Hitung next sequence number
        $year = date('Y', strtotime($this->tgl));
        $lastSurat = SuratBalasanPkl::where('tahun', $year)->orderBy('id', 'desc')->first();
        $nextNo = 1;
        if ($lastSurat && preg_match('/^(\d+)\//', $lastSurat->no, $matches)) {
            $nextNo = ((int) $matches[1]) + 1;
        }

        $jabatanId = ($this->jabatan_id && Jabatan::where('id', $this->jabatan_id)->exists()) ? $this->jabatan_id : null;
        $disetujuiOleh = ($this->disetujui_oleh && Karyawan::where('id', $this->disetujui_oleh)->exists()) ? $this->disetujui_oleh : null;

        $formattedNo = SuratTemplateNomor::generateNomor(
            'balasan_pkl',
            $jabatanId,
            $this->tgl,
            $nextNo
        );

        DB::beginTransaction();
        try {
            $surat = SuratBalasanPkl::create([
                'no'                   => $formattedNo,
                'tahun'                => $year,
                'tgl'                  => $this->tgl,
                'tujuan_nama'          => $this->tujuan_nama,
                'tujuan_universitas'   => $this->tujuan_universitas,
                'tujuan_alamat'        => $this->tujuan_alamat,
                'nomor_surat_masuk'    => $this->nomor_surat_masuk,
                'tgl_surat_masuk'      => $this->tgl_surat_masuk,
                'prodi'                => $this->prodi,
                'jumlah_mahasiswa'     => $this->jumlah_mahasiswa,
                'lama_praktik_bulan'   => $this->lama_praktik_bulan,
                'tgl_mulai'            => $this->tgl_mulai,
                'tgl_selesai'          => $this->tgl_selesai,
                'snap_biaya_praktik'   => $this->snap_biaya_praktik,
                'snap_biaya_orientasi' => $this->snap_biaya_orientasi,
                'snap_nomor_sk'        => $this->snap_nomor_sk,
                'jabatan_id'           => $jabatanId,
                'disetujui_oleh'       => $disetujuiOleh,
                'status'               => 'pending',
                'created_by'           => auth()->id(),
            ]);

            foreach ($this->mahasiswaList as $mhs) {
                if (!empty(trim($mhs['nama']))) {
                    SuratBalasanPklMahasiswa::create([
                        'surat_balasan_pkl_id' => $surat->id,
                        'nama'                 => $mhs['nama'],
                        'npm'                  => $mhs['npm'] ?? null,
                    ]);
                }
            }

            DB::commit();

            // Sync langsung ke Docstore Bank Surat
            app(\App\Services\DocumentSignatureService::class)->triggerDocstoreSync($surat->fresh());

            $this->dispatch('refresh-table-balasan-pkl');
            $this->dispatch('close-modal', id: 'modal-add-balasan-pkl');

            $this->toast()
                ->success('Berhasil', "Surat Balasan PKL {$surat->no} berhasil dibuat dan menunggu persetujuan Direktur.")
                ->send();

            $this->resetForm();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast()
                ->error('Gagal', 'Error: ' . $th->getMessage())
                ->send();
        }
    }

    public function resetForm()
    {
        $this->tujuan_nama = '';
        $this->tujuan_universitas = '';
        $this->tujuan_alamat = '';
        $this->nomor_surat_masuk = '';
        $this->prodi = '';
        $this->mahasiswaList = [['nama' => '', 'npm' => '']];
        $this->jumlah_mahasiswa = 1;
        $this->is_manual_bulan = false;
        $this->recalculateBulan();
    }

    public function getTotalEstimasiProperty(): float
    {
        $biayaPraktik = (float) ($this->snap_biaya_praktik ?: 0);
        $mhs = (int) ($this->jumlah_mahasiswa ?: 1);
        $bulan = (int) ($this->lama_praktik_bulan ?: 1);
        $biayaOrientasi = (float) ($this->snap_biaya_orientasi ?: 0);

        return ($biayaPraktik * $mhs * $bulan) + ($biayaOrientasi * $mhs);
    }

    public function render()
    {
        return view('livewire.surat.balasan-pkl.add');
    }
}
