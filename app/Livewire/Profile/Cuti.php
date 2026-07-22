<?php

namespace App\Livewire\Profile;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Enums\StatusApproval;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratCuti;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Lazy]
class Cuti extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithTable, InteractsWithForms;

    #[Locked]
    public ?Karyawan $karyawan;

    #[Locked]
    public ?SuratCuti $surat;

    public ?array $cutiStats = null;

    public function mount($id)
    {
        $this->karyawan = Karyawan::findOrFail($id);
        $this->loadCutiStats();
    }

    private function loadCutiStats()
    {
        $k = $this->karyawan;
        if (!$k) return;

        $tglMasuk = \Carbon\Carbon::parse($k->tgl_masuk);
        $now = \Carbon\Carbon::now();

        // Calculate next reset date (next anniversary)
        $anniversaryThisYear = $tglMasuk->copy()->year($now->year);
        if ($now->gte($anniversaryThisYear)) {
            $startDate = $anniversaryThisYear;
            $nextReset = $anniversaryThisYear->copy()->addYear();
        } else {
            $startDate = $anniversaryThisYear->copy()->subYear();
            $nextReset = $anniversaryThisYear;
        }

        $quota = 12; // Standard annual quota
        
        $sisa = $k->sisa_cuti; // uses our dynamic accessor!
        
        if ($sisa < 0) {
            $this->cutiStats = [
                'eligible' => false,
                'quota' => $quota,
                'used' => 0,
                'sisa' => 0,
                'next_reset' => $tglMasuk->copy()->addYear()->translatedFormat('d F Y'),
            ];
        } else {
            // Count used/pending cuti in the current anniversary period
            $used = $k->suratCuti()
                ->where('urgensi_id', 1)
                ->where('status', '!=', 'rejected')
                ->whereBetween('tgl_mulai', [$startDate->format('Y-m-d'), $nextReset->format('Y-m-d')])
                ->sum('lama_cuti');

            $this->cutiStats = [
                'eligible' => true,
                'quota' => $quota,
                'used' => $used,
                'sisa' => $sisa,
                'next_reset' => $nextReset->translatedFormat('d F Y'),
            ];
        }
    }

    public static function table(Table $tableCuti): Table
    {
        return $tableCuti
            ->query(
                SuratCuti::query()->where('karyawan_id', Auth::user()->karyawan_id)
                    ->latest()
            )
            ->deferLoading(false)
            ->columns([
                TextColumn::make('no_surat')
                    ->label('No Surat Cuti')
                    ->description(fn(SuratCuti $record) => $record->sumber === 'cuti_bersama' ? 'Cuti Bersama Sistem' : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(
                        fn($state) => $state->nama()
                    )
                    ->badge()
                    ->color(fn($state) => $state->color())
                    ->action(
                        function ($record, $livewire) {
                            if ($record->status === StatusApproval::APPROVED) {
                                $livewire->surat = SuratCuti::find($record->getKey());
                                $livewire->dispatch('trigger-print-cuti');
                                return;
                            } else {
                                $livewire->modalTrigger(
                                    modal: 'modal-status-cuti',
                                    id: $record->getKey()
                                );
                            }
                        }
                    ),

                TextColumn::make('tgl_surat')
                    ->label('Tgl Surat'),

                TextColumn::make('lama_cuti')
                    ->label('Lama Cuti')
                    ->formatStateUsing(fn(SuratCuti $record) => $record->lama_cuti . " Hari")
                    ->action(
                        fn(SuratCuti $record, $livewire) => $livewire->modalTrigger(modal: 'view-detil-tanggal', id: $record->getKey())
                    )
                    ->tooltip('Click : untuk detil tanggal.'),

                TextColumn::make('tgl_mulai')
                    ->label('Mulai Cuti'),

                TextColumn::make('tgl_akhir')
                    ->label('Berakhir Cuti'),
            ]);
    }

    // modal detil tanggal cuti
    function modalTrigger($modal, $id)
    {
        $this->surat = SuratCuti::findOrFail($id);
        $this->dispatch('open-modal', id: $modal);
    }

    public function render()
    {
        return view('livewire.profile.cuti');
    }
}
