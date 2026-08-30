<?php

namespace App\Livewire\Surat\Disposisi;

use App\Services\DocstoreSyncService;
use App\Services\SuratDisposisiService;
use Livewire\Component;
use Livewire\Attributes\Title;
use TallStackUi\Traits\Interactions;

#[Title('Buat Surat Disposisi Direktur')]
class Add extends Component
{
    use Interactions;

    public string $noAgenda = '';
    public ?string $suratMasukId = null;
    public string $tglSurat = '';
    public string $noSurat = '';
    public string $perihal = '';
    public string $asalSurat = '';
    public string $catatan = '';
    
    public string $diterimaOleh = '';
    public string $tglDiterima = '';
    public string $jamDiterima = '';

    // Recipients matrix (Kepada YTH)
    public array $recipients = [];

    // Master list of karyawan for 3 custom rows dropdown
    public array $allKaryawanList = [];

    // Mention Surat Modal state
    public bool $modalMention = false;
    public string $searchArsip = '';
    public array $arsipResults = [];
    public bool $loadingArsip = false;

    // Pattern Modal state
    public bool $modalPattern = false;
    public string $patternInput = '';

    public function mount(SuratDisposisiService $service): void
    {
        $this->noAgenda = $service->getNextNoAgenda();
        $this->patternInput = $service->getNoAgendaPattern();
        $this->tglSurat = date('Y-m-d');
        $this->tglDiterima = date('Y-m-d');
        $this->jamDiterima = date('H:i');

        // Load all karyawan list for custom dropdown
        $this->allKaryawanList = \App\Models\Sdm\Karyawan::select('id', 'nama', 'gelar_depan', 'gelar_belakang')
            ->orderBy('nama', 'asc')
            ->get()
            ->map(function ($k) {
                $namaFull = trim(($k->gelar_depan ? $k->gelar_depan . ' ' : '') . $k->nama . ($k->gelar_belakang ? ', ' . $k->gelar_belakang : ''));
                return [
                    'id' => $k->id,
                    'nama' => $namaFull,
                ];
            })
            ->toArray();

        // Load master jabatan list
        $masterList = $service->getMasterJabatanList();
        $this->recipients = array_map(function ($item) {
            return array_merge($item, [
                'is_info' => false,
                'is_action' => false,
                'is_arsip' => false,
            ]);
        }, $masterList);
    }

    public function addCustomRecipient(): void
    {
        $this->recipients[] = [
            'jabatan_id' => null,
            'nama_jabatan' => '',
            'karyawan_id' => null,
            'karyawan_nama' => null,
            'user_id' => null,
            'is_custom' => true,
            'is_info' => false,
            'is_action' => false,
            'is_arsip' => false,
        ];
    }

    public function removeCustomRecipient(int $index): void
    {
        if (isset($this->recipients[$index]) && !empty($this->recipients[$index]['is_custom'])) {
            array_splice($this->recipients, $index, 1);
        }
    }

    public function updatedRecipients($value, $key): void
    {
        if (str_contains($key, '.karyawan_id')) {
            $parts = explode('.', $key);
            $idx = (int) $parts[0];
            $karyawanId = (int) $value;

            if ($karyawanId) {
                $karyawan = \App\Models\Sdm\Karyawan::find($karyawanId);
                if ($karyawan) {
                    $namaFull = trim(($karyawan->gelar_depan ? $karyawan->gelar_depan . ' ' : '') . $karyawan->nama . ($karyawan->gelar_belakang ? ', ' . $karyawan->gelar_belakang : ''));
                    $user = \App\Models\User::where('karyawan_id', $karyawanId)->first();

                    $this->recipients[$idx]['karyawan_id'] = $karyawan->id;
                    $this->recipients[$idx]['karyawan_nama'] = $namaFull;
                    $this->recipients[$idx]['nama_jabatan'] = $namaFull;
                    $this->recipients[$idx]['user_id'] = $user?->id;
                }
            }
        }
    }

    public function openModalPattern(): void
    {
        $service = app(SuratDisposisiService::class);
        $this->patternInput = $service->getNoAgendaPattern();
        $this->modalPattern = true;
    }

    public function savePattern(SuratDisposisiService $service): void
    {
        $this->validate([
            'patternInput' => 'required|string|max:100',
        ]);

        $service->saveNoAgendaPattern($this->patternInput);
        $this->noAgenda = $service->getNextNoAgenda();
        $this->modalPattern = false;
        $this->toast()->success('Berhasil', 'Format No. Agenda berhasil diperbarui.')->send();
    }

    public function openModalMention(): void
    {
        $this->searchArsip = '';
        $this->arsipResults = [];
        $this->modalMention = true;
        $this->searchArsipLetters();
    }

    public function searchArsipLetters(): void
    {
        $this->loadingArsip = true;
        $items = [];

        try {
            $syncService = app(DocstoreSyncService::class);
            $result = $syncService->listFromDocstore(
                type: 'all',
                status: 'all',
                page: 1,
                perPage: 15,
                search: $this->searchArsip ?: null
            );

            $rawList = $result['data'] ?? $result['documents'] ?? (is_array($result) ? $result : []);
            if (is_array($rawList)) {
                foreach ($rawList as $row) {
                    $content = $row['content'] ?? [];
                    $noSurat = $row['document_number'] ?? $row['no_surat'] ?? $row['nomor_surat'] ?? $row['no'] ?? $content['document_number'] ?? '-';
                    $perihal = $row['perihal'] ?? $row['title'] ?? $content['title'] ?? $content['keterangan'] ?? $row['keterangan'] ?? '-';
                    $asalSurat = $row['asal_surat'] ?? $row['pengirim'] ?? $row['instansi_asal'] ?? $content['pengirim'] ?? 'RS Bintang Amin';
                    $tglSurat = $row['created_at'] ?? $row['tanggal_surat'] ?? $row['tgl_surat'] ?? date('Y-m-d');

                    if ($noSurat !== '-' || $perihal !== '-') {
                        $items[] = [
                            'docstore_key' => $row['docstore_key'] ?? $row['id'] ?? null,
                            'no_surat' => $noSurat,
                            'perihal' => $perihal,
                            'asal_surat' => $asalSurat,
                            'tgl_surat' => $tglSurat,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Log fallback
        }

        // Fallback & enrichment with local surat modules if docstore returns empty or few items
        if (count($items) < 10) {
            $searchKeyword = '%' . $this->searchArsip . '%';

            // Surat Perintah Tugas
            $spt = \App\Models\Surat\SuratPerintahTugas::query()
                ->when($this->searchArsip, fn($q) => $q->where('no', 'like', $searchKeyword)->orWhere('perihal', 'like', $searchKeyword))
                ->latest()->limit(5)->get();
            foreach ($spt as $s) {
                $items[] = [
                    'docstore_key' => 'SPT-' . $s->id,
                    'no_surat' => $s->no ?: ('SPT/' . $s->id),
                    'perihal' => $s->perihal ?: 'Surat Perintah Tugas',
                    'asal_surat' => 'RS Bintang Amin',
                    'tgl_surat' => $s->tgl ? $s->tgl->format('Y-m-d') : date('Y-m-d'),
                ];
            }

            // Surat Balasan PKL
            $pkl = \App\Models\Surat\SuratBalasanPkl::query()
                ->when($this->searchArsip, fn($q) => $q->where('no', 'like', $searchKeyword)->orWhere('tujuan_universitas', 'like', $searchKeyword))
                ->latest()->limit(5)->get();
            foreach ($pkl as $p) {
                $items[] = [
                    'docstore_key' => 'PKL-' . $p->id,
                    'no_surat' => $p->no ?: ('PKL/' . $p->id),
                    'perihal' => 'Balasan Praktik / PKL ' . $p->tujuan_universitas,
                    'asal_surat' => $p->tujuan_universitas ?: 'Universitas',
                    'tgl_surat' => $p->tgl ? $p->tgl->format('Y-m-d') : date('Y-m-d'),
                ];
            }

            // Surat Balasan Penelitian
            $penelitian = \App\Models\Surat\SuratBalasanPenelitian::query()
                ->when($this->searchArsip, fn($q) => $q->where('no', 'like', $searchKeyword)->orWhere('jenis_penelitian', 'like', $searchKeyword))
                ->latest()->limit(5)->get();
            foreach ($penelitian as $pn) {
                $items[] = [
                    'docstore_key' => 'PEN-' . $pn->id,
                    'no_surat' => $pn->no ?: ('PEN/' . $pn->id),
                    'perihal' => 'Izin Penelitian: ' . $pn->jenis_penelitian,
                    'asal_surat' => $pn->universitas ?: 'Institusi Penelitian',
                    'tgl_surat' => $pn->tgl ? $pn->tgl->format('Y-m-d') : date('Y-m-d'),
                ];
            }

            // Surat SP3
            $sp3 = \App\Models\Surat\SuratSp3::query()
                ->when($this->searchArsip, fn($q) => $q->where('no', 'like', $searchKeyword)->orWhere('rekanan', 'like', $searchKeyword))
                ->latest()->limit(5)->get();
            foreach ($sp3 as $sp) {
                $items[] = [
                    'docstore_key' => 'SP3-' . $sp->id,
                    'no_surat' => $sp->no ?: ('SP3/' . $sp->id),
                    'perihal' => 'Surat SP3 Pembayaran: ' . $sp->rekanan,
                    'asal_surat' => $sp->rekanan ?: 'Rekanan',
                    'tgl_surat' => $sp->tgl ? $sp->tgl->format('Y-m-d') : date('Y-m-d'),
                ];
            }
        }

        $this->arsipResults = $items;
        $this->loadingArsip = false;
    }

    public function selectArsipSurat(array $arsip): void
    {
        $this->suratMasukId = (string) ($arsip['docstore_key'] ?? '');
        $this->noSurat = $arsip['no_surat'] ?? '';
        $this->tglSurat = isset($arsip['tgl_surat']) ? date('Y-m-d', strtotime($arsip['tgl_surat'])) : date('Y-m-d');
        $this->perihal = $arsip['perihal'] ?? '';
        $this->asalSurat = $arsip['asal_surat'] ?? '';

        $this->modalMention = false;
        $this->toast()->info('Mention Surat', 'Data surat terarsip berhasil di-autofill.')->send();
    }

    public function clearMention(): void
    {
        $this->suratMasukId = null;
        $this->toast()->info('Info', 'Mention surat dilepas. Field dapat diisi manual.')->send();
    }

    public function submit(SuratDisposisiService $service)
    {
        $this->validate([
            'tglSurat' => 'required|date',
            'noSurat' => 'required|string|max:150',
            'perihal' => 'required|string|max:255',
            'asalSurat' => 'required|string|max:255',
            'catatan' => 'nullable|string',
        ], [
            'tglSurat.required' => 'Tanggal surat wajib diisi.',
            'noSurat.required' => 'Nomor surat wajib diisi.',
            'perihal.required' => 'Perihal surat wajib diisi.',
            'asalSurat.required' => 'Asal surat wajib diisi.',
        ]);

        // Validate at least one recipient checked
        $hasRecipient = false;
        foreach ($this->recipients as $r) {
            if (!empty($r['is_info']) || !empty($r['is_action']) || !empty($r['is_arsip'])) {
                $hasRecipient = true;
                break;
            }
        }

        if (!$hasRecipient) {
            $this->toast()->error('Validasi Gagal', 'Pilih minimal satu penerima pada tabel Kepada YTH beserta jenis RTL (Info/Action/Arsip).')->send();
            return;
        }

        try {
            $creatorUserId = auth()->id() ?? 1;
            $disposisi = $service->createDisposisi([
                'surat_masuk_id' => $this->suratMasukId,
                'tgl_surat' => $this->tglSurat,
                'no_surat' => $this->noSurat,
                'perihal' => $this->perihal,
                'asal_surat' => $this->asalSurat,
                'catatan' => $this->catatan,
                'diterima_oleh' => $this->diterimaOleh,
                'tgl_diterima' => $this->tglDiterima,
                'jam_diterima' => $this->jamDiterima,
            ], $this->recipients, $creatorUserId);

            $this->toast()->success('Berhasil', 'Surat Disposisi berhasil dibuat dan di-assign ke penerima.')->send();
            return redirect()->route('kepegawaian.surat.disposisi.show', $disposisi->id);
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal', 'Terjadi kesalahan: ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.surat.disposisi.add');
    }
}
