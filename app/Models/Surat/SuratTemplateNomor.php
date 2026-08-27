<?php

namespace App\Models\Surat;

use App\Models\Sdm\Jabatan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SuratTemplateNomor extends Model
{
    protected $table = 'surat_template_nomor';
    protected $guarded = [];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Ambil template nomor berdasarkan jenis surat
     */
    public static function getTemplate(string $jenisSurat): string
    {
        $record = static::where('jenis_surat', $jenisSurat)->first();
        if ($record && !empty($record->format_nomor)) {
            return $record->format_nomor;
        }

        return match ($jenisSurat) {
            'balasan_pkl'        => '{no}/S4/B-PKL/PBA-{kode_jabatan}/{tanggal}',
            'balasan_penelitian' => '{no}/S4/B-PNL/PBA-{kode_jabatan}/{tanggal}',
            'perintah_tugas'     => '{no}/S4/SPT/PBA-{kode_jabatan}/{tanggal}',
            default              => '{no}/S4/{kode_jabatan}/{tanggal}',
        };
    }

    /**
     * Generate format nomor surat lengkap dengan placeholder tag
     */
    public static function generateNomor(string $jenisSurat, ?int $jabatanId, string $tanggal, int $nextNo): string
    {
        $template = static::getTemplate($jenisSurat);
        $jabatan  = $jabatanId ? Jabatan::find($jabatanId) : null;
        $kodeJabatan = $jabatan?->kode_surat ?? 'DIR';

        $timestamp = strtotime($tanggal ?: date('Y-m-d'));
        $tglDotted = date('d.m.Y', $timestamp);
        $dd        = date('d', $timestamp);
        $mm        = date('m', $timestamp);
        $yyyy      = date('Y', $timestamp);

        $result = str_replace(
            ['{no}', '{kode_jabatan}', '{tanggal}', '{dd}', '{mm}', '{yyyy}', '{tahun}'],
            [$nextNo, $kodeJabatan, $tglDotted, $dd, $mm, $yyyy, $yyyy],
            $template
        );

        return $result;
    }
}
