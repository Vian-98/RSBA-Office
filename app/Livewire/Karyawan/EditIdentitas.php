<?php

namespace App\Livewire\Karyawan;

use Throwable;
use App\Enums\Agama;
use App\Enums\Kelamin;
use Livewire\Component;
use App\Models\Sdm\Karyawan;
use App\Enums\StatusKaryawan;
use Livewire\Attributes\Lazy;
use App\Livewire\Forms\KaryawanForm;
use TallStackUi\Traits\Interactions;

use Livewire\WithFileUploads;
use App\Models\Sdm\KaryawanDocument;
use Illuminate\Support\Facades\Storage;

#[Lazy]
class EditIdentitas extends Component
{
    use Interactions;
    use WithFileUploads;

    public KaryawanForm $form;

    public $status_options;
    public $agama_options;
    public $jk_options;
    public $pernikahan_options = [
        ['value' => 'belum_menikah', 'label' => 'Belum Menikah'],
        ['value' => 'menikah', 'label' => 'Menikah'],
        ['value' => 'janda_duda', 'label' => 'Janda/Duda']
    ];

    public $ptkp_options = [
        ['value' => 'TK0', 'label' => 'TK/0 (Tidak Kawin, 0 Tanggungan)'],
        ['value' => 'TK1', 'label' => 'TK/1 (Tidak Kawin, 1 Tanggungan)'],
        ['value' => 'TK2', 'label' => 'TK/2 (Tidak Kawin, 2 Tanggungan)'],
        ['value' => 'TK3', 'label' => 'TK/3 (Tidak Kawin, 3 Tanggungan)'],
        ['value' => 'K0',  'label' => 'K/0 (Kawin, 0 Tanggungan)'],
        ['value' => 'K1',  'label' => 'K/1 (Kawin, 1 Tanggungan)'],
        ['value' => 'K2',  'label' => 'K/2 (Kawin, 2 Tanggungan)'],
        ['value' => 'K3',  'label' => 'K/3 (Kawin, 3 Tanggungan)'],
    ];

    public $isDomisiliKTP = false;
    public $showLisensiSection = false;
    public $file_str;
    public $current_str_doc;

    public function mount($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $this->form->mount($karyawan);

        $this->form->setIdentitas($karyawan);

        $this->status_options = StatusKaryawan::options();
        $this->agama_options = Agama::options();
        $this->jk_options = Kelamin::options();

        $jabatan = $karyawan->jabatan->first();
        $bagianId = $jabatan?->pivot?->bagian_id ?? $jabatan?->bagian_id;
        $bagian = $bagianId ? \App\Models\Sdm\Bagian::find($bagianId) : null;
        $this->showLisensiSection = ($bagian?->group === 'medis');

        $this->loadStrDoc();
    }

    public function loadStrDoc()
    {
        if ($this->form->karyawan?->id) {
            $this->current_str_doc = KaryawanDocument::where('karyawan_id', $this->form->karyawan->id)
                ->where('jenis', 'str')
                ->latest()
                ->first();
        }
    }

    public function toggleLisensiSection()
    {
        $this->showLisensiSection = ! $this->showLisensiSection;
    }

    public function update()
    {
        $this->validate();

        if ($this->file_str) {
            $this->validate([
                'file_str' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);
        }

        try {
            $this->form->updateIdentitas();

            if ($this->file_str) {
                $filename = $this->file_str->store('karyawan/docs', 'public');

                if ($this->current_str_doc && $this->current_str_doc->filename && Storage::disk('public')->exists($this->current_str_doc->filename)) {
                    Storage::disk('public')->delete($this->current_str_doc->filename);
                }

                $this->current_str_doc = KaryawanDocument::updateOrCreate(
                    [
                        'karyawan_id' => $this->form->karyawan->id,
                        'jenis' => 'str',
                    ],
                    [
                        'nama' => 'Dokumen STR - ' . $this->form->karyawan->full_nama,
                        'filename' => $filename,
                    ]
                );

                $this->reset('file_str');
            }

            // Clear cache for updated employee's user, and current logged-in user
            $karyawan = $this->form->karyawan;
            if ($karyawan && $karyawan->user) {
                \Illuminate\Support\Facades\Cache::forget("navbar-user:" . $karyawan->user->id);
            }
            \Illuminate\Support\Facades\Cache::forget("navbar-user:" . auth()->id());

            $this->dispatch('updated-karywan');

            $this->toast()
                ->success('Updated', 'Update identitas & lisensi karyawan berhasil.')
                ->send();
        } catch (Throwable $e) {
            $this->toast()
                ->error('Failed', 'Error ' . $e->getMessage())
                ->send();
        }
    }

    public function deleteStrDoc()
    {
        if ($this->current_str_doc) {
            if ($this->current_str_doc->filename && Storage::disk('public')->exists($this->current_str_doc->filename)) {
                Storage::disk('public')->delete($this->current_str_doc->filename);
            }
            $this->current_str_doc->delete();
            $this->current_str_doc = null;

            $this->toast()
                ->success('Dokumen Dihapus', 'Softcopy dokumen STR berhasil dihapus.')
                ->send();
        }
    }

    function domisili()
    {
        $this->isDomisiliKTP = ! $this->isDomisiliKTP;
        if ($this->isDomisiliKTP) {
            $this->form->dom_prov = $this->form->prov;
            $this->form->dom_kab = $this->form->kab;
            $this->form->dom_kec = $this->form->kec;
            $this->form->dom_desa = $this->form->desa;
            $this->form->dom_alamat = $this->form->alamat;
        } else {
            $this->reset('form.dom_prov');
            $this->reset('form.dom_kab');
            $this->reset('form.dom_kec');
            $this->reset('form.dom_desa');
            $this->reset('form.dom_alamat');
        }
    }

    public function render()
    {
        $user = auth()->user();
        $canEditTglMasuk = $user && ($user->can('edit-tgl-masuk-karyawan') || $user->can('edit-kepegawaian-karyawan'));
        return view('livewire.karyawan.edit-identitas', compact('canEditTglMasuk'));
    }
}
