<?php

namespace App\Livewire\Maintenance\Permintaan;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

#[Lazy]
class PermintaanApproval extends Component
{
    use Interactions;

    public ?object $maintenanceRequest;

    public ?string $approval = null, $priority = 'normal';
    public ?string $jadwal;
    public ?array $teknisi_id = [];
    public ?string $catatan_teknisi = null, $ket_reject = null;

    public $priorityOptions = [
        ['value' => 'normal', 'label' => 'Normal',],
        ['value' => 'penting', 'label' => 'Penting',],
        ['value' => 'darurat', 'label' => 'Darurat',]
    ];

    public function rules(): array
    {
        return [
            'approval' => 'required',
            'jadwal' => $this->approval === 'approved' ? 'required' : 'nullable',
            'teknisi_id' => $this->approval === 'approved' ? 'required' : 'nullable',
            'priority' => $this->approval === 'approved' ? 'required' : 'nullable',
            'ket_reject' => $this->approval === 'rejected' ? 'required' : 'nullable'
        ];
    }

    public function mount($maintenanceRequest): void
    {
        $this->maintenanceRequest = $maintenanceRequest;
        $this->priority = $maintenanceRequest->priority ?? 'normal';
    }

    public function submit()
    {

        $this->validate();
        $is_setuju  = $this->approval === 'approved' ? 'Disetujui' : 'Ditolak';

        DB::beginTransaction();
        try {

            // message 

            // 01 Update status permintaan maintenance
            $this->maintenanceRequest->update([
                'status' => $this->approval,
                'user_verify_id' => auth()->id(),
                'ket_reject' => $this->ket_reject,
            ]);

            if ($this->approval === 'rejected') {
                $this->maintenanceRequest->asset->update([
                    'status' => 'baik',
                ]);
                $this->maintenanceRequest->asset->components->each(function ($component) {
                    $component->update([
                        'status' => 'baik',
                    ]);
                });
            }

            // 02 Add jadwal dan teknisi jika disetujui
            if ($this->approval === 'approved') {
                $jadwal =  $this->maintenanceRequest->jadwal()->create([
                    'asset_id' => $this->maintenanceRequest->asset_id,
                    'maintc_request_id' => $this->maintenanceRequest->id,
                    'tanggal' => $this->jadwal,
                    'priority' => $this->priority,
                    'note' => $this->catatan_teknisi,
                ]);


                // Assign teknisi
                $teknisiMapping = collect($this->teknisi_id)->map(function ($value, $index) {
                    return [
                        'teknisi_id' => $value,
                        'role' => $index === 0 ? 'leader' : 'helper',
                    ];
                })->toArray();

                // Create teknisi for the jadwal
                $jadwal->teknisi()->createMany(
                    $teknisiMapping
                );

                // Fetch names of assigned technicians
                $teknisiNames = \App\Models\User::whereIn('id', $this->teknisi_id)
                    ->with('karyawan')
                    ->get()
                    ->map(fn($u) => $u->karyawan?->nama ?? $u->name)
                    ->implode(', ');

                // Log approval
                \App\Models\Maintenance\TicketComment::create([
                    'request_id' => $this->maintenanceRequest->id,
                    'user_id'    => auth()->id(),
                    'body'       => 'Tiket disetujui & dijadwalkan oleh ' . (auth()->user()?->karyawan?->nama ?? auth()->user()?->name) . ' untuk teknisi: ' . ($teknisiNames ?: '-') . ' pada tanggal ' . \Carbon\Carbon::parse($this->jadwal)->format('d M Y'),
                    'type'       => 'log',
                ]);
            } else {
                // Log rejection
                \App\Models\Maintenance\TicketComment::create([
                    'request_id' => $this->maintenanceRequest->id,
                    'user_id'    => auth()->id(),
                    'body'       => 'Tiket ditolak oleh ' . (auth()->user()?->karyawan?->nama ?? auth()->user()?->name) . ($this->ket_reject ? ' dengan alasan: ' . $this->ket_reject : ''),
                    'type'       => 'log',
                ]);
            }

            // 03 Commit transaction
            DB::commit();

            $this->dispatch('submit-approval-beli-request');

            $this->toast()
                ->success('Berhasil', "Permintaan maintenance {$is_setuju}.")
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->success('Terjadi Kesalahan', "<i>{$e->getMessage()}</i> <br> Silahkan coba lagi.")
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.maintenance.permintaan.permintaan-approval');
    }
}
