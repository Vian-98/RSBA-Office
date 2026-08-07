<?php

namespace App\Livewire\Master\Jabatan;

use App\Models\Sdm\Jabatan;
use Livewire\Component;

class OrgChart extends Component
{
    public array $chartWarnings = [];

    public function getChartDataProperty()
    {
        $jabatans = Jabatan::with([
            'bagian',
            'tingkat',
            // Hanya penugasan jabatan yang masih aktif.
            'jabatans' => fn ($q) => $q
                ->whereNull('tgl_berakhir')
                ->orderByDesc('tgl_mulai')
                ->orderByDesc('id'),
            // Karyawan aktif ditentukan dari resign_at, bukan kolom keterangan resign.
            'jabatans.karyawan' => fn ($q) => $q->whereNull('resign_at')
        ])->get();

        $nodes = [];
        $this->chartWarnings = [];

        $jabatanIds = $jabatans->pluck('id')->map(fn ($id) => (string) $id)->flip();

        foreach ($jabatans as $j) {
            $karyawan = $j->jabatans->first()?->karyawan;

            // Root ditentukan oleh data parent_id. Jangan mengikat root ke primary key tertentu.
            $parentId = $j->parent_id ? (string) $j->parent_id : null;
            if ($parentId === (string) $j->id) {
                $parentId = null;
                $this->chartWarnings[] = "Jabatan {$j->nama} memiliki parent_id ke dirinya sendiri dan diperlakukan sebagai root.";
            }

            if ($parentId !== null && !$jabatanIds->has($parentId)) {
                $this->chartWarnings[] = "Parent jabatan untuk {$j->nama} tidak ditemukan dan diperlakukan sebagai root.";
                $parentId = null;
            }

            $nodes[] = [
                'id'         => (string) $j->id,
                'parentId'   => $parentId,
                'name'       => $karyawan?->nama ?? 'Vacant / Belum Diisi',
                'position'   => $j->nama ?? '—',
                'department' => $j->bagian?->nama ?? 'RSBA',
                'nip'        => $karyawan?->nip ?? '—',
                'status'     => $karyawan ? ($karyawan->status?->nama() ?? 'Aktif') : 'Kosong',
                'tingkat'    => $j->tingkat?->nama ?? ('Level ' . ($j->tingkat?->urutan ?? 99)),
                'avatar'     => $karyawan?->foto 
                    ? asset('storage/' . $karyawan->foto)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($karyawan?->nama ?? $j->nama) . '&background=6366f1&color=ffffff&bold=true',
            ];
        }

        // Putuskan siklus parent agar d3.stratify() tidak gagal total.
        $parentById = collect($nodes)->mapWithKeys(fn ($node) => [$node['id'] => $node['parentId']]);
        foreach ($nodes as $index => $node) {
            $visited = [];
            $currentId = $node['id'];

            while ($currentId !== null && $parentById->has($currentId)) {
                if (isset($visited[$currentId])) {
                    $nodes[$index]['parentId'] = null;
                    $parentById->put($node['id'], null);
                    $this->chartWarnings[] = "Siklus parent ditemukan pada jabatan {$node['position']} dan diputus sementara.";
                    break;
                }

                $visited[$currentId] = true;
                $currentId = $parentById->get($currentId);
            }
        }

        $rootIndexes = collect($nodes)
            ->filter(fn ($node) => $node['parentId'] === null)
            ->keys()
            ->values();

        // d3-org-chart mensyaratkan tepat satu root. Jika data lama memiliki
        // lebih dari satu root atau belum memiliki root, gunakan root virtual
        // agar chart tetap dapat dibaca tanpa mengubah data jabatan diam-diam.
        if ($rootIndexes->count() !== 1) {
            $this->chartWarnings[] = $rootIndexes->isEmpty()
                ? 'Struktur jabatan tidak memiliki root. Root virtual digunakan sementara.'
                : 'Struktur jabatan memiliki lebih dari satu root. Root virtual digunakan sementara.';

            $virtualRootId = '__rsba_org_root__';
            foreach ($nodes as $index => $node) {
                if ($node['parentId'] === null) {
                    $nodes[$index]['parentId'] = $virtualRootId;
                }
            }

            array_unshift($nodes, [
                'id' => $virtualRootId,
                'parentId' => null,
                'name' => 'Struktur Organisasi RSBA',
                'position' => 'Root Struktur',
                'department' => 'RSBA',
                'nip' => '—',
                'status' => 'Perlu Verifikasi',
                'tingkat' => 'Root Virtual',
                'avatar' => asset('logo-fallback.png'),
            ]);
        }

        $this->chartWarnings = array_values(array_unique($this->chartWarnings));

        return $nodes;
    }

    public function render()
    {
        return view('livewire.master.jabatan.org-chart', [
            'chartData' => $this->chartData,
            'chartWarnings' => $this->chartWarnings,
        ]);
    }
}
