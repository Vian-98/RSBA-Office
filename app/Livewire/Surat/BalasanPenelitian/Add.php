<?php

namespace App\Livewire\Surat\BalasanPenelitian;

use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratBalasanPenelitian;
use App\Models\Surat\SuratBalasanPenelitianBiaya;
use App\Models\Surat\SuratBalasanPenelitianMahasiswa;
use App\Models\Surat\SuratTarifPenelitian;
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
    public $tujuan_fakultas = '';
    public $tujuan_universitas = '';
    public $tujuan_alamat = '';
    public $nomor_surat_masuk = '';
    public $tgl_surat_masuk;
    public $perihal_surat_masuk = '';

    // Direktur Signatory
    public array $direkturOptions = [];
    public ?int $selectedDirekturIndex = 0;
    public ?int $jabatan_id = null;
    public ?int $disetujui_oleh = null;

    // Daftar Mahasiswa
    public array $mahasiswaList = [
        ['nama' => '', 'npm' => '', 'fakultas_pt' => '', 'judul_penelitian' => '']
    ];

    // Rincian Biaya
    public array $biayaList = [];

    protected $rules = [
        'tgl'                => 'required|date',
        'tujuan_universitas' => 'required|string|max:150',
        'mahasiswaList'      => 'required|array|min:1',
        'mahasiswaList.*.nama' => 'required|string|max:150',
        'biayaList'          => 'required|array|min:1',
        'biayaList.*.keterangan' => 'required|string|max:150',
    ];

    protected $messages = [
        'tujuan_universitas.required' => 'Nama universitas / instansi wajib diisi.',
        'mahasiswaList.*.nama.required' => 'Nama peneliti / mahasiswa wajib diisi.',
        'biayaList.*.keterangan.required' => 'Keterangan biaya wajib diisi.',
    ];

    public function mount()
    {
        $this->tgl = date('Y-m-d');
        $this->tgl_surat_masuk = date('Y-m-d');

        // Load standard default tariff
        $tarif = SuratTarifPenelitian::latest('id')->first();
        if ($tarif) {
            $this->biayaList = [
                [
                    'keterangan'     => $tarif->jenis_penelitian,
                    'jumlah_orang'   => 1,
                    'jasa_sarana'    => $tarif->jasa_sarana,
                    'jasa_pelayanan' => $tarif->jasa_pelayanan,
                ]
            ];
        }

        // Load Direktur options
        $this->direkturOptions = Jabatan::getDirekturList();
        if (!empty($this->direkturOptions)) {
            $this->selectDirektur(0);
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
        $fakultasPtDefault = trim($this->tujuan_fakultas . ' / ' . $this->tujuan_universitas, ' /');
        $this->mahasiswaList[] = [
            'nama'             => '',
            'npm'              => '',
            'fakultas_pt'      => $fakultasPtDefault,
            'judul_penelitian' => ''
        ];
        $this->syncJumlahOrangBiaya();
    }

    public function removeMahasiswa(int $index)
    {
        if (count($this->mahasiswaList) > 1) {
            unset($this->mahasiswaList[$index]);
            $this->mahasiswaList = array_values($this->mahasiswaList);
            $this->syncJumlahOrangBiaya();
        }
    }

    public function addBiaya()
    {
        $this->biayaList[] = [
            'keterangan'     => '',
            'jumlah_orang'   => count($this->mahasiswaList),
            'jasa_sarana'    => 0,
            'jasa_pelayanan' => 0,
        ];
    }

    public function removeBiaya(int $index)
    {
        if (count($this->biayaList) > 1) {
            unset($this->biayaList[$index]);
            $this->biayaList = array_values($this->biayaList);
        }
    }

    public function getTotalEstimasiProperty(): float
    {
        $total = 0;
        foreach ($this->biayaList as $b) {
            $jml = (int) ($b['jumlah_orang'] ?? 1);
            $sarana = (double) ($b['jasa_sarana'] ?? 0);
            $pelayanan = (double) ($b['jasa_pelayanan'] ?? 0);
            $total += ($sarana + $pelayanan) * $jml;
        }
        return $total;
    }

    public function submit()
    {
        $this->validate();

        $year = date('Y', strtotime($this->tgl));
        $lastSurat = SuratBalasanPenelitian::where('tahun', $year)->orderBy('id', 'desc')->first();
        $nextNo = 1;
        if ($lastSurat && preg_match('/^(\d+)\//', $lastSurat->no, $matches)) {
            $nextNo = ((int) $matches[1]) + 1;
        }

        $jabatanId = ($this->jabatan_id && Jabatan::where('id', $this->jabatan_id)->exists()) ? $this->jabatan_id : null;
        $disetujuiOleh = ($this->disetujui_oleh && Karyawan::where('id', $this->disetujui_oleh)->exists()) ? $this->disetujui_oleh : null;

        $formattedNo = SuratTemplateNomor::generateNomor(
            'balasan_penelitian',
            $jabatanId,
            $this->tgl,
            $nextNo
        );

        DB::beginTransaction();
        try {
            $surat = SuratBalasanPenelitian::create([
                'no'                  => $formattedNo,
                'tahun'               => $year,
                'tgl'                 => $this->tgl,
                'tujuan_nama'         => $this->tujuan_nama,
                'tujuan_fakultas'     => $this->tujuan_fakultas,
                'tujuan_universitas'  => $this->tujuan_universitas,
                'tujuan_alamat'       => $this->tujuan_alamat,
                'nomor_surat_masuk'   => $this->nomor_surat_masuk,
                'tgl_surat_masuk'     => $this->tgl_surat_masuk,
                'perihal_surat_masuk' => $this->perihal_surat_masuk,
                'jabatan_id'          => $jabatanId,
                'disetujui_oleh'      => $disetujuiOleh,
                'status'              => 'pending',
                'created_by'          => auth()->id(),
            ]);

            foreach ($this->mahasiswaList as $mhs) {
                if (!empty(trim($mhs['nama']))) {
                    SuratBalasanPenelitianMahasiswa::create([
                        'surat_balasan_penelitian_id' => $surat->id,
                        'nama'                        => $mhs['nama'],
                        'npm'                         => $mhs['npm'] ?? null,
                        'fakultas_pt'                 => $mhs['fakultas_pt'] ?: ($this->tujuan_fakultas . ' / ' . $this->tujuan_universitas),
                        'judul_penelitian'            => $mhs['judul_penelitian'] ?? null,
                    ]);
                }
            }

            foreach ($this->biayaList as $b) {
                SuratBalasanPenelitianBiaya::create([
                    'surat_balasan_penelitian_id' => $surat->id,
                    'keterangan'                  => $b['keterangan'] ?? 'Biaya Penelitian',
                    'jumlah_orang'                => $b['jumlah_orang'] ?? 1,
                    'jasa_sarana'                 => (double) ($b['jasa_sarana'] ?? 0),
                    'jasa_pelayanan'              => (double) ($b['jasa_pelayanan'] ?? 0),
                ]);
            }

            DB::commit();

            // Sync langsung ke Docstore Bank Surat
            app(\App\Services\DocumentSignatureService::class)->triggerDocstoreSync($surat->fresh());

            $this->dispatch('refresh-table-balasan-penelitian');
            $this->dispatch('close-modal', id: 'modal-add-balasan-penelitian');

            $this->toast()
                ->success('Berhasil', "Surat Balasan Penelitian {$surat->no} berhasil dibuat dan menunggu persetujuan Direktur.")
                ->send();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast()->error('Gagal', 'Error: ' . $th->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.surat.balasan-penelitian.add');
    }
}
