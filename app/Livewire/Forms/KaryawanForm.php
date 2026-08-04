<?php

namespace App\Livewire\Forms;

use Throwable;
use Carbon\Carbon;
use Livewire\Form;
use App\Models\Sdm\Karyawan;
use App\Enums\StatusKaryawan;
use Illuminate\Support\Facades\DB;

class KaryawanForm extends Form
{
    public ?Karyawan $karyawan;

    public $status = 'kontrak';
    public $status_pernikahan = 'belum_menikah';
    public $jk = 'L';

    public $tgl_masuk;
    public $nip;
    public $nama;
    public $gelar_depan;
    public $gelar_belakang;
    public $nik;
    public $npwp;
    public $tempat_lahir;
    public $tgl_lahir;
    public $agama;
    public $suku;
    public $hp;
    public $hp2;
    public $prov;
    public $kab;
    public $kec;
    public $desa;
    public $alamat;
    public $dom_prov;
    public $dom_kab;
    public $dom_kec;
    public $dom_desa;
    public $dom_alamat;

    public $jabatan;
    public $bagian;
    public $tgl_jabatan;
    public $dinas;
    public $tgl_dinas;
    public $ket_dinas;

    public $tgl_status;

    public $ruangan;
    public $tgl_ruangan;
    public $kategori_kerja = 'shift';

    function mount($karyawan)
    {
        $this->karyawan = $karyawan;
    }

    // rules for when store data
    protected function rules(): array
    {
        return [
            // Kolom wajib di database.
            'status' => 'required|in:kontrak,tetap,mitra,bantuan,magang',
            'tgl_masuk' => 'required|date',
            'nama' => 'required|string|max:50',
            'nik' => 'required|string|digits:16',
            'tgl_lahir' => 'required|date',
            'hp' => 'required|string|max:15',
            'status_pernikahan' => 'required|in:belum_menikah,menikah,janda_duda',
            'agama' => 'required|in:islam,kristen,katolik,hindu,budha,khonghucu',
            'prov' => 'required|string|max:50',
            'kab' => 'required|string|max:50',
            'kec' => 'required|string|max:50',
            'desa' => 'required|string|max:50',
            'alamat' => 'required|string|max:225',

            // Kolom nullable/default di database.
            'jk' => 'nullable|in:L,P',
            'kategori_kerja' => 'required|in:shift,reguler',
            'tempat_lahir' => 'nullable|string|max:30',
            'gelar_depan' => 'nullable|string|max:50',
            'gelar_belakang' => 'nullable|string|max:150',
            'npwp' => 'nullable|string|max:50',
            'suku' => 'nullable|string|max:25',
            'hp2' => 'nullable|string|max:15',
            'dom_prov' => 'nullable|string|max:50',
            'dom_kab' => 'nullable|string|max:50',
            'dom_kec' => 'nullable|string|max:50',
            'dom_desa' => 'nullable|string|max:50',
            'dom_alamat' => 'nullable|string|max:255',
        ];
    }

    // set using different compoenent
    function setIdentitas(Karyawan $karyawan)
    {
        $this->nama = $karyawan->nama;
        $this->nip = $karyawan->nip;
        $this->tgl_masuk = $karyawan->tgl_masuk;
        $this->gelar_depan = $karyawan->gelar_depan;
        $this->gelar_belakang = $karyawan->gelar_belakang;
        $this->nik = $karyawan->nik;
        $this->npwp = $karyawan->npwp;
        $this->tempat_lahir = $karyawan->tempat_lahir;
        $this->tgl_lahir = $karyawan->tgl_lahir;
        $this->agama = $karyawan->agama;
        $this->suku = $karyawan->suku;
        $this->jk = $karyawan->jk;
        $this->status_pernikahan = match ($karyawan->status_pernikahan) {
            'belum', 'belum_menikah', null, '' => 'belum_menikah',
            'single', 'janda_duda' => 'janda_duda',
            default => $karyawan->status_pernikahan,
        };
        $this->hp = $karyawan->hp;
        $this->hp2 = $karyawan->hp2;
        $this->prov = $karyawan->prov;
        $this->kab = $karyawan->kab;
        $this->kec = $karyawan->kec;
        $this->desa = $karyawan->desa;
        $this->alamat = $karyawan->alamat;
        $this->dom_prov = $karyawan->dom_prov;
        $this->dom_kab = $karyawan->dom_kab;
        $this->dom_kec = $karyawan->dom_kec;
        $this->dom_desa = $karyawan->dom_desa;
        $this->dom_alamat = $karyawan->dom_alamat;
    }

    // set using different compoenent
    function setKedinasan(Karyawan $karyawan)
    {
        $this->status = $karyawan->status;
        $jabatan = $karyawan->jabatan->first();
        $this->jabatan = $jabatan?->id ?? '';
        $this->bagian = $jabatan?->pivot?->bagian_id ?? $jabatan?->bagian_id ?? '';
        $this->ruangan = $karyawan->ruangan_id ?? '';
        $this->dinas = $karyawan->resign ?? '';
        $this->kategori_kerja = $karyawan->kategori_kerja?->value ?? 'shift';
    }

    // simpan data
    public function store()
    {
        if (empty($this->tgl_masuk)) {
            $this->tgl_masuk = now()->toDateString();
        }

        $this->nip = $this->createNip($this->status, $this->tgl_masuk);

        $data = [
            "nip" => $this->nip,
            "nama" => $this->nama,
            "jk" => $this->jk,
            "tgl_masuk" => $this->tgl_masuk,
            "tempat_lahir" => $this->tempat_lahir,
            "tgl_lahir" => $this->tgl_lahir,
            "hp" => $this->hp,
            "hp2" => $this->hp2,
            "status_pernikahan" => $this->status_pernikahan,
            "alamat" => $this->alamat,
            "nik" => $this->nik,
            "gelar_depan" => $this->gelar_depan,
            "gelar_belakang" => $this->gelar_belakang,
            "status" => $this->status,
            "kategori_kerja" => $this->kategori_kerja ?? 'shift',
            "prov" => $this->prov,
            "kab" => $this->kab,
            "kec" => $this->kec,
            "desa" => $this->desa,
            "dom_prov" => $this->dom_prov,
            "dom_kab" => $this->dom_kab,
            "dom_kec" => $this->dom_kec,
            "dom_desa" => $this->dom_desa,
            "dom_alamat" => $this->dom_alamat,
            "agama" => $this->agama,
            "suku" => $this->suku,
            "npwp" => $this->npwp,
            "cuti" => 0

        ];

        DB::beginTransaction();
        try {
            Karyawan::create($data);
            DB::commit();

            return [
                'status' => 'sukses',
                'message' => 'Inserted'
            ];
        } catch (Throwable $e) {
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // update and using different livewire component
    public function updateIdentitas()
    {
        $data = [
            'nama' => $this->nama,
            'tgl_masuk' => $this->tgl_masuk,
            'gelar_depan' => $this->gelar_depan,
            'gelar_belakang' => $this->gelar_belakang,
            'nik' => $this->nik,
            'npwp' => $this->npwp,
            'tempat_lahir' => $this->tempat_lahir,
            'tgl_lahir' => $this->tgl_lahir,
            'agama' => $this->agama,
            'suku' => $this->suku,
            'jk' => $this->jk,
            'status_pernikahan' => $this->status_pernikahan,
            'hp' => $this->hp,
            'hp2' => $this->hp2,
            'prov' => $this->prov,
            'kab' => $this->kab,
            'kec' => $this->kec,
            'desa' => $this->desa,
            'alamat' => $this->alamat,
            'dom_prov' => $this->dom_prov,
            'dom_kab' => $this->dom_kab,
            'dom_kec' => $this->dom_kec,
            'dom_desa' => $this->dom_desa,
            'dom_alamat' => $this->dom_alamat
        ];

        $this->karyawan->update($data);
    }


    // validate and update using different livewire component
    function updateKedinasan($data)
    {
        $this->karyawan->update($data);
    }


    // create NIP
    protected function createNip($status, $tanggal): string
    {
        /**
         * eg : 22240001
         * mean : 2 fixed, 2 based on statusKaryawan, 24 tahun , 0001 nomor urut based on statusKaryawan
         */

        $statusKode = StatusKaryawan::from($status)->idNIP();
        $tahun  = Carbon::parse($tanggal)->format('y');
        $prefix = "2" . $statusKode . $tahun;

        // Cari NIP tertinggi dengan prefix yang sama
        $lastNip = Karyawan::where('nip', 'like', $prefix . '%')
            ->orderBy('nip', 'desc')
            ->first();

        $incrementNumber = 1;

        if ($lastNip) {
            $incrementNumber = (int)substr($lastNip->nip, -4) + 1;
        }

        // Garansi NIP Unik: terus increment sampai menemukan NIP yang belum digunakan di DB
        do {
            $formattedIncrement = str_pad($incrementNumber, 4, '0', STR_PAD_LEFT);
            $nip = $prefix . $formattedIncrement;
            $exists = Karyawan::where('nip', $nip)->exists();
            if ($exists) {
                $incrementNumber++;
            }
        } while ($exists);

        return $nip;
    }
}
