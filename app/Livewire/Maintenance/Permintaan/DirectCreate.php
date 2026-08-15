<?php

namespace App\Livewire\Maintenance\Permintaan;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Assets\AssetBarang;
use App\Models\Maintenance\Request as MaintenanceRequest;
use App\Models\Maintenance\TicketComment;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

class DirectCreate extends Component
{
    use Interactions;
    use WithFileUploads;

    public $asset_id;
    public string $workflow_type = 'approved'; // 'pending' or 'approved'
    public string $priority = 'normal';
    public ?string $ket_priority = null;
    public string $note = '';
    
    // Direct scheduling fields (when workflow_type === 'approved')
    public ?string $jadwal = null;
    public array $teknisi_id = [];
    public ?string $catatan_teknisi = null;

    // Attachments
    public array $lampirans = [];

    public array $priorityOptions = [
        ['value' => 'normal', 'label' => 'Normal'],
        ['value' => 'penting', 'label' => 'Penting / Urgent'],
        ['value' => 'darurat', 'label' => 'Darurat / Emergency'],
    ];

    public array $workflowOptions = [
        ['value' => 'approved', 'label' => 'Setujui & Dijadwalkan Langsung'],
        ['value' => 'pending', 'label' => 'Simpan Sebagai Permintaan Baru (Pending)'],
    ];

    public function rules(): array
    {
        return [
            'asset_id' => 'required|exists:asset_barang,id',
            'workflow_type' => 'required|in:pending,approved',
            'priority' => 'required|in:normal,penting,darurat',
            'ket_priority' => $this->priority !== 'normal' ? 'required|string|max:255' : 'nullable',
            'note' => 'required|string|max:1000',
            'jadwal' => $this->workflow_type === 'approved' ? 'required|date' : 'nullable',
            'teknisi_id' => $this->workflow_type === 'approved' ? 'required|array|min:1' : 'nullable',
            'lampirans.*' => 'nullable|image|max:10240', // Max 10MB per image
        ];
    }

    public function messages(): array
    {
        return [
            'asset_id.required' => 'Pilih unit aset barang terlebih dahulu.',
            'note.required' => 'Kendala / deskripsi perbaikan wajib diisi.',
            'ket_priority.required' => 'Alasan prioritas penting/darurat wajib diisi.',
            'jadwal.required' => 'Tanggal pelaksanaan maintenance wajib diisi.',
            'teknisi_id.required' => 'Pilih setidaknya 1 teknisi penanggung jawab.',
        ];
    }

    public function mount()
    {
        if (!auth()->user()?->can('approval-maintenance')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat tiket maintenance direct.');
        }
        $this->jadwal = now()->format('Y-m-d');
    }

    public function resetForm()
    {
        $this->reset(['asset_id', 'ket_priority', 'note', 'teknisi_id', 'catatan_teknisi', 'lampirans']);
        $this->workflow_type = 'approved';
        $this->priority = 'normal';
        $this->jadwal = now()->format('Y-m-d');
    }

    public function submit()
    {
        if (!auth()->user()?->can('approval-maintenance')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat tiket maintenance direct.');
        }
        $this->validate();

        DB::beginTransaction();
        try {
            $asset = AssetBarang::findOrFail($this->asset_id);

            // Check if asset is already under active maintenance
            $hasActiveTicket = MaintenanceRequest::where('asset_id', $asset->id)
                ->active()
                ->exists();

            if ($hasActiveTicket || $asset->status === 'diperbaiki') {
                $this->addError('asset_id', 'Aset ' . ($asset->kode ?? '') . ' (' . ($asset->barang?->nama ?? 'Aset') . ') sedang dalam perbaikan/maintenance aktif. Selesaikan perbaikan yang berjalan terlebih dahulu.');
                DB::rollBack();
                return;
            }

            // Upload lampiran if any
            $lampiranPaths = [];
            if (!empty($this->lampirans)) {
                foreach ($this->lampirans as $file) {
                    $lampiranPaths[] = $file->store('maintenance/lampiran', 'public');
                }
            }

            if ($this->workflow_type === 'approved') {
                // Direct Approved & Scheduled
                $maintReq = MaintenanceRequest::create([
                    'asset_id' => $asset->id,
                    'user_req_id' => auth()->id() ?? 1,
                    'priority' => $this->priority,
                    'ket_priority' => $this->priority !== 'normal' ? $this->ket_priority : 'Direct Ticket (Approved)',
                    'note' => $this->note,
                    'status' => 'approved',
                    'user_verify_id' => auth()->id(),
                    'lampiran' => !empty($lampiranPaths) ? $lampiranPaths : null,
                ]);

                $jadwal = $maintReq->jadwal()->create([
                    'asset_id' => $asset->id,
                    'maintc_request_id' => $maintReq->id,
                    'tanggal' => $this->jadwal,
                    'priority' => $this->priority,
                    'note' => $this->catatan_teknisi,
                ]);

                $teknisiMapping = collect($this->teknisi_id)->map(function ($value, $index) {
                    return [
                        'teknisi_id' => $value,
                        'role' => $index === 0 ? 'leader' : 'helper',
                    ];
                })->toArray();

                $jadwal->teknisi()->createMany($teknisiMapping);

                $teknisiNames = \App\Models\User::whereIn('id', $this->teknisi_id)
                    ->with('karyawan')
                    ->get()
                    ->map(fn($u) => $u->karyawan?->nama ?? $u->name)
                    ->implode(', ');

                TicketComment::create([
                    'request_id' => $maintReq->id,
                    'user_id'    => auth()->id(),
                    'body'       => 'Tiket Maintenance Direct disetujui & dijadwalkan oleh ' . (auth()->user()?->karyawan?->nama ?? auth()->user()?->name) . ' untuk teknisi: ' . ($teknisiNames ?: '-') . ' pada tanggal ' . \Carbon\Carbon::parse($this->jadwal)->format('d M Y'),
                    'type'       => 'log',
                ]);

                $asset->update(['status' => 'diperbaiki']);
            } else {
                // Direct Pending Request
                $maintReq = MaintenanceRequest::create([
                    'asset_id' => $asset->id,
                    'user_req_id' => auth()->id() ?? 1,
                    'priority' => $this->priority,
                    'ket_priority' => $this->priority !== 'normal' ? $this->ket_priority : null,
                    'note' => $this->note,
                    'status' => 'pending',
                    'lampiran' => !empty($lampiranPaths) ? $lampiranPaths : null,
                ]);

                TicketComment::create([
                    'request_id' => $maintReq->id,
                    'user_id'    => auth()->id(),
                    'body'       => 'Tiket Maintenance Direct diajukan oleh ' . (auth()->user()?->karyawan?->nama ?? auth()->user()?->name),
                    'type'       => 'log',
                ]);

                $asset->update(['status' => 'diperbaiki']);
            }

            DB::commit();

            $this->dispatch('close-modal', id: 'modal-direct-create-maintenance');
            $this->dispatch('maintenance-ticket-created');
            $this->dispatch('submit-approval-beli-request');

            $this->toast()->success('Berhasil', 'Tiket Maintenance Direct berhasil dibuat.')->send();
            $this->resetForm();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal', 'Terjadi kesalahan: ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.maintenance.permintaan.direct-create');
    }
}
