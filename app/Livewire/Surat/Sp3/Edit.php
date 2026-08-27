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
        $creator = $this->suratSp3?->created_by ? \App\Models\User::find($this->suratSp3->created_by) : auth()->user();
        $data = Jabatan::getMengetahuiOptionsForUser($creator);
        $this->mengetahuiOptions = $data['options'];
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

        // 1. Rekam data sebelum diedit untuk mencatat riwayat perubahan (diff)
        $oldRekanan          = $this->suratSp3->rekanan;
        $oldTgl              = $this->suratSp3->tgl;
        $oldBayar            = $this->suratSp3->bayar ?? $this->suratSp3->method_bayar;
        $oldKeterangan       = $this->suratSp3->keterangan;
        $oldJabatanId        = $this->suratSp3->jabatan_id;
        $oldJabatanNama      = optional($this->suratSp3->jabatans)->nama ?? '-';
        $oldVerifikatorId    = $this->suratSp3->verifikator_keuangan_id;
        $oldVerifikatorNama  = optional($this->suratSp3->verifikatorKeuangan)->full_nama ?? optional($this->suratSp3->verifikatorKeuangan)->nama ?? '-';
        $oldTotal            = (float)$this->suratSp3->details->sum('nominal');
        $oldDetailsCount     = $this->suratSp3->details->count();

        $newTotal            = (float)collect($this->listSp3)->sum('nominal');
        $newJabatanNama      = optional(\App\Models\Sdm\Jabatan::find($this->jabatan))->nama ?? '-';
        $newVerifikator      = \App\Models\Sdm\Karyawan::find($this->verifikator_keuangan_id);
        $newVerifikatorNama  = $newVerifikator?->full_nama ?? $newVerifikator?->nama ?? '-';

        $perubahan = [];
        if ($oldRekanan !== $this->rekanan) {
            $perubahan[] = ['field' => 'Rekanan / Pelaksana', 'dari' => $oldRekanan, 'menjadi' => $this->rekanan];
        }
        if ($oldTgl !== $this->tgl) {
            $perubahan[] = ['field' => 'Tanggal Surat', 'dari' => date('d M Y', strtotime($oldTgl)), 'menjadi' => date('d M Y', strtotime($this->tgl))];
        }
        if ($oldBayar !== $this->method_bayar) {
            $perubahan[] = ['field' => 'Metode Bayar', 'dari' => strtoupper($oldBayar), 'menjadi' => strtoupper($this->method_bayar)];
        }
        if ($oldKeterangan !== $this->keterangan) {
            $perubahan[] = ['field' => 'Keterangan / Berita', 'dari' => $oldKeterangan, 'menjadi' => $this->keterangan];
        }
        if ($oldJabatanId != $this->jabatan) {
            $perubahan[] = ['field' => 'Mengetahui (Atasan TTD)', 'dari' => $oldJabatanNama, 'menjadi' => $newJabatanNama];
        }
        if ($oldVerifikatorId != $this->verifikator_keuangan_id) {
            $perubahan[] = ['field' => 'Verifikator Keuangan', 'dari' => $oldVerifikatorNama, 'menjadi' => $newVerifikatorNama];
        }
        if (abs($oldTotal - $newTotal) > 0.01) {
            $perubahan[] = ['field' => 'Total Nominal', 'dari' => formatRupiah($oldTotal), 'menjadi' => formatRupiah($newTotal)];
        }
        if ($oldDetailsCount !== count($this->listSp3)) {
            $perubahan[] = ['field' => 'Jumlah Item Pembayaran', 'dari' => $oldDetailsCount . ' item', 'menjadi' => count($this->listSp3) . ' item'];
        }

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

            // Log history edit & ajukan ulang beserta rincian perubahannya
            \App\Models\Surat\SuratSp3Log::create([
                'surat_sp3_id'   => $this->suratSp3->id,
                'user_id'        => auth()->id(),
                'karyawan_id'    => auth()->user()?->karyawan_id,
                'nama_pelaku'    => auth()->user()?->karyawan?->full_nama ?? auth()->user()?->name ?? 'Pembuat SP3',
                'jabatan_pelaku' => optional(auth()->user()?->karyawan?->jabatan?->first())->nama ?? 'Staf',
                'aksi'           => 'Diedit & Diajukan Ulang',
                'status'         => 'pending',
                'catatan'        => 'Surat SP3 diperbaiki oleh pembuat dan diajukan ulang ke Bagian Keuangan.',
                'perubahan'      => !empty($perubahan) ? $perubahan : null,
            ]);

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
