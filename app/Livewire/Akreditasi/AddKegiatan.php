<?php

namespace App\Livewire\Akreditasi;

use Throwable;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use App\Models\Akreditasi\AkreKegiatan;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;

class AddKegiatan extends Component
{
    use Interactions;

    public ?string $nama, $lembaga, $standar;
    public $tanggal;
    public ?string $createTerNewStandar;

    public ?string $newStandar;

    #[Computed]
    public ?array $optionsStandar =  [];

    public function rules(): array
    {
        return [
            'nama' => 'required',
            'standar' => 'required',
            'lembaga' => 'required',
            'tanggal' => 'required'
        ];
    }

    public function mount()
    {
        $this->standar_akreditasi();
    }

    public function standar_akreditasi()
    {
        $this->optionsStandar =  AkreKegiatan::get()
            ->groupBy('standard')
            ->map(function ($items, $standard) {
                return [
                    'nama' => $standard,
                    'value' => $standard
                ];
            })
            ->values()
            ->toArray();
    }


    public function submit_new_standar()
    {
        $new = [
            'nama' => $this->newStandar,
            'value' => $this->newStandar
        ];

        $this->optionsStandar[] = $new;

        $this->toast()
            ->success('Berhasil', 'Standar akreditasi berhasil dibuat.')
            ->send();

        $this->dispatch('close-modal', id: 'modal-new-standar-akre');
    }

    public function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {

            // create folder
            $folder = "akre/{$this->tanggal}/" . Str::uuid();
            Storage::disk('public')->makeDirectory($folder);

            // Simpan data
            $data = [
                'uuid' => Str::uuid(),
                'tanggal' => $this->tanggal,
                'nama' => $this->nama,
                'standard' => $this->standar,
                'lembaga' => $this->lembaga,
                'created_by' => auth()->user()->id,
                'folder_path' => $folder
            ];


            // chapters berdasarkan standar terakhir
            $kegiatan = AkreKegiatan::where('standard', $this->standar)
                ->with(['chapters.babs.elements.files'])
                ->latest()
                ->first();

            // dd($kegiatan);

            if ($kegiatan) {
                $newKegiatan = AkreKegiatan::create($data);


                // Map: old random ID => new auto-generated ID pada babs each
                $babMapping = [];

                foreach ($kegiatan->chapters as $chapter) {
                    $newChapter = $chapter->replicate();
                    $newChapter->kegiatan_id = $newKegiatan->id;

                    $newChapter->save();

                    foreach ($chapter->babs as $bab) {
                        $newBab = $bab->replicate();
                        $newBab->chapter_id = $newChapter->id;
                        $newBab->parent_id = null;
                        $newBab->save();

                        // mapping bab: old random ID => new auto-generated ID
                        $babMapping[$bab->id] = $newBab->id;

                        foreach ($bab->elements as $element) {
                            $newElement = $element->replicate(
                                [
                                    'nilai',
                                    'tdd',
                                    'catatan',
                                    'validate_by',
                                    'is_correction'
                                ]
                            );
                            $newElement->akre_bab_id = $newBab->id;
                            $newElement->save();

                            foreach ($element->files as $file) {
                                $newFile = $file->replicate();
                                $newFile->element_id = $newElement->id;
                                $newElement->save();
                            }
                        }
                    }


                    // each update parent_id pada akre_bab_elements
                    foreach ($chapter->babs as $bab) {
                        if ($bab->parent_id !== null && isset($babMapping[$bab->parent_id])) {
                            $newBabId = $babMapping[$bab->id];
                            $newParentId = $babMapping[$bab->parent_id];

                            DB::table('akre_bab_elements')
                                ->where('id', $newBabId)
                                ->update(['parent_id' => $newParentId]);
                        }
                    }
                }

                // insert kegiatan , chapters, bab, element, ke new AkreKegiatan;
            } else {
                AkreKegiatan::insert($data);
            }

            DB::commit();

            $this->toast()
                ->success('Berhasil', 'Kegiatan akreditasi ditambahkan.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            // Hapus folder jika sudah terbuat
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
        return view('livewire.akreditasi.add-kegiatan');
    }
}
