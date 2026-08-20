<?php

namespace App\Livewire\Forms\Karyawan;

use Livewire\Form;
use App\Models\Sdm\Karyawan;
use App\Enums\StatusKaryawan;
use App\Enums\KategoriKerja;

class EditKedinasanForm extends Form
{
    public ?Karyawan $karyawan = null;

    public $status = '';
    public $tgl_status = '';
    public $kategori_kerja = 'shift';

    public $jabatan = '';
    public $bagian = '';
    public $tgl_jabatan = '';
    public $no_sk_jabatan = '';
    public $document_id_jabatan = null;

    public $ruangan = '';
    public $tgl_ruangan = '';
    public $no_sk_ruangan = '';
    public $document_id_ruangan = null;

    public $dinas = '';
    public $tgl_dinas = '';
    public $ket_dinas = '';

    public $pendidikan_setara = '';
    public $pendidikan_golongan = '';

    public function setKedinasan(Karyawan $karyawan): void
    {
        $this->karyawan = $karyawan;
        $this->status = $karyawan->status instanceof StatusKaryawan
            ? $karyawan->status->value
            : (string) ($karyawan->status ?? '');
        $jabatan = $karyawan->jabatan->first();
        $this->jabatan = $jabatan?->id ?? '';
        $this->bagian = $jabatan?->pivot?->bagian_id ?? $jabatan?->bagian_id ?? '';
        $this->ruangan = $karyawan->ruangan_id ?? '';
        $this->dinas = $karyawan->resign ?? '';
        $this->kategori_kerja = $karyawan->kategori_kerja instanceof KategoriKerja
            ? $karyawan->kategori_kerja->value
            : (string) ($karyawan->kategori_kerja?->value ?? 'shift');
        $this->pendidikan_setara = $karyawan->pendidikan_setara ?? '';
    }

    public function autoSetGolongan(): void
    {
        // Placeholder for auto golongan calculation if matrix is applied
    }
}
