<?php

namespace App\Livewire\Surat\PerintahTugas;

use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratPerintahTugas;
use App\Models\Surat\SuratPerintahTugasKaryawan;
use App\Models\Surat\SuratTemplateNomor;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

class Add extends Component
{
    use Interactions;

    public $tgl;
    public $perihal = '';
    public $hari_tanggal = '';
    public $waktu = '08:00 WIB s.d Selesai';
    public $tempat = '';

    // Direktur Signatory
    public array $direkturOptions = [];
    public ?int $selectedDirekturIndex = 0;
    public ?int $jabatan_id = null;
    public ?int $disetujui_oleh = null;

    // Multi-select Karyawan (min 1)
    public array $selectedKaryawanIds = [];
    public array $karyawanList = [];

    protected $rules = [
        'tgl'                 => 'required|date',
        'perihal'             => 'required|string|max:500',
        'hari_tanggal'        => 'required|string|max:150',
        'waktu'               => 'required|string|max:100',
        'tempat'              => 'required|string|max:255',
        'selectedKaryawanIds' => 'required|array|min:1',
    ];

    protected $messages = [
        'selectedKaryawanIds.required' => 'Pilih minimal 1 karyawan yang ditugaskan.',
        'selectedKaryawanIds.min'      => 'Pilih minimal 1 karyawan yang ditugaskan.',
        'perihal.required'             => 'Isi perintah tugas wajib diisi.',
    ];

    public function mount()
    {
        $this->tgl = date('Y-m-d');
        $this->hari_tanggal = \Carbon\Carbon::now()->translatedFormat('l / d F Y');

        // Load Direktur options
        $this->direkturOptions = Jabatan::getDirekturList();
        if (!empty($this->direkturOptions)) {
            $this->selectDirektur(0);
        }

        // Load Karyawan list for selector
        $this->karyawanList = Karyawan::with('jabatan')
            ->orderBy('nama')
            ->get()
            ->map(function ($k) {
                $jabatanNama = optional($k->jabatan->first())->nama ?? '-';
                return [
                    'id'    => $k->id,
                    'label' => "{$k->full_nama} (NIP: " . ($k->nip ?: '-') . " — {$jabatanNama})",
                    'nama'  => $k->full_nama,
                    'nip'   => $k->nip ?: '-',
                    'jabatan' => $jabatanNama,
                ];
            })
            ->toArray();
    }

    public function selectDirektur(int $index)
    {
        $this->selectedDirekturIndex = $index;
        if (isset($this->direkturOptions[$index])) {
            $this->jabatan_id     = $this->direkturOptions[$index]['jabatan_id'];
            $this->disetujui_oleh = $this->direkturOptions[$index]['karyawan_id'];
        }
    }

    public function toggleKaryawan(int $karyawanId)
    {
        if (in_array($karyawanId, $this->selectedKaryawanIds)) {
            $this->selectedKaryawanIds = array_values(array_diff($this->selectedKaryawanIds, [$karyawanId]));
        } else {
            $this->selectedKaryawanIds[] = $karyawanId;
        }
    }

    public function removeKaryawan(int $karyawanId)
    {
        $this->selectedKaryawanIds = array_values(array_diff($this->selectedKaryawanIds, [$karyawanId]));
    }

    public function submit()
    {
        $this->validate();

        $year = date('Y', strtotime($this->tgl));
        $lastSurat = SuratPerintahTugas::where('tahun', $year)->orderBy('id', 'desc')->first();
        $nextNo = 1;
        if ($lastSurat && preg_match('/^(\d+)\//', $lastSurat->no, $matches)) {
            $nextNo = ((int) $matches[1]) + 1;
        }

        $formattedNo = SuratTemplateNomor::generateNomor(
            'perintah_tugas',
            $this->jabatan_id,
            $this->tgl,
            $nextNo
        );

        DB::beginTransaction();
        try {
            $surat = SuratPerintahTugas::create([
                'no'             => $formattedNo,
                'tahun'          => $year,
                'tgl'            => $this->tgl,
                'perihal'        => $this->perihal,
                'hari_tanggal'   => $this->hari_tanggal,
                'waktu'          => $this->waktu,
                'tempat'         => $this->tempat,
                'jabatan_id'     => $this->jabatan_id,
                'disetujui_oleh' => $this->disetujui_oleh,
                'status'         => 'pending',
                'created_by'     => auth()->id(),
            ]);

            foreach ($this->selectedKaryawanIds as $kId) {
                SuratPerintahTugasKaryawan::create([
                    'surat_perintah_tugas_id' => $surat->id,
                    'karyawan_id'             => $kId,
                ]);
            }

            DB::commit();

            $this->dispatch('refresh-table-perintah-tugas');
            $this->dispatch('close-modal', id: 'modal-add-perintah-tugas');

            $this->toast()
                ->success('Berhasil', "Surat Perintah Tugas {$surat->no} berhasil dibuat dan menunggu persetujuan Direktur.")
                ->send();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast()->error('Gagal', 'Error: ' . $th->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.surat.perintah-tugas.add');
    }
}
