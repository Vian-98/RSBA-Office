<?php

namespace App\Livewire\Kepegawaian\CutiBersama;

use App\Models\Sdm\CutiBersama;
use App\Models\Sdm\CutiBersamaTanggal;
use App\Models\Surat\CutiJenis;
use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

#[Title('Kelola Cuti Bersama')]
class Index extends Component
{
    use WithPagination;
    use AuthorizesFromRoute;
    use Interactions;

    public string $search = '';
    public int $perPage = 10;

    // Modal state
    public bool $modalOpen = false;
    public ?int $selectedId = null;

    // Form fields
    public string $nama = '';
    public ?string $keterangan = null;
    public int $jenis_cuti_id = 1;
    public bool $potong_cuti_tahunan = true;
    public array $tgl_cuti = [];

    protected $rules = [
        'nama' => 'required|string|max:255',
        'keterangan' => 'nullable|string',
        'tgl_cuti' => 'required|array|min:1',
        'tgl_cuti.*' => 'required|date',
    ];

    protected $messages = [
        'tgl_cuti.required' => 'Pilih setidaknya satu tanggal untuk Cuti Bersama.',
        'tgl_cuti.min' => 'Pilih setidaknya satu tanggal untuk Cuti Bersama.',
    ];

    public function mount()
    {
        $cutiTahunan = CutiJenis::where('nama', 'like', '%tahunan%')->first();
        if ($cutiTahunan) {
            $this->jenis_cuti_id = $cutiTahunan->id;
        }
    }

    public function openModal(?int $id = null)
    {
        $this->resetValidation();
        $this->selectedId = $id;

        if ($id) {
            $cutiBersama = CutiBersama::with('tanggal')->findOrFail($id);
            $this->nama = $cutiBersama->nama;
            $this->keterangan = $cutiBersama->keterangan;
            $this->jenis_cuti_id = $cutiBersama->jenis_cuti_id ?? 1;
            $this->potong_cuti_tahunan = true;
            $this->tgl_cuti = $cutiBersama->tanggal->pluck('tanggal')->map(fn($d) => $d->format('Y-m-d'))->toArray();
        } else {
            $this->nama = '';
            $this->keterangan = '';
            $this->jenis_cuti_id = 1;
            $this->potong_cuti_tahunan = true;
            $this->tgl_cuti = [];
        }

        $this->modalOpen = true;
    }

    public function closeModal()
    {
        $this->modalOpen = false;
    }

    public function save()
    {
        $this->validate();

        if ($this->selectedId) {
            $cutiBersama = CutiBersama::findOrFail($this->selectedId);
            if ($cutiBersama->status === 'diterapkan') {
                $this->toast()->error('Tidak dapat mengubah event yang sudah diterapkan. Batalkan terlebih dahulu.')->send();
                return;
            }

            $cutiBersama->update([
                'nama' => $this->nama,
                'keterangan' => $this->keterangan,
                'jenis_cuti_id' => 1,
                'potong_cuti_tahunan' => true,
            ]);

            // Sync tanggal
            $cutiBersama->tanggal()->delete();
            foreach ($this->tgl_cuti as $tgl) {
                CutiBersamaTanggal::create([
                    'cuti_bersama_id' => $cutiBersama->id,
                    'tanggal' => $tgl,
                ]);
            }

            $this->toast()->success('Event Cuti Bersama berhasil diperbarui.')->send();
        } else {
            $cutiBersama = CutiBersama::create([
                'nama' => $this->nama,
                'keterangan' => $this->keterangan,
                'jenis_cuti_id' => 1,
                'potong_cuti_tahunan' => true,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($this->tgl_cuti as $tgl) {
                CutiBersamaTanggal::create([
                    'cuti_bersama_id' => $cutiBersama->id,
                    'tanggal' => $tgl,
                ]);
            }

            $this->toast()->success('Event Cuti Bersama baru berhasil dibuat (Draft).')->send();
        }

        $this->modalOpen = false;
    }

    public function delete($id)
    {
        $cutiBersama = CutiBersama::findOrFail($id);
        if ($cutiBersama->status === 'diterapkan') {
            $this->toast()->error('Event yang sudah diterapkan tidak bisa langsung dihapus. Batalkan terlebih dahulu.')->send();
            return;
        }

        $cutiBersama->delete();
        $this->toast()->success('Event Cuti Bersama berhasil dihapus.')->send();
    }

    public function render()
    {
        $this->authorizeFromRoute();

        $events = CutiBersama::with(['jenisCuti', 'tanggal', 'diprosesOleh'])
            ->when($this->search, fn($q) => $q->where('nama', 'like', '%' . $this->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.kepegawaian.cuti-bersama.index', [
            'events' => $events,
        ]);
    }
}
