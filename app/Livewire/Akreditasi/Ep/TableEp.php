<?php

namespace App\Livewire\Akreditasi\Ep;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Livewire\Component;
use Filament\Tables\Table;
use TallStackUi\Traits\Interactions;
use Filament\Forms\Components\Select;
use App\Models\Akreditasi\AkreElement;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Attributes\On;

class TableEp extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use Interactions;
    use InteractsWithTable, InteractsWithForms;

    public ?int $akre_bab_id;
    public $modalPreffix = '';

    public ?int $docSelectedId;

    public ?int $elementSelectedId;

    public function mount($akre_bab_id, $modalPreffix)
    {
        $this->akre_bab_id = $akre_bab_id;
        $this->modalPreffix = $modalPreffix;
    }

    #[On('uploaded-files-element')]
    #[On('deleted-files_element')]
    public function refreshTable()
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AkreElement::with('documents')->where('akre_bab_id', $this->akre_bab_id)
            )
            ->columns([
                TextColumn::make('element')
                    ->label('Element Penilaian')
                    ->extraAttributes(['class' => 'text-xs'])
                    ->prefix(
                        fn($record) => $record->nomor . ") "
                    )
                    ->wrap()
                    ->searchable(),

                TextColumn::make('methode')
                    ->extraAttributes(['class' => 'text-xs'])
                    ->label('Methode')
                    ->badge(),

                TextColumn::make('kelengkapan')
                    ->label('Kelengkapan Penilaian')
                    ->extraAttributes(['class' => 'text-xs'])
                    ->html()
                    ->wrap(),

                TextColumn::make('nilai')
                    ->label('Nilai')
                    ->extraAttributes(['class' => 'text-xs'])
                    ->badge()
                    ->state(function ($record) {
                        if ($record->tdd) {
                            return 'TDD';
                        }
                        return $record->nilai ?? 'Belum Dinilai';
                    })
                    ->color(function ($record) {
                        if ($record->tdd) {
                            return 'primary';
                        }

                        return match ($record->nilai) {
                            null => 'gray',
                            0 => 'danger',
                            5 => 'warning',
                            10 => 'success',
                            default => 'gray'
                        };
                    })
                    ->default('Belum Dinilai')
                    ->action(
                        Action::make('penilaian')
                            ->modalHeading('Penilaian Element')
                            ->schema([
                                Checkbox::make('tdd')
                                    ->label('Tidak Dapat Dinilai (TDD)')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $set('nilai', null);
                                        }
                                    }),

                                Select::make('nilai')
                                    ->label('Nilai')
                                    ->options([
                                        0 => '0 - Tidak Ada',
                                        5 => '5 - Sebagian',
                                        10 => '10 - Lengkap'
                                    ])
                                    ->required(fn(Get $get) => !$get('tdd'))
                                    ->disabled(fn(Get $get) => $get('tdd'))
                                    ->dehydrated(),

                                Textarea::make('catatan')
                                    ->label('Catatan')
                                    ->placeholder('Catatan')
                                    ->helperText(
                                        fn(Get $get) => $get('validated_by')
                                            ? "{$get('updated_at')}, Oleh: {$get('validated_by')}"
                                            : null
                                    )
                                    ->rows(3),

                            ])
                            ->fillForm(fn($record) => [
                                'nilai' => $record->nilai,
                                'catatan' => $record->catatan,
                                'validated_by' => $record->validator,
                                'updated_at' => $record->updated_at
                            ])
                            ->action(function ($record, array $data) {
                                $record->update([
                                    'nilai' => $data['nilai'],
                                    'tdd' => $data['tdd'],
                                    'catatan' => $data['catatan'],
                                    'validated_by' => auth()->user()->id,
                                ]);

                                $this->dispatch('updated-nilai-element')->to('akreditasi.element.stats');

                                $this->toast()
                                    ->success('Berhasil', 'Penilaian berhasil disimpan.')
                                    ->send();
                            })
                            ->modalFooterActions(fn() => [
                                Action::make('submit')
                                    ->label('Simpan')
                                    ->submit('submit')
                                    ->visible(fn() => auth()->user()->can('assesor-akreditasi')),
                                Action::make('cancel')
                                    ->label('Tutup')
                                    ->close()
                                    ->color('gray')
                                    ->visible(fn() => auth()->user()->can('assesor-akreditasi')),
                            ])
                            ->modalFooterActionsAlignment('right')
                    ),

                TextColumn::make('catatan')
                    ->extraAttributes(['class' => 'text-xs'])
                    ->label('Catatan')
                    ->wrap(),

                // Custom column untuk detail
                ViewColumn::make('details')
                    ->label('Documents')
                    ->extraAttributes(['class' => 'text-xs'])
                    ->view('livewire.akreditasi.ep.list-documents'),
            ])
            ->recordActions([
                Action::make('upload')
                    ->iconButton()
                    ->icon('tabler-book-upload')
                    ->action(
                        fn($record, $livewire) => $livewire->modal(
                            modal: 'modal-manage-document-ep',
                            id: $record->getKey()
                        )
                    )
                    ->visible(
                        fn() =>
                        auth()->user()->hasRole('Super-Admin') or
                            !auth()->user()->can('assesor-akreditasi')

                    )
            ])
            ->paginated(false);;
    }

    public function modal($modal, $id)
    {
        $this->elementSelectedId = $id;
        $this->dispatch('open-modal', id: "{$modal}-{$this->modalPreffix}");
    }

    public function modalViewDocument($id, $modal)
    {
        $this->docSelectedId = $id;
        $this->dispatch('open-modal', id: "{$modal}-{$this->modalPreffix}");
    }

    public function render()
    {
        return view('livewire.akreditasi.ep.table-ep');
    }
}
