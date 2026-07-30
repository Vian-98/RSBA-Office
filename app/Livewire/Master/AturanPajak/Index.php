<?php

namespace App\Livewire\Master\AturanPajak;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

class Index extends Component
{
    use Interactions;

    public string $activeTab = 'ptkp';
    
    // PTKP Properties
    public array $ptkpForm = [];

    // TER Properties
    public string $activeTerCategory = 'A';
    public array $terForm = [];

    // Pasal 17 Properties
    public array $pasal17Form = [];

    public function mount()
    {
        $this->loadPtkp();
        $this->loadTer();
        $this->loadPasal17();
    }

    public function loadPtkp()
    {
        $ptkpRecords = DB::table('sdm_payroll_ptkp')
            ->orderBy('status', 'asc')
            ->get();

        $this->ptkpForm = [];
        foreach ($ptkpRecords as $record) {
            $this->ptkpForm[$record->id] = [
                'status' => $record->status,
                'nominal_setahun' => (int) $record->nominal_setahun,
            ];
        }
    }

    public function loadTer()
    {
        $terRecords = DB::table('sdm_payroll_ter')
            ->where('kategori', $this->activeTerCategory)
            ->orderBy('bruto_bawah', 'asc')
            ->get();

        $this->terForm = [];
        foreach ($terRecords as $record) {
            $this->terForm[$record->id] = [
                'bruto_bawah' => (int) $record->bruto_bawah,
                'bruto_atas' => (int) $record->bruto_atas,
                'tarif_persen' => (float) $record->tarif_persen,
            ];
        }
    }

    public function loadPasal17()
    {
        $pasal17Records = DB::table('sdm_payroll_pasal17')
            ->orderBy('pkp_bawah', 'asc')
            ->get();

        $this->pasal17Form = [];
        foreach ($pasal17Records as $record) {
            $this->pasal17Form[$record->id] = [
                'pkp_bawah' => (int) $record->pkp_bawah,
                'pkp_atas' => $record->pkp_atas !== null ? (int) $record->pkp_atas : '',
                'tarif_persen' => (float) $record->tarif_persen,
            ];
        }
    }

    public function changeTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function changeTerCategory(string $category)
    {
        $this->activeTerCategory = $category;
        $this->loadTer();
    }

    public function savePtkp()
    {
        DB::beginTransaction();
        try {
            foreach ($this->ptkpForm as $id => $data) {
                // Parse potential strings with dot separator
                $nominal = $data['nominal_setahun'];
                if (is_string($nominal)) {
                    $nominal = str_replace('.', '', $nominal);
                }
                $nominal = (double) $nominal;

                if ($nominal < 0) {
                    $this->toast()->error('Gagal !', 'Nominal PTKP tidak boleh negatif.')->send();
                    return;
                }

                DB::table('sdm_payroll_ptkp')
                    ->where('id', $id)
                    ->update([
                        'nominal_setahun' => $nominal,
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            $this->loadPtkp();
            $this->toast()->success('Berhasil !', 'Aturan batas PTKP berhasil disimpan.')->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal !', 'Error: ' . $e->getMessage())->send();
        }
    }

    public function saveTer()
    {
        DB::beginTransaction();
        try {
            foreach ($this->terForm as $id => $data) {
                $tarif = (double) $data['tarif_persen'];

                if ($tarif < 0 || $tarif > 100) {
                    $this->toast()->error('Gagal !', 'Tarif persen TER harus berada di antara 0% dan 100%.')->send();
                    return;
                }

                DB::table('sdm_payroll_ter')
                    ->where('id', $id)
                    ->update([
                        'tarif_persen' => $tarif,
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            $this->loadTer();
            $this->toast()->success('Berhasil !', 'Tarif TER Kategori ' . $this->activeTerCategory . ' berhasil disimpan.')->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal !', 'Error: ' . $e->getMessage())->send();
        }
    }

    public function savePasal17()
    {
        DB::beginTransaction();
        try {
            foreach ($this->pasal17Form as $id => $data) {
                $pkpBawah = $data['pkp_bawah'];
                if (is_string($pkpBawah)) {
                    $pkpBawah = str_replace('.', '', $pkpBawah);
                }
                $pkpBawah = (double) $pkpBawah;

                $pkpAtas = $data['pkp_atas'];
                if ($pkpAtas !== '' && $pkpAtas !== null) {
                    if (is_string($pkpAtas)) {
                        $pkpAtas = str_replace('.', '', $pkpAtas);
                    }
                    $pkpAtas = (double) $pkpAtas;
                } else {
                    $pkpAtas = null;
                }

                $tarif = (double) $data['tarif_persen'];

                if ($pkpBawah < 0 || ($pkpAtas !== null && $pkpAtas < 0)) {
                    $this->toast()->error('Gagal !', 'Batas PKP tidak boleh bernilai negatif.')->send();
                    return;
                }

                if ($pkpAtas !== null && $pkpAtas <= $pkpBawah) {
                    $this->toast()->error('Gagal !', 'Batas PKP atas harus lebih besar dari batas bawah.')->send();
                    return;
                }

                if ($tarif < 0 || $tarif > 100) {
                    $this->toast()->error('Gagal !', 'Tarif persen Pasal 17 harus berada di antara 0% dan 100%.')->send();
                    return;
                }

                DB::table('sdm_payroll_pasal17')
                    ->where('id', $id)
                    ->update([
                        'pkp_bawah' => $pkpBawah,
                        'pkp_atas' => $pkpAtas,
                        'tarif_persen' => $tarif,
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
            $this->loadPasal17();
            $this->toast()->success('Berhasil !', 'Tarif PPh 21 progresif (Pasal 17) berhasil disimpan.')->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()->error('Gagal !', 'Error: ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.master.aturan-pajak.index')
            ->layout('layouts.app');
    }
}
