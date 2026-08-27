<?php

namespace App\Livewire\Kuitansi;

use App\Enums\StatusKuitansi;
use App\Models\Keuangan\Kuitansi;
use App\Models\Keuangan\KuitansiApproval;
use App\Models\Keuangan\KuitansiDetail;
use App\Models\Keuangan\MetodeBayar;
use App\Models\Sdm\Karyawan;
use App\Services\DocstoreSyncService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public string $tanggal = '';
    public ?string $diterima_dari = '';
    public ?string $keterangan = '';
    public ?int $penerima_id = null;
    public string $penerima_nama = '';
    public string $metode_bayar = 'Tunai';
    public $jumlah = null;

    public array $items = [];
    public array $approvers = [];

    protected function rules(): array
    {
        return [
            'tanggal'       => 'required|date',
            'diterima_dari' => 'nullable|string|max:150',
            'keterangan'    => 'required|string|max:1000',
            'penerima_id'   => 'nullable|exists:sdm_karyawan,id',
            'penerima_nama' => 'required|string|max:100',
            'metode_bayar'  => 'required|string|max:50',
            'approvers'     => 'required|array|min:1',
            'items'         => 'nullable|array',
            'jumlah'        => 'required|numeric|min:1',
        ];
    }

    protected $messages = [
        'approvers.required' => 'Pilih minimal satu pejabat persetujuan / verifikator.',
        'approvers.min'      => 'Pilih minimal satu pejabat persetujuan / verifikator.',
        'penerima_nama.required' => 'Nama penerima wajib diisi.',
        'jumlah.required'    => 'Nominal kuitansi wajib diisi.',
        'jumlah.min'         => 'Nominal kuitansi harus lebih besar dari 0.',
    ];

    public function mount()
    {
        $this->tanggal = date('Y-m-d');
        $userKaryawan = auth()->user()->karyawan;

        if ($userKaryawan) {
            $this->penerima_id = $userKaryawan->id;
            $this->penerima_nama = $userKaryawan->full_nama ?? $userKaryawan->nama;
        } else {
            $this->penerima_nama = auth()->user()->name;
        }

        $this->items = [
            ['keterangan' => '', 'nominal' => '']
        ];
    }

    public function updatedPenerimaId($value)
    {
        if ($value) {
            $karyawan = Karyawan::find($value);
            if ($karyawan) {
                $this->penerima_nama = $karyawan->full_nama ?? $karyawan->nama;
            }
        }
    }

    public function addItem()
    {
        $this->items[] = ['keterangan' => '', 'nominal' => ''];
    }

    public function removeItem(int $index)
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            $this->calculateTotal();
        }
    }

    public function updatedItems()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $nom = isset($item['nominal']) ? (float) preg_replace('/[^\d]/', '', (string)$item['nominal']) : 0;
            $total += $nom;
        }

        if ($total > 0) {
            $this->jumlah = number_format($total, 0, ',', '.');
        }
    }

    public function updatedJumlah($value)
    {
        if (!empty($value)) {
            $raw = preg_replace('/[^\d]/', '', (string)$value);
            if ($raw !== '') {
                $this->jumlah = number_format((float)$raw, 0, ',', '.');
            }
        }
    }

    public function submit()
    {
        // Clean numeric formatted string for jumlah
        if (is_string($this->jumlah)) {
            $this->jumlah = (float) preg_replace('/[^\d]/', '', $this->jumlah);
        }

        $this->validate();


        // Ensure metode bayar is saved in master if new
        MetodeBayar::firstOrCreate(['nama' => trim($this->metode_bayar)]);

        DB::beginTransaction();
        try {
            $nomor = $this->createNomor();

            $kuitansi = Kuitansi::create([
                'nomor'         => $nomor,
                'tanggal'       => $this->tanggal,
                'diterima_dari' => $this->diterima_dari,
                'jumlah'        => $this->jumlah,
                'keterangan'    => $this->keterangan,
                'penerima_id'   => $this->penerima_id,
                'penerima_nama' => $this->penerima_nama,
                'metode_bayar'  => $this->metode_bayar,
                'status'        => StatusKuitansi::WAITING,
                'created_by'    => auth()->id(),
            ]);

            // Save details if provided
            $hasValidItems = false;
            foreach ($this->items as $item) {
                if (!empty($item['keterangan']) && !empty($item['nominal'])) {
                    $hasValidItems = true;
                    $nom = (float) str_replace(['.', ','], ['', '.'], $item['nominal']);
                    KuitansiDetail::create([
                        'kuitansi_id' => $kuitansi->id,
                        'keterangan'  => $item['keterangan'],
                        'nominal'     => $nom,
                    ]);
                }
            }

            // If no itemized details entered, create 1 detail matching keterangan & jumlah
            if (!$hasValidItems) {
                KuitansiDetail::create([
                    'kuitansi_id' => $kuitansi->id,
                    'keterangan'  => $this->keterangan,
                    'nominal'     => $this->jumlah,
                ]);
            }

            // Create approval entries
            foreach ($this->approvers as $approverId) {
                KuitansiApproval::create([
                    'kuitansi_id'    => $kuitansi->id,
                    'disetujui_oleh' => $approverId,
                    'status'         => 'waiting',
                ]);
            }

            // Trigger docstore sync
            app(DocstoreSyncService::class)->syncKuitansi($kuitansi);

            DB::commit();

            $this->dispatch('kuitansi-tersimpan', id: $kuitansi->id);
            $this->dispatch('close-modal', id: 'modal-add-kuitansi');

            $this->toast()
                ->success('Berhasil', "Kuitansi {$nomor} berhasil dibuat dan diajukan untuk persetujuan.")
                ->send();

            $this->reset(['diterima_dari', 'keterangan', 'jumlah', 'approvers']);
            $this->items = [['keterangan' => '', 'nominal' => '']];
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Gagal Menyimpan', $e->getMessage())
                ->send();
        }
    }

    /**
     * Generate nomor kuitansi otomatis yang reset tiap tahun:
     * Format: KW/{0001}/{Y} contoh: KW/0001/2026
     */
    protected function createNomor(): string
    {
        $tahun = date('Y', strtotime($this->tanggal ?: 'now'));
        $prefix = "KW/";
        $suffix = "/{$tahun}";

        $lastRecord = Kuitansi::lockForUpdate()
            ->where('nomor', 'like', "{$prefix}%{$suffix}")
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($lastRecord) {
            $parts = explode('/', $lastRecord->nomor);
            if (count($parts) === 3 && is_numeric($parts[1])) {
                $nextNum = (int)$parts[1] + 1;
            }
        }

        do {
            $formattedNum = str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            $candidate = "{$prefix}{$formattedNum}{$suffix}";
            $exists = Kuitansi::where('nomor', $candidate)->exists();
            if ($exists) {
                $nextNum++;
            }
        } while ($exists);

        return $candidate;
    }

    public function render()
    {
        return view('livewire.kuitansi.add', [
            'metodeBayarOptions' => MetodeBayar::pluck('nama', 'nama')->toArray(),
        ]);
    }
}
