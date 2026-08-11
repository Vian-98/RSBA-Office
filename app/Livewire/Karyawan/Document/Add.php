<?php

namespace App\Livewire\Karyawan\Document;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use App\Models\Sdm\KaryawanDocument;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use WithFileUploads;
    use Interactions;

    public $karyawanId;
    public $fileTmp;
    public $nama;
    public $jenis;

    public $jenisDocsOpt = [
        ['value' => 'ijazah', 'label' => 'Ijazah'],
        ['value' => 'sertifikat', 'label' => 'Sertifikat'],
        ['value' => 'str', 'label' => 'STR (Surat Tanda Registrasi)'],
        ['value' => 'sip', 'label' => 'SIP (Surat Izin Praktik)'],
        ['value' => 'pribadi', 'label' => 'Pribadi'],
        ['value' => 'lain', 'label' => 'Lain-Lain']
    ];

    public $rules = [
        'fileTmp' => 'required|mimes:pdf|max:10240',
        'nama' => 'required',
        'jenis' => 'required'
    ];

    public function mount($id)
    {
        $this->karyawanId = $id;
    }


    function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $path = $this->fileTmp->store('karyawan/docs', 'public');

            $data = [
                'karyawan_id' => $this->karyawanId,
                'nama' => $this->nama,
                'jenis' => $this->jenis,
                'filename' => $path
            ];
            // dd($data);

            KaryawanDocument::create($data);
            DB::commit();
            $this->dispatch('document-karyawan-created');

            $this->toast()
                ->success('Berhasil', 'Document karyawan berhasil disimpan.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Failed', 'Error : ' . $e->getMessage())
                ->send();
        }
    }


    public function render()
    {
        return view('livewire.karyawan.document.add');
    }
}
