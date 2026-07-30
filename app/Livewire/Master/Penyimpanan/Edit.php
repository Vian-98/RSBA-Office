<?php

namespace App\Livewire\Master\Penyimpanan;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use App\Models\Master\BarangPenyimpanan;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public ?BarangPenyimpanan $penyimpanan;
    public string $nama = '', $deskripsi = '';
    public $lemaris = [];

    public function mount($id)
    {
        $this->penyimpanan = BarangPenyimpanan::findOrFail($id);
        $this->nama = $this->penyimpanan?->nama ?? '';
        $this->deskripsi = $this->penyimpanan?->deskripsi ?? '';
        
        $this->lemaris = $this->penyimpanan->lemaris->map(function ($lemari) {
            return [
                'id' => $lemari->id,
                'nama_lemari' => $lemari->nama_lemari
            ];
        })->toArray();
    }

    public function addLemari()
    {
        $this->lemaris[] = ['id' => null, 'nama_lemari' => ''];
    }

    public function removeLemari($index)
    {
        unset($this->lemaris[$index]);
        $this->lemaris = array_values($this->lemaris);
    }

    function submit()
    {
        $this->validate([
            'nama' => 'required|string|unique:um_penyimpanan,nama,' . $this->penyimpanan->id,
            'deskripsi' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $this->penyimpanan->update([
                'nama' => $this->nama,
                'deskripsi' => $this->deskripsi ?? '',
            ]);

            // Sync Lemari
            $lemariIds = collect($this->lemaris)->pluck('id')->filter()->toArray();
            $this->penyimpanan->lemaris()->whereNotIn('id', $lemariIds)->delete();

            foreach ($this->lemaris as $lemari) {
                if (!empty(trim($lemari['nama_lemari']))) {
                    if ($lemari['id']) {
                        $this->penyimpanan->lemaris()->where('id', $lemari['id'])->update([
                            'nama_lemari' => $lemari['nama_lemari']
                        ]);
                    } else {
                        $this->penyimpanan->lemaris()->create([
                            'nama_lemari' => $lemari['nama_lemari']
                        ]);
                    }
                }
            }

            DB::commit();

            $this->dispatch('penyimpanan-updated');
            $this->dispatch('close-modal', id: 'modal-edit-penyimpanan');

            $this->toast()
                ->success('Berhasil', 'Tempat penyimpanan berhasil diubah.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Gagal', "Tempat penyimpanan gagal diubah. Error: {$e->getMessage()}")
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.penyimpanan.edit');
    }
}
