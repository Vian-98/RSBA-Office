<?php

namespace App\Livewire\Gaji;

use App\Models\Sdm\Karyawan;
use App\Models\Sdm\Bagian;
use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

#[Title('Penggajian')]
class Index extends Component
{
    use WithPagination;
    use AuthorizesFromRoute;
    use Interactions;

    public string $search = '';
    public string $bagianFilter = '';

    // Modal state
    public bool $isOpenModal = false;
    public ?array $selectedSlip = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBagianFilter(): void
    {
        $this->resetPage();
    }

    public function viewSlip(int $karyawanId): void
    {
        $karyawan = Karyawan::with(['jabatan.bagian'])->find($karyawanId);
        if (!$karyawan) {
            return;
        }

        $calc = $this->calculateSalary($karyawan);
        $this->selectedSlip = [
            'id' => $karyawan->id,
            'nama' => $karyawan->full_nama,
            'nip' => $karyawan->nip,
            'status' => $karyawan->status->nama(),
            'jabatan' => $calc['jabatan_nama'],
            'bagian' => $calc['bagian_nama'],
            'gaji_pokok' => $calc['gaji_pokok'],
            'tunjangan' => $calc['tunjangan'],
            'bpjs_kes' => $calc['bpjs_kes'],
            'bpjs_ket' => $calc['bpjs_ket'],
            'pajak' => $calc['pajak'],
            'gaji_bersih' => $calc['gaji_bersih'],
            'periode' => CarbonTranslate(now(), 'F Y'),
        ];
        $this->isOpenModal = true;
    }

    public function closeModal(): void
    {
        $this->isOpenModal = false;
        $this->selectedSlip = null;
    }

    private function calculateSalary(Karyawan $karyawan): array
    {
        // 1. Basic Salary by Status
        $gajiPokok = match ($karyawan->status?->value) {
            'tetap' => 5000000,
            'kontrak' => 3500000,
            'mitra' => 4500000,
            'bantuan' => 3000000,
            'magang' => 2000000,
            default => 3000000,
        };

        // 2. Allowance by Jabatan
        $jabName = 'Staff';
        $bagName = 'Umum';
        $latestJab = $karyawan->jabatan->first();
        if ($latestJab) {
            $jabName = $latestJab->nama;
            $bagName = optional($latestJab->bagian)->nama ?? 'Umum';
        }

        $tunjangan = match ($jabName) {
            'Direktur Utama' => 15000000,
            'Kepala Bagian SDM', 'Kepala Bagian Umum', 'Kepala Bagian Keuangan' => 5000000,
            'Staff Pelaksana SDM', 'Staff Pelaksana Umum', 'Staff Pelaksana Keuangan' => 1500000,
            default => 500000,
        };

        // 3. Deductions
        $bpjsKes = 150000;
        $bpjsKet = 100000;
        $pajak = round(0.05 * ($gajiPokok + $tunjangan));

        // 4. Net Salary
        $gajiBersih = ($gajiPokok + $tunjangan) - ($bpjsKes + $bpjsKet + $pajak);

        return [
            'jabatan_nama' => $jabName,
            'bagian_nama' => $bagName,
            'gaji_pokok' => $gajiPokok,
            'tunjangan' => $tunjangan,
            'bpjs_kes' => $bpjsKes,
            'bpjs_ket' => $bpjsKet,
            'pajak' => $pajak,
            'gaji_bersih' => $gajiBersih,
        ];
    }

    public function sendEmail(int $karyawanId): void
    {
        $karyawan = Karyawan::with(['jabatan.bagian', 'user'])->find($karyawanId);
        if (!$karyawan) {
            $this->toast()->error('Gagal !', 'Karyawan tidak ditemukan.')->send();
            return;
        }

        $email = optional($karyawan->user)->email;
        if (!$email) {
            $this->toast()->warning('Peringatan !', 'Karyawan ini tidak memiliki akun user atau alamat email terdaftar.')->send();
            return;
        }

        $calc = $this->calculateSalary($karyawan);
        $slipData = [
            'nama' => $karyawan->full_nama,
            'nip' => $karyawan->nip,
            'status' => $karyawan->status->nama(),
            'jabatan' => $calc['jabatan_nama'],
            'bagian' => $calc['bagian_nama'],
            'periode' => CarbonTranslate(now(), 'F Y'),
            'gaji_pokok' => $calc['gaji_pokok'],
            'tunjangan' => $calc['tunjangan'],
            'bpjs_kes' => $calc['bpjs_kes'],
            'bpjs_ket' => $calc['bpjs_ket'],
            'pajak' => $calc['pajak'],
            'gaji_bersih' => $calc['gaji_bersih'],
        ];

        try {
            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\SlipGajiMail($slipData));
            $this->toast()->success('Berhasil !', 'Slip gaji berhasil dikirim ke email: ' . $email)->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', 'Error saat mengirim email: ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        $this->authorizeFromRoute();

        $query = Karyawan::query()
            ->with(['jabatan.bagian']);

        if (!empty($this->search)) {
            $query->where('nama', 'like', '%' . $this->search . '%');
        }

        if (!empty($this->bagianFilter)) {
            $query->whereHas('jabatan.bagian', function ($q) {
                $q->where('id', $this->bagianFilter);
            });
        }

        $karyawans = $query->paginate(10);

        // Transform collection to append calculated salary
        $karyawans->getCollection()->transform(function ($karyawan) {
            $calc = $this->calculateSalary($karyawan);
            $karyawan->calculated_salary = $calc;
            return $karyawan;
        });

        return view('livewire.gaji.index', [
            'karyawans' => $karyawans,
            'bagians' => Bagian::all(),
        ]);
    }
}

// Simple Carbon translated format helper
function CarbonTranslate($carbonDate, $format)
{
    return \Carbon\Carbon::parse($carbonDate)->translatedFormat($format);
}
