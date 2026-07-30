<?php

namespace App\Livewire\Jasmed\Dokter;

use Throwable;
use Livewire\Component;
use App\Models\JmDokter;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    #[Locked]
    public $jm_pasien_id;

    // public JmDokter $dokter;
    public array $dokters = [];

    public array $statusOptions  = [
        ['value' => 'sp', 'label' => 'Spesialis'],
        ['value' => 'um', 'label' => 'Umum'],
        ['value' => 'an', 'label' => 'Anastesi'],
        ['value' => 'dpjp', 'label' => 'DPJP'],
        ['value' => 'um_s', 'label' => 'Umum Sertifikat'],
        ['value' => 'sppdkgh', 'label' => 'SPPD-KGH'],
        ['value' => 'dpjp_hd', 'label' => 'DPJP HD'],
    ];


    public function mount($id)
    {
        $this->jm_pasien_id = $id;
        $this->dokters = JmDokter::where('jm_pasien_id', $id)->get()->toArray();
        // $this->jm_pasien_id = $id;
    }

    public function submit()
    {
        // Validate the dokters array
        $this->validate([
            'dokters.*.dokter' => 'required|string|max:255',
            'dokters.*.status' => 'required|in:sp,um,an,dpjp,um_s,sppdkgh,dpjp_hd',
        ]);


        DB::beginTransaction();
        try {
            $NewIncomingId = [];

            // // Save each dokter
            foreach ($this->dokters as $dokter) {
                // $data = [
                //     'jm_pasien_id' => $this->jm_pasien_id,
                //     'dokter' => $dokter['dokter'],
                //     'status' => $dokter['status'],
                //     'jumlah' => $dokter['jumlah'] ?? 1
                // ];

                // Update record database
                $records = JmDokter::updateOrCreate(
                    [
                        'id' => $dokter['id'] ?? null,
                        'jm_pasien_id' => $this->jm_pasien_id,

                    ],
                    [
                        'jm_pasien_id' => $this->jm_pasien_id,
                        'dokter' => $dokter['dokter'],
                        'status' => $dokter['status'],
                        'jumlah' => $dokter['jumlah'] ?? 1,
                    ]
                );

                // save id into newInconmingId
                $NewIncomingId[] = $records->id;
            }

            // Delete database yang tidak terecord dalam newIncomingId
            JmDokter::where('jm_pasien_id', $this->jm_pasien_id)
                ->whereNotIn('id', $NewIncomingId)
                ->delete();

            DB::commit();

            $this->toast()
                ->success('Berhasil', 'Dokter visite berhasil diperbaharui.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Tidak Berhasil', 'Dokter visite tidak berhasil diperbaharui. Error: ' . $e->getMessage())
                ->send();
        }

        // // Optionally, you can reset the dokters array or redirect
        // $this->dispatch('close-modal', id: 'modal-edit-dokter');
    }

    public function render()
    {
        return view('livewire.jasmed.dokter.edit');
    }
}
