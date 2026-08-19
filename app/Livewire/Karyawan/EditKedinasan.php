<?php

namespace App\Livewire\Karyawan;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\KaryawanDocument;
use App\Enums\StatusKaryawan;
use App\Enums\KategoriKerja;
use App\Livewire\Forms\KaryawanForm;
use App\Models\Sdm\KaryawanJabatan;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;
use TallStackUi\Traits\Interactions;

#[Lazy]
class EditKedinasan extends Component
{
    use Interactions;

    public KaryawanForm $form;
    public ?Karyawan $karyawan = null;
    public $karyawanId;

    public $status_options = [];
    public $status_init = '';
    public $kategori_options = [];
    public $kategori_init = '';
    public $jabatan_options = [];
    public $bagian_options = [];
    public $jabatan_init = '';
    public $bagian_init = '';

    public $dinas_options = [
        ['id' => 'resign', 'label' => 'Resign / Mengundurkan Diri'],
        ['id' => 'dipecat', 'label' => 'Dipecat'],
        ['id' => 'end_kontrak', 'label' => 'Habis Kontrak'],
    ];
    public $dinas_init = '';
    public $ruangan_init = '';
    public $dinas;
    public $tgl_dinas;
    public $pendidikan_options = [];
    public $auto_pendidikan_label = '';
    public $document_options = [];
    public ?KaryawanDocument $previewDocument = null;

    public function rules(): array
    {
        return [
            'form.status' => 'required',
            'form.kategori_kerja' => 'required',
            'form.jabatan' => 'required',
            'form.bagian' => 'required|exists:bagian,id',
            'form.tgl_status' => Rule::requiredIf(fn() => (string) ($this->form->status instanceof StatusKaryawan ? $this->form->status->value : $this->form->status) !== (string) ($this->status_init instanceof StatusKaryawan ? $this->status_init->value : $this->status_init)),
            'form.tgl_jabatan' => Rule::requiredIf(fn() =>
                $this->form->jabatan != $this->jabatan_init || $this->form->bagian != $this->bagian_init
            ),
            'form.no_sk_jabatan' => Rule::requiredIf(fn() =>
                $this->form->jabatan != $this->jabatan_init || $this->form->bagian != $this->bagian_init
            ),
            'form.document_id_jabatan' => 'nullable|exists:sdm_kary_document,id',
            'form.tgl_ruangan' => Rule::requiredIf(fn() => $this->form->ruangan != $this->ruangan_init),
            'form.no_sk_ruangan' => Rule::requiredIf(fn() => $this->form->ruangan != $this->ruangan_init),
            'form.document_id_ruangan' => 'nullable|exists:sdm_kary_document,id',
            'form.tgl_dinas' => Rule::requiredIf(fn() => $this->form->dinas != $this->dinas_init)
        ];
    }

    public function boot(): void
    {
        $this->status_options = StatusKaryawan::options();
        $this->kategori_options = KategoriKerja::options();
        $this->jabatan_options = Jabatan::all();
        $this->bagian_options = Bagian::query()->where('is_active', true)->orderBy('nama')->get();
    }

    public function hydrate(): void
    {
        if ($this->karyawanId && !$this->karyawan) {
            $this->karyawan = Karyawan::find($this->karyawanId);
            if ($this->karyawan) {
                $this->form->mount($this->karyawan);
            }
        }
    }

    public function mount($id)
    {
        $this->karyawanId = $id;
        $karyawan = Karyawan::findOrFail($id);
        $this->karyawan = $karyawan;
        $this->form->mount($karyawan); //new instance form

        $this->form->setKedinasan($karyawan);

        $this->status_options = StatusKaryawan::options();
        $this->status_init = $karyawan->status instanceof StatusKaryawan ? $karyawan->status->value : (string) ($karyawan->status ?? '');

        $this->kategori_options = KategoriKerja::options();
        $this->kategori_init = $karyawan->kategori_kerja instanceof KategoriKerja ? $karyawan->kategori_kerja->value : (string) ($karyawan->kategori_kerja?->value ?? 'shift');

        $this->jabatan_options = Jabatan::all();
        $this->bagian_options = Bagian::query()->where('is_active', true)->orderBy('nama')->get();
        $this->jabatan_init = $this->form->jabatan;
        $this->bagian_init = $this->form->bagian;
        $this->ruangan_init = $karyawan->ruangan_id ?? '';

        $this->loadDocumentOptions($id);

        // Load education options from matrix groups & current auto default
        $groups = \Illuminate\Support\Facades\DB::table('sdm_payroll_golongan_matrix')
            ->orderBy('urutan_kelompok', 'asc')
            ->get()
            ->pluck('kelompok_pendidikan')
            ->unique()
            ->values()
            ->toArray();

        $options = [['value' => '', 'label' => '[Otomatis sesuai Pendidikan Terakhir]']];
        foreach ($groups as $g) {
            $options[] = ['value' => $g, 'label' => $g];
        }
        $this->pendidikan_options = $options;

        $allPendidikan = \Illuminate\Support\Facades\DB::table('sdm_kary_pendidikan')
            ->where('karyawan_id', $id)
            ->get();

        $tingkat = 'sma';
        $maxScore = 0;
        $scoreMap = [
            's2' => 4, 's3' => 4, 'spesialis' => 4,
            's1' => 3, 'profesi' => 3, 'dokter' => 3,
            'd3' => 2, 'd4' => 2,
            'sd' => 1, 'smp' => 1, 'sma' => 1, 'lain' => 1,
        ];

        foreach ($allPendidikan as $p) {
            $score = $scoreMap[$p->tingkat] ?? 1;
            if ($score > $maxScore) {
                $maxScore = $score;
                $tingkat = $p->tingkat;
            }
        }

        $this->auto_pendidikan_label = \App\Enums\TingkatPendidikan::tryFrom($tingkat)?->nama() ?? 'SMA';
    }

    public function loadDocumentOptions($karyawanId): void
    {
        $docs = KaryawanDocument::where('karyawan_id', $karyawanId)
            ->orderBy('created_at', 'desc')
            ->get();

        $options = [];
        foreach ($docs as $d) {
            $options[] = [
                'id' => $d->id,
                'nama' => $d->nama . ' (' . strtoupper($d->jenis) . ')',
            ];
        }
        $this->document_options = $options;
    }

    #[On('document-karyawan-created')]
    #[On('document-karyawan-deleted')]
    public function refreshDocuments(): void
    {
        if ($this->form->karyawan) {
            $this->loadDocumentOptions($this->form->karyawan->id);
        }
    }

    public function viewDocument(int $documentId): void
    {
        $doc = KaryawanDocument::find($documentId);
        if (!$doc) {
            $this->toast()->error('File tidak ditemukan', 'Dokumen SK tidak tersedia di database.')->send();
            return;
        }

        $this->previewDocument = $doc;
        $this->dispatch('open-modal', id: 'view-sk-document-modal');
    }

    public function updated($field)
    {
        // When status is changed to pns / ptt_daerah / ptt_pusat -> auto set golongan
        if ($field === 'form.status' && in_array($this->form->status, ['pns', 'ptt_daerah', 'ptt_pusat'])) {
            $this->form->autoSetGolongan();
        }

        // When custom pendidikan matrix selection changes -> re-evaluate golongan
        if ($field === 'form.pendidikan_golongan') {
            $this->form->autoSetGolongan();
        }
    }

    public function update()
    {
        $this->validate();

        $formStatus = $this->form->status instanceof StatusKaryawan ? $this->form->status->value : (string) $this->form->status;
        $initStatus = $this->status_init instanceof StatusKaryawan ? $this->status_init->value : (string) $this->status_init;

        // update status
        if ($formStatus !== $initStatus) {
            $this->updateStatus();
        }

        $formKategori = $this->form->kategori_kerja instanceof KategoriKerja ? $this->form->kategori_kerja->value : (string) $this->form->kategori_kerja;
        $initKategori = $this->kategori_init instanceof KategoriKerja ? $this->kategori_init->value : (string) $this->kategori_init;

        // update kategori kerja
        if ($formKategori !== $initKategori) {
            $this->updateKategoriKerja();
        }

        // update jabatan
        if ($this->form->jabatan != $this->jabatan_init || $this->form->bagian != $this->bagian_init) {
            $this->updateJabatan();
        }

        // update ruangan
        if ($this->form->ruangan != $this->ruangan_init) {
            $this->updateRuangan();
        }

        // update dinas
        if ($this->form->dinas != $this->dinas_init) {
            $this->updateDinas();
        }
    }

    public function updateKategoriKerja()
    {
        try {
            $kategoriVal = $this->form->kategori_kerja instanceof KategoriKerja ? $this->form->kategori_kerja->value : (string) $this->form->kategori_kerja;
            $this->form->karyawan->update([
                'kategori_kerja' => $kategoriVal,
            ]);
            $this->kategori_init = $kategoriVal;
            $this->dispatch('kategori-kerja-updated');
            $this->toast()
                ->success('Sukses', 'Kategori kerja berhasil diperbarui.')
                ->send();
        } catch (Throwable $th) {
            $this->toast()
                ->error('Failed', 'Error : ' . $th->getMessage())
                ->send();
        }
    }

    public function updateStatus()
    {
        try {
            $statusVal = $this->form->status instanceof StatusKaryawan ? $this->form->status->value : (string) $this->form->status;
            $data = [
                'status'     => $statusVal,
                'tgl_status' => $this->form->tgl_status,
            ];

            // update
            $this->form->karyawan->update($data);
            $this->status_init = $statusVal;
            $this->kategori_init = $this->form->kategori_kerja instanceof KategoriKerja ? $this->form->kategori_kerja->value : (string) $this->form->kategori_kerja;

            // event
            $this->dispatch('status-updated');

            // toast
            $this->toast()
                ->success('Sukses', 'Update data kedinasan berhasil.')
                ->send();
        } catch (Throwable $th) {
            $this->toast()
                ->error('Failed', 'Error : ' . $th->getMessage())
                ->send();
        }
    }

    public function updateJabatan()
    {
        $karyawan = $this->form->karyawan;

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // Tutup SEMUA jabatan aktif (tgl_berakhir IS NULL) agar tidak ada duplikat pejabat aktif
            KaryawanJabatan::where('karyawan_id', $this->form->karyawan->id)
                ->whereNull('tgl_berakhir')
                ->update(['tgl_berakhir' => $this->form->tgl_jabatan]);

            $documentId = $this->form->document_id_jabatan;
            if (is_array($documentId)) {
                $documentId = $documentId['id'] ?? $documentId['value'] ?? (isset($documentId[0]) ? $documentId[0] : null);
            }
            $documentId = empty($documentId) ? null : (int) $documentId;

            $data = [
                'jabatan_id'  => $this->form->jabatan,
                'karyawan_id' => $this->form->karyawan->id,
                'bagian_id'   => $this->form->bagian,
                'tgl_mulai'   => $this->form->tgl_jabatan,
                'no_sk'       => $this->form->no_sk_jabatan,
                'document_id' => $documentId,
            ];

            // insert data new jabatan
            KaryawanJabatan::create($data);

            // Auto-sync role user jika terhubung dengan akun user
            $karyawan->user?->syncRoleFromJabatan();

            // Cek jika jabatan baru adalah level struktural (tingkat_id <= 3 / Kabag / Wadir / Direktur)
            // dan karyawan memiliki penugasan koordinator aktif
            $newJabatan = Jabatan::find($this->form->jabatan);
            if ($newJabatan && $newJabatan->tingkat_id <= 3) {
                $hasActiveKoor = \App\Models\Sdm\RuanganKoordinator::where('karyawan_id', $this->form->karyawan->id)
                    ->where('aktif', true)
                    ->exists();

                if ($hasActiveKoor) {
                    $this->toast()
                        ->info('Perhatian Koordinator', 'Karyawan ini masih memiliki penugasan Koordinator Ruangan aktif. Harap periksa menu Penugasan Koordinator bila penugasan lama perlu dinonaktifkan.')
                        ->send();
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            $this->jabatan_init = $this->form->jabatan;
            $this->bagian_init = $this->form->bagian;
            $this->form->tgl_jabatan = null;
            $this->form->no_sk_jabatan = null;
            $this->form->document_id_jabatan = null;

            $this->dispatch('new-jabatan-created'); //dispatch event

            $this->toast()
                ->success('Berhasil', 'Jabatan baru berhasil disimpan.')
                ->send();
        } catch (Throwable $th) {
            \Illuminate\Support\Facades\DB::rollBack();
            $this->toast()
                ->error('Failed', 'Error : ' . $th->getMessage())
                ->send();
        }
    }

    public function updateRuangan()
    {
        try {
            $karyawan = $this->form->karyawan;
            $newRuanganId = $this->form->ruangan;
            $tglRuangan = $this->form->tgl_ruangan;

            $documentId = $this->form->document_id_ruangan;
            if (is_array($documentId)) {
                $documentId = $documentId['id'] ?? $documentId['value'] ?? (isset($documentId[0]) ? $documentId[0] : null);
            }
            $documentId = empty($documentId) ? null : (int) $documentId;

            // Update tgl_berakhir penugasan ruangan aktif terdahulu
            \App\Models\Sdm\KaryawanRuangan::where('karyawan_id', $karyawan->id)
                ->whereNull('tgl_berakhir')
                ->update(['tgl_berakhir' => $tglRuangan]);

            // Insert penugasan ruangan baru ke sdm_kary_ruangan
            \App\Models\Sdm\KaryawanRuangan::create([
                'karyawan_id' => $karyawan->id,
                'ruangan_id'  => $newRuanganId,
                'tgl_mulai'   => $tglRuangan,
                'tgl_berakhir'=> null,
                'no_sk'       => $this->form->no_sk_ruangan,
                'document_id' => $documentId,
                'is_utama'    => true,
                'keterangan'  => 'Rotasi / Perubahan Ruangan via Edit Kedinasan',
            ]);

            // Update ruangan_id pada sdm_karyawan
            $karyawan->update(['ruangan_id' => $newRuanganId]);

            $this->ruangan_init = $newRuanganId;
            $this->form->tgl_ruangan = null;
            $this->form->no_sk_ruangan = null;
            $this->form->document_id_ruangan = null;

            $this->dispatch('new-ruangan-created');

            $this->toast()
                ->success('Berhasil', 'Penugasan ruangan baru berhasil disimpan.')
                ->send();
        } catch (Throwable $th) {
            $this->toast()
                ->error('Gagal', 'Error : ' . $th->getMessage())
                ->send();
        }
    }


    public function render()
    {
        $karyawan = $this->karyawan ?? ($this->karyawanId ? Karyawan::find($this->karyawanId) : null);
        return view('livewire.karyawan.edit-kedinasan', [
            'karyawan' => $karyawan,
        ]);
    }
}
