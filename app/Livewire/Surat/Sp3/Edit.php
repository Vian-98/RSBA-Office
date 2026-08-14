<?php

namespace App\Livewire\Surat\Sp3;

use Throwable;
use App\Enums\StatusApproval;
use App\Models\Master\Supplier;
use App\Models\Sdm\Jabatan;
use App\Models\Surat\SuratSp3;
use App\Models\Surat\SuratSp3Detail;
use App\Services\DigitalSignatureService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class Edit extends Component
{
    use Interactions;

    #[Locked]
    public ?SuratSp3 $suratSp3 = null;

    public array $caraBayarOptions = [
        ['label' => 'Tunai', 'value' => 'tunai'],
        ['label' => 'Transfer', 'value' => 'trf'],
        ['label' => 'Giro', 'value' => 'giro'],
    ];

    public $mengetahuiOptions;
    public $tgl;
    public ?string $rekanan = null, $keterangan = '', $method_bayar = 'tunai';
    public ?int $mengetahui = null, $jabatan = null, $rekananId = null, $userApprove = null, $verifikator_keuangan_id = null;
    public bool $isRekanan = true;
    public string $createTerm = '';
    public $listSp3 = [];

    protected $rules = [
        'tgl'                     => 'required',
        'rekanan'                 => 'required',
        'method_bayar'            => 'required',
        'keterangan'              => 'required',
        'jabatan'                 => 'required',
        'verifikator_keuangan_id' => 'required',
        'listSp3'                 => 'required|array|min:1',
    ];

    public function messages()
    {
        return [
            'tgl.required'                     => 'Tanggal surat wajib diisi.',
            'rekanan.required'                 => 'Rekanan / Supplier wajib diisi atau dipilih.',
            'method_bayar.required'            => 'Metode bayar wajib dipilih.',
            'keterangan.required'              => 'Subject / Keterangan wajib diisi.',
            'jabatan.required'                 => 'Mengetahui (Atasan TTD) wajib dipilih.',
            'verifikator_keuangan_id.required' => 'Verifikator keuangan wajib dipilih.',
            'listSp3.required'                 => 'Rincikan item pembayarannya.',
            'listSp3.min'                      => 'Silahkan rincikan item pembayarannya minimal 1 baris.',
        ];
    }

    public function mount(?SuratSp3 $suratSp3 = null)
    {
        $this->loadMengetahuiOptions();

        if ($suratSp3) {
            $this->suratSp3 = $suratSp3;
            $this->loadData();
        }
    }

    #[On('buka-edit-sp3')]
    public function loadSp3ById($id)
    {
        $this->suratSp3 = SuratSp3::with('details')->find($id);
        $this->loadMengetahuiOptions();

        if ($this->suratSp3) {
            $this->loadData();
        }
    }

    public function loadMengetahuiOptions(): void
    {
        $this->mengetahuiOptions = Jabatan::with('bagian')
            ->whereHas('bagian', function ($query) {
                $query->where('group', 'manajemen');
            })
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->nama,
                    'value' => $item->id
                ];
            });
    }

    public function loadData(): void
    {
        if (!$this->suratSp3) {
            return;
        }

        $this->tgl                     = $this->suratSp3->tgl;
        $this->rekanan                 = $this->suratSp3->rekanan;
        $this->method_bayar            = $this->suratSp3->bayar ?? $this->suratSp3->method_bayar ?? 'tunai';
        $this->keterangan              = $this->suratSp3->keterangan;
        $this->jabatan                 = $this->suratSp3->jabatan_id;
        $this->verifikator_keuangan_id = $this->suratSp3->verifikator_keuangan_id;

        $supplier = Supplier::where('nama', $this->rekanan)->first();
        if ($supplier) {
            $this->rekananId = $supplier->id;
            $this->isRekanan = true;
        } else {
            $this->rekananId = null;
            $this->isRekanan = false;
        }

        $details = $this->suratSp3->details;
        if ($details && $details->isNotEmpty()) {
            $this->listSp3 = $details->map(fn ($d) => [
                'nominal'    => (int) $d->nominal,
                'keterangan' => $d->keterangan,
            ])->toArray();
        } else {
            $this->listSp3 = [
                ['nominal' => 0, 'keterangan' => '']
            ];
        }

        if ($this->jabatan) {
            $this->updatedJabatan($this->jabatan);
        }
    }

    public function updatedRekananId($value)
    {
        if ($value) {
            $supplier = Supplier::find($value);
            if ($supplier) {
                $this->rekanan = $supplier->nama;
            }
        }
    }

    public function updatedJabatan($value)
    {
        $jabatan = Jabatan::find($value);
        if ($jabatan) {
            $karyawanJabatan = $jabatan->jabatans()
                ->where('tgl_berakhir', null)
                ->orderBy('id', 'desc')
                ->first();

            if ($karyawanJabatan) {
                $this->mengetahui   = $karyawanJabatan->karyawan?->id;
                $this->userApprove  = $karyawanJabatan->karyawan?->user?->id ?? auth()->id() ?? 1;
            } else {
                $this->userApprove = auth()->id() ?? 1;
            }
        }
    }

    public function submit()
    {
        if (!$this->suratSp3) {
            $this->toast()->error('Error', 'Data Surat SP3 tidak ditemukan.')->send();
            return;
        }

        // Sinkronisasi rekanan jika menggunakan dropdown rekanan ID
        if ($this->isRekanan && $this->rekananId) {
            $supplier = Supplier::find($this->rekananId);
            if ($supplier) {
                $this->rekanan = $supplier->nama;
            }
        }

        // Bersihkan formatting ribuan dari nominal
        if (is_array($this->listSp3)) {
            foreach ($this->listSp3 as $key => $item) {
                if (isset($item['nominal'])) {
                    $cleaned = preg_replace('/[^\d]/', '', (string)$item['nominal']);
                    $this->listSp3[$key]['nominal'] = (double)$cleaned;
                }
            }
        }

        $this->validate();

        DB::beginTransaction();
        try {
            // Update SP3 header dan kembalikan status ke PENDING
            $this->suratSp3->update([
                'tgl'                     => $this->tgl,
                'rekanan'                 => $this->rekanan,
                'bayar'                   => $this->method_bayar,
                'keterangan'              => $this->keterangan,
                'jabatan_id'              => $this->jabatan,
                'verifikator_keuangan_id' => $this->verifikator_keuangan_id,
                'status'                  => StatusApproval::PENDING,
            ]);

            // Hapus detail lama dan masukkan detail baru
            $this->suratSp3->details()->delete();
            foreach ($this->listSp3 as $item) {
                SuratSp3Detail::create([
                    'sp3_id'     => $this->suratSp3->id,
                    'nominal'    => $item['nominal'] ?? 0,
                    'keterangan' => $item['keterangan'] ?? '',
                ]);
            }

            // Hapus riwayat approval sebelumnya agar bisa diverifikasi ulang
            $this->suratSp3->approvals()->delete();

            // Sync ke docstore
            app(\App\Services\DocstoreSyncService::class)->syncSp3($this->suratSp3->fresh());

            DB::commit();


            $this->dispatch('update-approval');
            $this->dispatch('close-modal', id: 'modal-edit-sp3');

            $this->toast()
                ->success('Berhasil', "Surat SP3 {$this->suratSp3->no} berhasil diperbarui dan diajukan ulang ke Keuangan.")
                ->send();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast()
                ->error('Gagal', 'Error: ' . $th->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.surat.sp3.edit');
    }
}
