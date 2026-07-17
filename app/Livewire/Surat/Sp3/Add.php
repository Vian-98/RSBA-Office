<?php

namespace App\Livewire\Surat\Sp3;

use Throwable;
use Exception;
use App\Models\Master\Supplier;
use App\Models\Sdm\Jabatan;
use App\Models\Surat\SuratSp3;
use App\Models\Surat\SuratSp3Approval;
use App\Models\Surat\SuratSp3Detail;
use App\Models\User;
use App\Services\DigitalSignatureService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    #[Locked]
    public ?SuratSp3 $suratSp3;

    public array $caraBayarOptions = [
        ['label' => 'Tunai', 'value' => 'tunai'],
        ['label' => 'Transfer', 'value' => 'trf'],
        ['label' => 'Giro', 'value' => 'giro'],
    ];

    public $mengetahuiOptions;
    public $tgl;
    public string $rekanan, $keterangan = '', $method_bayar;
    public ?int $mengetahui, $jabatan, $rekananId, $userApprove;
    public $listSp3 = [];

    protected $rules = [
        'tgl' => 'required',
        'rekanan' => 'required',
        'method_bayar' => 'required',
        'keterangan' => 'required',
        'jabatan' => 'required',
        'listSp3' => 'required|array|min:1'
    ];

    public function messages()
    {
        return [
            'listSp3.required' => 'Rincikan item pembayarannya.',
            'listSp3.min' => 'Silahkan rincikan item pembayarannya.'
        ];
    }

    protected DigitalSignatureService $digitalSignatureService;

    public function boot(DigitalSignatureService $digitalSignatureService)
    {
        $this->digitalSignatureService = $digitalSignatureService;
    }

    public function mount()
    {
        $this->tgl = date('Y-m-d');
        $this->mengetahuiOptions = Jabatan::with('bagian')
            ->whereHas('bagian', function ($query) {
                $query->where('group', 'manajemen');
            })
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->nama,
                    'value' => $item->id
                ];
            });
    }

    public function updatedRekananId($value)
    {
        // get nama vendor;
        $supplier = Supplier::findOrFail($value);

        if ($supplier) {
            $this->rekanan = $supplier->nama;
        } else {
            $this->toast()
                ->error('Not Found', 'Data supplier tidak ditemukan.')
                ->send();
        }
    }

    function updatedJabatan($value)
    {
        // get id user karyawan base jabatan
        $jabatan = Jabatan::find($value);

        if ($jabatan) {
            $karyawanJabatan = $jabatan->jabatans()
                ->where('tgl_berakhir', null)
                ->orderBy('id', 'desc')
                ->first();

            if ($karyawanJabatan) {
                $this->mengetahui = $karyawanJabatan->karyawan?->id;
                $this->userApprove = $karyawanJabatan->karyawan?->user?->id;
            } else {
                $this->toast()
                    ->error('Pejabat tidak ditemukan.', "Tidak ada karyawan dengan jabatan <b>{$jabatan->nama}</b>.")
                    ->send();
            }
        } else {
            $this->toast()
                ->error('Harus Diisi', 'Mengetahui harus dipilih.')
                ->send();
        }
    }

    public function submit($send = true)
    {
        $this->validate();
        $data = [
            'no' => $this->createNomor(),
            'tahun' => date('Y', strtotime($this->tgl)),
            'tgl' => $this->tgl,
            'rekanan' => $this->rekanan,
            'bayar' => $this->method_bayar,
            'keterangan' => $this->keterangan,
            'jabatan_id' => $this->jabatan,
            'created_by' => auth()->user()->id,
        ];

        DB::beginTransaction();
        try {
            $suratSp3 = SuratSp3::create($data);

            // mapping detail sp3
            $itemsDetail = collect($this->listSp3)
                ->map(
                    function ($item) use ($suratSp3) {
                        return [
                            'sp3_id' => $suratSp3->id,
                            'keterangan' => $item['keterangan'],
                            'nominal' => $item['nominal'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                )->toArray();
            // insert into database
            SuratSp3Detail::insert($itemsDetail);

            // Manual dan printout
            if (!$send) {
                $this->signManual($suratSp3);
            }

            // Sync immediately to docstore
            app(\App\Services\DocstoreSyncService::class)->syncSp3($suratSp3);

            DB::commit();
            $this->dispatch('created-sp3');
            $this->toast()
                ->success('Berhasil', 'SP3 berhasil disimpan.')
                ->send();

            // If Manual , Direct to Printou
            if (!$send) {
                $this->js("setTimeout(() => \$dispatch('print-out-sp3'), 1500)");
            }
        } catch (Throwable $th) {
            DB::rollBack();

            $this->toast()
                ->error('Gagal Menyimpan Data', 'error : ' . $th->getMessage())
                ->send();
        }
    }

    public function signManual($suratSp3)
    {
        $data = [
            'surat_sp3_id' => $suratSp3->id,
            'disetujui' => $this->userApprove,
            'status' => 'approved',  // Manual cetak langsung approved oleh sistem
            'keterangan' => 'Manual cetak, tanda tangan sistem',
            'approved_at' => now()->toIso8601String(),
        ];

        // ambil data signature user [p12 path] , jika manual, gunakan tanda tangan Super Admin,
        // Super Admin credential mewakili credential sistem,
        $user = User::find(1);
        if (!$user) {
            throw new Exception("User Super Admin tidak ditemukan.");
        }
        $certificate = $user->certificate()->latest('id')->first();

        if (!$certificate) {
            throw new Exception("Tidak memiliki certificate.");
        }

        // buat signature hash dari p12
        $dataToSign = json_encode($data);

        $passwordCertificate = null;

        // Proses tanda tangan data
        $signature = $this->digitalSignatureService->signData(
            user: $user,
            data: $dataToSign,
            password: $passwordCertificate,
            type: 'persetujuan_sp3',
            id: $suratSp3->id
        );

        if (!$signature['status']) {
            throw new Exception($signature['message']);
        }

        $data['signature_hash'] = $signature['data_hash']; //adding hash to data

        SuratSp3Approval::create($data);
        $suratSp3->update(
            [
                'status' => 'approved'
            ]
        );

        // return
        $this->suratSp3 = $suratSp3;
    }

    private function createNomor()
    {
        // Format Nomor {no}/S4/SP.3/PBA-{kode_surat_jabatan (A10,A11,A12)}/{tanggal 14.04.2025}

        $last = SuratSp3::select('no')
            ->where('jabatan_id', $this->jabatan)
            ->orderBy('id', 'desc')
            ->first();

        $jabatan = Jabatan::find($this->jabatan);

        $tanggal = date('d.m.Y', strtotime($this->tgl));
        $no = 1;
        if ($last) {
            $fullNomor = explode('/', $last->no);
            $lastNomor = $fullNomor[0];
            $no = (int)$lastNomor + 1;
        }
        return "{$no}/S4/SP.3/PBA-{$jabatan->kode_surat}/{$tanggal}";
    }

    public function render()
    {
        return view('livewire.surat.sp3.add');
    }
}
