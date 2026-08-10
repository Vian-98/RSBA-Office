<?php

namespace App\Livewire\Gaji\Concerns;

use App\Models\Sdm\Karyawan;

trait HasPayrollFormProperties
{
    public bool $isInputModalOpen = false;
    public ?int $selectedKaryawanId = null;
    public ?Karyawan $selectedKaryawan = null;

    // Form fields
    public $form_gaji_pokok = 0;
    public $form_tunjangan_tetap = 0;
    public $form_tunjangan_absensi = 0;
    public $form_tunjangan_jabatan = 0;
    public $form_tunjangan_shift = 0;
    public $form_tunjangan_radiologi = 0;
    public $form_tunjangan_lain = 0;
    public $form_uang_lembur = 0;
    public $form_tunjangan_hari_raya = 0;
    
    public $form_potongan_absensi = 0;
    public $form_potongan_cash_bon = 0;
    public $form_potongan_obat = 0;
    public $form_potongan_lain = 0;
    public $form_potongan_bank = 0;
    public $form_potongan_bpjs_kes = 0;
    public $form_potongan_bpjs_tk = 0;
    public int $form_bpjs_keluarga_tambahan = 0;

    // PPh 21 Override Form Fields
    public $form_potongan_pph21 = 0;
    public $form_pph21_calculated = 0;
    public bool $form_pph21_is_overridden = false;
    public string $form_pph21_override_reason = '';

    // December YTD reconciliation fields
    public bool $is_december = false;
    public $ytd_prior_bruto = 0.0;
    public $ytd_prior_pph21 = 0.0;
    public $ytd_prior_bpjs_tk = 0.0;
    public $ytd_total_bruto = 0.0;
    public $ytd_total_bpjs_tk = 0.0;
    public $ytd_biaya_jabatan = 0.0;
    public $ytd_neto = 0.0;
    public $ytd_ptkp = 0.0;
    public $ytd_pkp = 0.0;
    public $ytd_tax_annual = 0.0;
    public $ytd_paid_jan_nov = 0.0;

    public array $form_umk_allocations = [];
    public array $form_tunjangan_lain_items = [];
    
    public ?int $temp_allowance_type_id = null;
    public $temp_allowance_nominal = 0;

    // Calculated fields
    public $calc_bpjs_kes = 0;
    public $calc_bpjs_tk = 0;
    public $calc_pph21 = 0;
    public $calc_total_gaji = 0;
    public $calc_total_potongan = 0;
    public $calc_gaji_bersih = 0;
    public $calc_golongan = null;
    public $calc_masa_kerja = 0.0;
}
