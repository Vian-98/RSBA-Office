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
    public $jk;
    public $status_pernikahan;
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
    public $bpjs_kesehatan;
    public $bpjs_tk;

    public $jabatan;
    public $tgl_jabatan;
    public $dinas;
    public $tgl_dinas;
    public $ket_dinas;

    public $tgl_status;

    public $ruangan;
    public $kategori_kerja;

    function mount($karyawan)
    {
        $this->karyawan = $karyawan;
    }

    // rules for when store data
    protected function rules(): array
    {
        return [
            'status' => 'required',
            'nama' => 'required|string',
            'nik' => 'required|int|digits_between:16,16',
            'tempat_lahir' => 'required|string',
            'tgl_lahir' => 'required|date',
            'jk' => 'required',
            'hp' => 'required',
            'prov' => 'required',
            'kab' => 'required',
            'kec' => 'required',
            'desa' => 'required',
            'alamat' => 'required',
            'bpjs_kesehatan' => 'nullable|string|max:50',
            'bpjs_tk' => 'nullable|string|max:50'
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
        $this->status_pernikahan = $karyawan->status_pernikahan;
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
        $this->bpjs_kesehatan = $karyawan->bpjs_kesehatan;
        $this->bpjs_tk = $karyawan->bpjs_tk;
    }

    // set using different compoenent
    function setKedinasan(Karyawan $karyawan)
    {
        $this->status = $karyawan->status;
        $this->jabatan = $karyawan->jabatan[0]->id ?? '';
        $this->dinas = $karyawan->resign ?? '';
        $this->ruangan = $karyawan->ruangan_id;
        $this->kategori_kerja = $karyawan->kategori_kerja?->value ?? 'reguler';
    }

    // simpan data
    public function store()
    {
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
            "bpjs_kesehatan" => $this->bpjs_kesehatan,
            "bpjs_tk" => $this->bpjs_tk,
            "ruangan_id" => empty($this->ruangan) ? null : $this->ruangan,
            "kategori_kerja" => empty($this->kategori_kerja) ? 'reguler' : $this->kategori_kerja,
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
            'gelar_depan' => $this->gelar_depan,
            'gelar_belakang' => $this->gelar_belakang,
            'nik' => $this->nik,
            'npwp' => $this->npwp,
            'tempat_lahir' => $this->tempat_lahir,
            'tgl_lahir' => $this->tgl_lahir,
            'agama' => $this->agama,
            'suku' => $this->suku,
            'jk' => $this->jk,
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
            'dom_alamat' => $this->dom_alamat,
            'bpjs_kesehatan' => $this->bpjs_kesehatan,
            'bpjs_tk' => $this->bpjs_tk
        ];

        if (auth()->user()->hasRole('Staff-SDM') || auth()->user()->hasRole('Super-Admin')) {
            $data['tgl_masuk'] = $this->tgl_masuk;
        }

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
         * mean : 2 fixed, 2 based on statusKarywan, 24 tahun , 0001 nomor urut based on statusKaryawan
         */

        $statusKode = StatusKaryawan::from($status)->idNIP();
        $tahun  = Carbon::parse($tanggal)->format('y');

        $lastNip = Karyawan::where('status', $status)
            // ->whereYear('created_at', Carbon::now()->year)
            ->orderBy('nip', 'desc')
            ->first();

        $incrementNumber = 1; // Default if no NIP exists for the status in this year

        if ($lastNip) {
            // Extract the last four digits and increment by 1
            $incrementNumber = (int)substr($lastNip->nip, -4) + 1;
        }

        // Format increment number to be four digits
        $incrementNumber = str_pad($incrementNumber, 4, '0', STR_PAD_LEFT);

        return "2" . $statusKode . $tahun . $incrementNumber;
    }
}
