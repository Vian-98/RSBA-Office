<?php

namespace App\Livewire\Surat\PerintahTugas;

use App\Models\Surat\SuratTemplateNomor;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

class KonfigFormat extends Component
{
    use Interactions;

    public $format_nomor;
    public $keterangan;

    public function mount()
    {
        $this->format_nomor = SuratTemplateNomor::getTemplate('perintah_tugas');
        $record = SuratTemplateNomor::where('jenis_surat', 'perintah_tugas')->first();
        $this->keterangan = $record?->keterangan ?? 'Format Nomor Surat Perintah Tugas (SPT)';
    }

    public function save()
    {
        $this->validate([
            'format_nomor' => 'required|string|max:255',
        ]);

        try {
            SuratTemplateNomor::updateOrCreate(
                ['jenis_surat' => 'perintah_tugas'],
                [
                    'format_nomor' => $this->format_nomor,
                    'keterangan'   => $this->keterangan,
                    'updated_by'   => auth()->id(),
                ]
            );

            $this->dispatch('close-modal', id: 'modal-konfig-format-spt');
            $this->toast()->success('Berhasil', 'Template penomoran Surat Perintah Tugas berhasil disimpan.')->send();
        } catch (Throwable $th) {
            $this->toast()->error('Gagal', 'Error: ' . $th->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.surat.perintah-tugas.konfig-format');
    }
}
