<?php

namespace App\Livewire\Karyawan;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\Karyawan;
use App\Enums\StatusKaryawan;
use App\Enums\KategoriKerja;
use App\Livewire\Forms\KaryawanForm;
use App\Models\Sdm\KaryawanJabatan;
use Livewire\Attributes\Lazy;
use Illuminate\Validation\Rule;
use TallStackUi\Traits\Interactions;

#[Lazy]
class EditKedinasan extends Component
{
    use Interactions;

    public KaryawanForm $form;

    public $status_options;
    public $status_init;
    public $kategori_options;
    public $kategori_init;
    public $jabatan_options;
    public $bagian_options;
    public $jabatan_init;
    public $bagian_init;

    public $dinas_options = [
        ['id' => 'resign', 'label' => 'Resign / Mengundurkan Diri'],
        ['id' => 'dipecat', 'label' => 'Dipecat'],
        ['id' => 'end_kontrak', 'label' => 'Habis Kontrak'],
    ];
    public $dinas_init;
    public $ruangan_init;
    public $dinas;
    public $tgl_dinas;


    public function rules(): array
    {
        return [
            'form.status' => 'required',
            'form.kategori_kerja' => 'required',
            'form.jabatan' => 'required',
            'form.bagian' => 'required|exists:bagian,id',
            'form.tgl_status' => Rule::requiredIf(fn() => $this->form->status != $this->status_init),
            'form.tgl_jabatan' => Rule::requiredIf(fn() =>
                $this->form->jabatan != $this->jabatan_init || $this->form->bagian != $this->bagian_init
            ),
            'form.tgl_ruangan' => Rule::requiredIf(fn() => $this->form->ruangan != $this->ruangan_init),
            'form.tgl_dinas' => Rule::requiredIf(fn() => $this->form->dinas != $this->dinas_init)
        ];
    }

    public function mount($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $this->form->mount($karyawan); //new instance form

        $this->form->setKedinasan($karyawan);

        $this->status_options = StatusKaryawan::options();
        $this->status_init = $karyawan->status;

        $this->kategori_options = KategoriKerja::options();
        $this->kategori_init = $karyawan->kategori_kerja?->value ?? 'shift';

        $this->jabatan_options = Jabatan::all();
        $this->bagian_options = Bagian::query()->where('is_active', true)->orderBy('nama')->get();
        $this->jabatan_init = $this->form->jabatan;
        $this->bagian_init = $this->form->bagian;
        $this->ruangan_init = $karyawan->ruangan_id ?? '';

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

    public function updatedFormJabatan($value): void
    {
        $jabatan = Jabatan::find($value);
        $this->form->bagian = $jabatan?->bagian_id ?? '';
    }

    public function updatedFormRuangan($value)
    {
        if (is_array($value)) {
            $this->form->ruangan = $value['id'] ?? $value['value'] ?? (isset($value[0]) ? $value[0] : null);
        }
    }

    public function update()
    {
        $this->validate($this->rules());

        // update status or kategori kerja
        if (($this->form->status != $this->status_init) || ($this->form->kategori_kerja != $this->kategori_init)) {
            $this->updateStatus();
        }

        // update jabatan
        if ($this->form->tgl_jabatan && (
            $this->form->jabatan != $this->jabatan_init ||
            $this->form->bagian != $this->bagian_init
        )) {
            $this->updateJabatan();
        }

        // update ruangan
        if ($this->form->tgl_ruangan && ($this->form->ruangan != $this->ruangan_init)) {
            $this->updateRuangan();
        }

        // update ruangan, kategori kerja, dan pendidikan terakhir
        $data = [];
        
        $ruanganRaw = $this->form->ruangan;
        if (is_array($ruanganRaw)) {
            $ruanganRaw = $ruanganRaw['id'] ?? $ruanganRaw['value'] ?? (isset($ruanganRaw[0]) ? $ruanganRaw[0] : null);
        }
        $ruanganId = (empty($ruanganRaw) || $ruanganRaw == '') ? null : (int) $ruanganRaw;

        if ($ruanganId != $this->ruangan_init) {
            $data['ruangan_id'] = $ruanganId;
            $this->ruangan_init = $ruanganId;
            $this->form->ruangan = $ruanganId;
        }
        
        $data['kategori_kerja'] = $this->form->kategori_kerja;
        $data['pendidikan_setara'] = empty($this->form->pendidikan_setara) ? null : $this->form->pendidikan_setara;
        
        if (count($data) > 0) {
            $this->form->karyawan->update($data);
            if ($this->form->kategori_kerja === 'reguler' || $this->form->kategori_kerja === \App\Enums\KategoriKerja::REGULER) {
                \App\Models\Sdm\JadwalKerja::syncKaryawanRegulerSchedule($this->form->karyawan->id);
            }
            $this->dispatch('updated-karywan');
        }

        $this->toast()
            ->success('Sukses', 'Update data kedinasan berhasil.')
            ->send();
    }

    function updateStatus()
    {

        try {
            $data = [
                'status' => $this->form->status,
                'kategori_kerja' => $this->form->kategori_kerja,
            ];

            // update
            $this->form->karyawan->update($data);
            $this->status_init = $this->form->status;
            $this->kategori_init = $this->form->kategori_kerja;

            // update
            $this->form->karyawan->update($data);

            // event
            $this->dispatch('status-updated');

            // toast
            $this->toast()
                ->success('Sukses', 'Update data kedinasan berhasil.')
                ->send();
        } catch (Throwable $th) {
            $this->toast()
                ->error('Failed', 'Error : ', $th->getMessage())
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

            $data = [
                'jabatan_id'  => $this->form->jabatan,
                'karyawan_id' => $this->form->karyawan->id,
                'bagian_id'   => $this->form->bagian,
                'tgl_mulai'   => $this->form->tgl_jabatan
            ];

            // insert data new jabatan
            KaryawanJabatan::create($data);

            // Auto-sync role user jika terhubung dengan akun user
            $karyawan->user?->syncRoleFromJabatan();

            // Cek jika jabatan baru adalah level struktural (tingkat_id <= 3 / Kabag / Wadir / Direktur)
            // dan karyawan memiliki penugasan koordinator aktif
            $newJabatan = \App\Models\Sdm\Jabatan::find($this->form->jabatan);
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

            $this->dispatch('new-jabatan-created'); //dispatch event

            $this->toast()
                ->success('Berhasil', 'Jabatan baru berhasil disimpan.')
                ->send();
        } catch (Throwable $th) {
            \Illuminate\Support\Facades\DB::rollBack();
            $this->toast()
                ->error('Failed', 'Error : ', $th->getMessage())
                ->send();
        }
    }

    public function updateRuangan()
    {
        try {
            $karyawan = $this->form->karyawan;
            $newRuanganId = $this->form->ruangan;
            $tglRuangan = $this->form->tgl_ruangan;

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
                'is_utama'    => true,
                'keterangan'  => 'Rotasi / Perubahan Ruangan via Edit Kedinasan',
            ]);

            // Update ruangan_id pada sdm_karyawan
            $karyawan->update(['ruangan_id' => $newRuanganId]);

            $this->ruangan_init = $newRuanganId;
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
        return view('livewire.karyawan.edit-kedinasan');
    }
}
