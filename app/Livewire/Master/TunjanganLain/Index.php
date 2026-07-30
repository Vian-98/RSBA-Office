<?php

namespace App\Livewire\Master\TunjanganLain;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;

#[Title('Master Jenis Tunjangan Lain-Lain')]
class Index extends Component
{
    use WithPagination;
    use Interactions;

    public string $search = '';
    
    // Form fields
    public ?int $editingId = null;
    public string $nama = '';
    public string $keterangan = '';
    public bool $isModalOpen = false;

    protected $rules = [
        'nama' => 'required|string|max:255',
        'keterangan' => 'nullable|string',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openModal(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->editingId = $id;

        if ($id) {
            $type = DB::table('sdm_payroll_allowance_types')->where('id', $id)->first();
            if ($type) {
                $this->nama = $type->nama;
                $this->keterangan = $type->keterangan ?? '';
            }
        } else {
            $this->nama = '';
            $this->keterangan = '';
        }

        $this->isModalOpen = true;
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->editingId = null;
        $this->nama = '';
        $this->keterangan = '';
    }

    public function save(): void
    {
        $this->validate();

        // Check if name is unique, excluding current editing ID
        $exists = DB::table('sdm_payroll_allowance_types')
            ->where('nama', $this->nama)
            ->when($this->editingId, function ($query) {
                $query->where('id', '!=', $this->editingId);
            })
            ->exists();

        if ($exists) {
            $this->addError('nama', 'Nama jenis tunjangan ini sudah digunakan.');
            return;
        }

        DB::beginTransaction();
        try {
            DB::table('sdm_payroll_allowance_types')->updateOrInsert(
                ['id' => $this->editingId],
                [
                    'nama' => $this->nama,
                    'keterangan' => $this->keterangan,
                    'updated_at' => now(),
                    'created_at' => $this->editingId ? DB::raw('created_at') : now(),
                ]
            );

            DB::commit();

            $this->toast()
                ->success('Berhasil !', 'Jenis tunjangan lain-lain berhasil disimpan.')
                ->send();

            $this->closeModal();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Gagal !', 'Error: ' . $e->getMessage())
                ->send();
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->dialog()
            ->question('Hapus Tipe Tunjangan?', 'Apakah Anda yakin ingin menghapus tipe tunjangan ini? Semua alokasi slip gaji yang terhubung dengannya juga akan ikut terhapus.')
            ->confirm('Ya, Hapus', 'delete', $id)
            ->cancel('Batal')
            ->send();
    }

    public function delete(int $id): void
    {
        DB::beginTransaction();
        try {
            DB::table('sdm_payroll_allowance_types')->where('id', $id)->delete();
            DB::commit();

            $this->toast()
                ->success('Berhasil !', 'Jenis tunjangan lain-lain berhasil dihapus.')
                ->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Gagal !', 'Error: ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        $query = DB::table('sdm_payroll_allowance_types');

        if (!empty($this->search)) {
            $query->where('nama', 'like', '%' . $this->search . '%');
        }

        $allowanceTypes = $query->paginate(10);

        return view('livewire.master.tunjangan-lain.index', [
            'allowanceTypes' => $allowanceTypes,
        ]);
    }
}
