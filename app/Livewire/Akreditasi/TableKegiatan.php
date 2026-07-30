<?php

namespace App\Livewire\Akreditasi;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Models\Akreditasi\AkreKegiatan;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;

class TableKegiatan extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(AkreKegiatan::with('user'))
            ->columns([
                TextColumn::make('nama')
                    ->label('Kegiatan'),

                TextColumn::make('tanggal')
                    ->label('Rencana Pelaksanaan'),

                TextColumn::make('standard')
                    ->label('Standar Akreditasi'),

                TextColumn::make('lembaga')
                    ->label('Lembaga Survey'),

                TextColumn::make('total_nilai')
                    ->label('Nilai'),

            ])
            ->recordActions([
                Action::make('masuk')
                    ->iconButton()
                    ->icon('tabler-table')
                    ->url(
                        fn($record) => route(
                            'kepegawaian.akreditasi.chapters',
                            ['uuid' => $record->uuid]
                        )
                    )
            ])
            ->recordUrl(
                fn($record) => route(
                    'kepegawaian.akreditasi.chapters',
                    ['uuid' => $record->uuid]
                )
            );
    }

    public function render()
    {
        return view('livewire.akreditasi.table-kegiatan');
    }
}
