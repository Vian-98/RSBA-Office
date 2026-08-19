<?php

namespace App\Livewire\Akreditasi\Documents;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use App\Models\Akreditasi\AkreElement;
use Livewire\Component;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Livewire\Attributes\Locked;
use Livewire\WithoutUrlPagination;
use TallStackUi\Traits\Interactions;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use App\Models\Akreditasi\AkreElementDocuments;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class TableDocuments extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use WithFileUploads;
    use Interactions;
    use InteractsWithTable, InteractsWithForms;
    use WithoutUrlPagination;

    #[Locked]
    public ?int $element_id;

    #[Locked]
    public ?int $kegiatan_id;

    #[Locked]
    public ?int $selectedDocId;

    public function mount($elementId)
    {
        $this->element_id = $elementId;

        $kegiatan =  AkreElement::join('akre_bab_elements', 'akre_elements.akre_bab_id', '=', 'akre_bab_elements.id')
            ->join('akre_chapter', 'akre_bab_elements.chapter_id', '=', 'akre_chapter.id')
            ->join('akre_kegiatan', 'akre_chapter.kegiatan_id', '=', 'akre_kegiatan.id')
            ->where('akre_elements.id', $elementId)
            ->select('akre_kegiatan.id')
            ->first();

        if ($kegiatan) {
            $this->kegiatan_id = $kegiatan->id;
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AkreElementDocuments::with([
                    'sourceElement'
                ])
                    ->where('element_id', $this->element_id)
            )
            ->columns([
                TextColumn::make('document.nama')
                    ->label('Nama File')
                    ->searchable()
                    ->action(fn($record) => $this->viewDocument(
                        id: $record->document->id
                    )),

                TextColumn::make('upload')
                    ->label('Upload By')
                    ->getStateUsing(function ($record) {
                        $formattedDate = $record->document->created_at->format('Y-m-d H:i:s');
                        return
                            "<div class='flex flex-col'>
                                <span class='text-sm'>{$record->document->user_upload}</span>
                                <span class='text-xs text-gray-500 italic'>{$formattedDate}</span>
                            </div>";
                    })
                    ->html()
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderBy('created_at', $direction);
                    }),

                TextColumn::make('source_element_id')
                    ->label('Sumber Document')
                    ->formatStateUsing(
                        function ($record) {
                            // Dapatkan sumber documents atau document asli
                            $sumber = $record->sourceElement;

                            if (!$sumber) {
                                return $record->source_deleted
                                    ? "<span class='text-red-500'>Sumber Dihapus</span>"
                                    : "<span class='text-gray-500'>Tidak ada sumber</span>";
                            }

                            $deletedLabel = $record->source_deleted
                                ? "<span class='inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-600'>Dihapus</span>"
                                : "";

                            // return "<div >
                            // {$sumber->bab->chapter->singkatan}
                            // <span class='text-xs text-gray-400'>></span>
                            // {$sumber->bab->nama}
                            // <span class='text-xs text-gray-400'>></span>
                            // {$sumber->nomor}<br>
                            // {$deletedLabel}
                            // </div>";

                            return "<div class='flex flex-col'>
                                <span class='text-sm'>
                                {$sumber->bab->chapter->singkatan}
                                <span class='text-xs text-gray-400'>></span>
                                {$sumber->bab->nama}
                                <span class='text-xs text-gray-400'>></span>
                                {$sumber->nomor}
                                </span>
                                <span class='text-xs italic'>{$deletedLabel}</span>
                            </div>";
                        }
                    )
                    ->wrap()
                    ->html()
            ])
            ->recordActions([
                Action::make('attach')
                    ->iconButton()
                    ->icon('tabler-file-export')
                    ->action(
                        fn($record, $livewire) => $livewire->modalAttach(
                            id: $record->document_id
                        )
                    ),

                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus File')
                    ->modalDescription('Are you sure? This will also delete associated files.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->before(function ($record) {
                        AkreElementDocuments::where('document_id', $record->document->id)
                            ->where('is_original', false)
                            ->update(
                                ['source_deleted' => true]
                            );

                        // Soft delete the document
                        if ($record->is_original) {
                            $record->document->update([
                                'is_deleted' => true,
                                'deleted_at' => now(),
                                'deleted_by' => auth()->id(),
                            ]);
                        }
                    })
                    ->after(function () {
                        $this->dispatch('deleted-files_element');

                        $this->toast()
                            ->success('Berhasil', 'File berhasil dihapus.')
                            ->send();
                    })
                    ->visible(
                        fn() => auth()->user()->can('view-kepegawaian-akreditasi') or auth()->user()->can('sekretariat-akreditasi')
                    )
            ])

        ;
    }

    #[On('uploaded-files-element')]
    public function refreshTable()
    {
        $this->resetTable();
    }

    public function viewDocument($id)
    {
        $this->selectedDocId = $id;
        $this->dispatch('open-modal', id: "modal-document-view");
    }

    public function modalAttach($id)
    {
        $this->selectedDocId = $id;
        $this->dispatch('open-modal', id: 'modal-attach-file');
    }

    public function render()
    {
        return view('livewire.akreditasi.documents.table-documents');
    }
}
