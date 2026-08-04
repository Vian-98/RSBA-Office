<?php

namespace App\Livewire\Akreditasi\Element;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use App\Models\Akreditasi\AkreBabElement;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;

#[Lazy]
class AddBab extends Component implements HasSchemas
{
    use Interactions;
    use InteractsWithSchemas;

    public ?int $chapter_id;
    public ?string $jenisPenomoran = 'alfabet';
    public ?int $parent = null;
    public ?string $nama;
    public ?string $bab = 'bab';

    // filament form
    public ?array $formData = [];

    public function rules(): array
    {
        return [
            'nama' => 'required',
            'parent' => $this->bab === 'sub' ? 'required' : ''
        ];
    }


    public function submit()
    {
        $this->validate();

        // form data filament
        $formData = $this->form->getState();

        DB::beginTransaction();
        try {
            $data = [
                'no' => $this->generateNomorOtomatis(),
                'chapter_id' => $this->chapter_id,
                'nama' => $this->nama,
                'deskripsi' => $formData['deskripsi'],
                'maksud_tujuan' => $formData['maksud_tujuan'],
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

    public function mount(?int $chapterId)
    {
        $this->chapter_id = $chapterId;
        $this->form->fill();
    }

    // filament schema for text-rich-editor
    public function form(Schema $schema): Schema
    {
        return $schema->components([
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
        ])
            ->statePath('formData');
    }


    public function render()
    {
        return view('livewire.akreditasi.element.add-bab');
    }
}
