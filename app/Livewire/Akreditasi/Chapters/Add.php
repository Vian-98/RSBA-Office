<?php

namespace App\Livewire\Akreditasi\Chapters;

use Throwable;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use App\Models\Akreditasi\AkreChapter;
use Illuminate\Support\Facades\Storage;

class Add extends Component
{
    use Interactions;

    public ?int $kegiatan_id;
    public ?string $kegiatan_folder;

    public ?string $nama, $singkatan;
    public $deskripsi;

    public function mount(int $kegiatanId, string $folderKegiatan)
    {
        $this->kegiatan_id = $kegiatanId;
        $this->kegiatan_folder = $folderKegiatan;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required',
            'singkatan' => 'required'
        ];
    }

    public function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $folder = "{$this->kegiatan_folder}/{$this->singkatan}";
            Storage::makeDirectory($folder);

            $data  = [
                'kegiatan_id' => $this->kegiatan_id,
                'nama' => $this->nama,
                'singkatan' => $this->singkatan,
                'deskripsi' => $this->deskripsi,
                'folder_path' => $folder
            ];

            AkreChapter::insert($data);

            DB::commit();

            $this->toast()
                ->success('Berhasil', 'Chapter / Standar akreditasi ditambahkan.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            if (!empty($folder) && Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->deleteDirectory($folder);
            }

            $this->toast()
                ->error('Tidak Berhasil', "{$e->getMessage()}")
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.akreditasi.chapters.add');
    }
}
