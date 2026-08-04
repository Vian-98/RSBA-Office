<?php

namespace App\Livewire\Maintenance\Permintaan;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Assets\AssetBarang;
use App\Models\Assets\AssetMaintenanceSchedule;
use App\Models\Maintenance\Request as MaintenanceRequest;
use App\Models\Maintenance\TicketComment;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\DB;

class AnnualSchedule extends Component
{
    use Interactions;

    public int $asset_id;

    public ?int $editingId = null;
    public string $judul = 'Service / Pengecekan Rutin';
    public string $interval_unit = 'month';
    public int $interval_value = 1;
    public ?string $tgl_mulai = null;
    public ?string $catatan = null;

    protected $rules = [
        'judul' => 'required|string|max:255',
        'interval_unit' => 'required|in:month,year',
        'interval_value' => 'required|integer|min:1|max:60',
        'tgl_mulai' => 'required|date',
        'catatan' => 'nullable|string|max:1000',
    ];

    public function mount(int $asset_id)
    {
        $this->asset_id = $asset_id;
        $this->tgl_mulai = now()->format('Y-m-d');
    }

    public function resetForm()
    {
        $this->reset(['editingId', 'catatan']);
        $this->judul = 'Service / Pengecekan Rutin';
        $this->interval_unit = 'month';
        $this->interval_value = 1;
        $this->tgl_mulai = now()->format('Y-m-d');
    }

    private function checkAuthorization(): bool
    {
        if (!auth()->user()?->can('approval-maintenance')) {
            $this->toast()->error('Terlarang', 'Anda tidak memiliki hak akses untuk mengelola jadwal maintenance berkala.')->send();
            return false;
        }
        return true;
    }

    public function saveSchedule()
    {
        if (!$this->checkAuthorization()) return;

        $this->validate();

        $asset = AssetBarang::findOrFail($this->asset_id);
        $tglMulai = Carbon::parse($this->tgl_mulai);

        if ($this->editingId) {
            $schedule = AssetMaintenanceSchedule::where('asset_barang_id', $this->asset_id)
                ->findOrFail($this->editingId);

            $schedule->update([
                'judul' => $this->judul,
                'interval_unit' => $this->interval_unit,
                'interval_value' => $this->interval_value,
                'tgl_mulai' => $tglMulai->format('Y-m-d'),
                'tgl_berikutnya' => $tglMulai->format('Y-m-d'),
                'catatan' => $this->catatan,
            ]);

            $this->toast()->success('Berhasil', 'Jadwal maintenance berkala berhasil diperbarui.')->send();
        } else {
            AssetMaintenanceSchedule::create([
                'asset_barang_id' => $this->asset_id,
                'judul' => $this->judul,
                'interval_unit' => $this->interval_unit,
                'interval_value' => $this->interval_value,
                'tgl_mulai' => $tglMulai->format('Y-m-d'),
                'tgl_berikutnya' => $tglMulai->format('Y-m-d'),
                'catatan' => $this->catatan,
                'is_active' => true,
            ]);

            $this->toast()->success('Berhasil', 'Jadwal maintenance berkala baru berhasil ditambahkan.')->send();
        }

        $this->resetForm();
    }

    public function editSchedule(int $id)
    {
        if (!$this->checkAuthorization()) return;

        $schedule = AssetMaintenanceSchedule::where('asset_barang_id', $this->asset_id)
            ->findOrFail($id);

        $this->editingId = $schedule->id;
        $this->judul = $schedule->judul;
        $this->interval_unit = $schedule->interval_unit;
        $this->interval_value = $schedule->interval_value;
        $this->tgl_mulai = $schedule->tgl_mulai ? $schedule->tgl_mulai->format('Y-m-d') : now()->format('Y-m-d');
        $this->catatan = $schedule->catatan;
    }

    public function toggleSchedule(int $id)
    {
        if (!$this->checkAuthorization()) return;

        $schedule = AssetMaintenanceSchedule::where('asset_barang_id', $this->asset_id)
            ->findOrFail($id);

        $schedule->update(['is_active' => !$schedule->is_active]);
        $statusText = $schedule->is_active ? 'diaktifkan' : 'dinonaktifkan';

        $this->toast()->info('Status Diubah', "Jadwal maintenance berhasil {$statusText}.")->send();
    }

    public function deleteSchedule(int $id)
    {
        if (!$this->checkAuthorization()) return;

        AssetMaintenanceSchedule::where('asset_barang_id', $this->asset_id)
            ->where('id', $id)
            ->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        $this->toast()->success('Dihapus', 'Jadwal maintenance berkala telah dihapus.')->send();
    }

    public function triggerNow(int $id)
    {
        if (!$this->checkAuthorization()) return;

        DB::beginTransaction();
        try {
            $schedule = AssetMaintenanceSchedule::where('asset_barang_id', $this->asset_id)
                ->findOrFail($id);

            $asset = AssetBarang::findOrFail($this->asset_id);

            // Check if asset is already under active maintenance
            $hasActiveTicket = MaintenanceRequest::where('asset_id', $asset->id)
                ->active()
                ->exists();

            if ($hasActiveTicket || $asset->status === 'diperbaiki') {
                DB::rollBack();
                $this->toast()->error('Gagal', 'Aset sedang dalam perbaikan/maintenance aktif. Selesaikan perbaikan yang berjalan terlebih dahulu.')->send();
                return;
            }

            // Buat tiket perbaikan/inspeksi rutin
            $maintReq = MaintenanceRequest::create([
                'asset_id' => $asset->id,
                'user_req_id' => auth()->id() ?? 1,
                'priority' => 'normal',
                'ket_priority' => 'Inspeksi / Service Berkala',
                'note' => "[JADWAL BERKALA] " . $schedule->judul . ($schedule->catatan ? " - " . $schedule->catatan : ""),
                'status' => 'pending',
            ]);

            TicketComment::create([
                'request_id' => $maintReq->id,
                'user_id'    => auth()->id() ?? 1,
                'body'       => 'Tiket Inspeksi/Service Rutin Berkala dipicu oleh ' . (auth()->user()?->karyawan?->nama ?? auth()->user()?->name ?? 'Sistem') . ' untuk ' . ($asset->barang->nama ?? 'Asset'),
                'type'       => 'log',
            ]);

            $asset->update(['status' => 'diperbaiki']);

            // Update siklus jadwal berikutnya
            $nextDate = $schedule->calculateNextDueDate(now());
            $schedule->update([
                'terakhir_dilakukan' => now()->format('Y-m-d'),
                'tgl_berikutnya' => $nextDate->format('Y-m-d'),
            ]);

            DB::commit();

            $this->toast()->success('Tiket Dibuat', 'Tiket perbaikan berkala berhasil dibuat & jadwal berikutnya telah di-update.')->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal', 'Terjadi kesalahan saat memicu tiket berkala: ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        $schedules = AssetMaintenanceSchedule::where('asset_barang_id', $this->asset_id)
            ->latest()
            ->get();

        return view('livewire.maintenance.permintaan.annual-schedule', [
            'schedules' => $schedules
        ]);
    }
}
