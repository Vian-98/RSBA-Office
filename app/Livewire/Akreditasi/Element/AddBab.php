<?php

namespace App\Livewire\Akreditasi\Element;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use App\Models\Akreditasi\AkreBabElement;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

#[Lazy]
class AddBab extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use Interactions;
    use InteractsWithForms;

    public ?int $chapter_id;
    public ?string $jenisPenomoran = 'alfabet';
    public ?int $parent = null;
    public ?string $nama, $deskripsi, $maksud_tujuan;
    public ?string $bab = 'bab';

    public function rules(): array
    {
        return [
            'nama' => 'required',
            'deskripsi' => 'required',
            'maksud_tujuan' => 'required',
            'parent' => $this->bab === 'sub' ? 'required' : ''
        ];
    }


    public function submit()
    {
        $this->validate();
        DB::beginTransaction();
        try {
            $data = [
                'no' => $this->generateNomorOtomatis(),
                'chapter_id' => $this->chapter_id,
                'nama' => $this->nama,
                'deskripsi' => $this->deskripsi,
                'maksud_tujuan' => $this->maksud_tujuan,
                'bab' => $this->bab,
                'parent_id' => $this->parent,
            ];

            AkreBabElement::create($data);

            DB::commit();

            $this->toast()
                ->success('Berhasil', 'Bab baru ditambahkan.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Tidak Berhasil', $e->getMessage())
                ->send();
        }
    }

    public function generateNomorOtomatis()
    {
        // Ambil nomor terakhir berdasarkan jenis penomoran
        $recordTerakhir = AkreBabElement::where('chapter_id', $this->chapter_id)
            ->where('bab', $this->bab)
            ->where('parent_id', $this->parent)
            ->latest('id')
            ->first();

        if (!$recordTerakhir) {
            // Jika belum ada data
            return $this->jenisPenomoran === 'alfabet' ? 'A' : '1';
            return;
        }

        // Generate nomor berikutnya
        if ($this->jenisPenomoran === 'alfabet') {
            return $this->getNextAlfabet($recordTerakhir->no);
        } else {
            return (int)$recordTerakhir->no + 1;
        }
    }

    private function getNextAlfabet($currentAlfabet)
    {
        // A -> B, B -> C, dst
        if (strlen($currentAlfabet) === 1) {
            $ascii = ord($currentAlfabet);
            if ($ascii < 90) { // A-Z
                return chr($ascii + 1);
            }
            return 'AA'; // Setelah Z
        }

        // AA -> AB, AB -> AC, dst
        return ++$currentAlfabet;
    }

    #[Computed]
    public function bab(): array
    {
        return AkreBabElement::where('bab', 'bab')
            ->where('chapter_id', $this->chapter_id)
            ->get()
            ->map(function ($item) {
                return [
                    'nama' => $item->nama,
                    'value' => $item->id
                ];
            })
            ->values()
            ->toArray();
    }

    public function mount($chapterId)
    {
        $this->chapter_id = $chapterId;
    }

    // filament schema for text-rich-editor
    public function getFormSchema(): array
    {
        return [
            RichEditor::make('deskripsi')
                ->required()
                ->hiddenLabel()
                ->placeholder('Deskripsi Bab')
                ->toolbarButtons([
                    'bold',
                    'italic',
                    'underline',
                    'strike',
                    'bulletList',
                    'orderedList',
                    'link',
                    'blockquote',
                    'undo',
                    'redo'
                ])
                ->columnSpanFull(),

            RichEditor::make('maksud_tujuan')
                ->required()
                ->hiddenLabel()
                ->placeholder('Maksud & Tujuan Bab.')
                ->toolbarButtons([
                    'bold',
                    'italic',
                    'underline',
                    'strike',
                    'bulletList',
                    'orderedList',
                    'link',
                    'blockquote',
                    'undo',
                    'redo'
                ])
                ->columnSpanFull(),
        ];
    }


    public function render()
    {
        return view('livewire.akreditasi.element.add-bab');
    }
}
