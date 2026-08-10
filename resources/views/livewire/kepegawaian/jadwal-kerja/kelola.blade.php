<div class="flex flex-col gap-4">
    @php
        if (!function_exists('labelSingkat')) {
            function labelSingkat($kode, $nama) {
                $kode = strtoupper(trim($kode));
                if ($kode === 'REGULER') return 'REG';
                if (in_array($kode, ['PAGI', 'SIANG', 'MALAM'])) return $kode;

                $parts = explode(' ', trim($nama), 3);
                $waktu  = $parts[0] ?? '';
                $second = $parts[1] ?? '';

                $w = strtoupper(mb_substr($waktu, 0, 1));

                if (strtolower($second) === 'awal') {
                    $third = $parts[2] ?? '';
                    $room = strtoupper(mb_substr(explode(' ', $third)[0] ?? '', 0, 3));
                    return $w . 'A' . ($room ? '.' . $room : '');
                }
                if (!empty($second)) {
                    return $w . '.' . strtoupper(mb_substr($second, 0, 3));
                }
                return strtoupper(mb_substr($kode, 0, 5));
            }
        }

        if (!function_exists('autoWarna')) {
            function autoWarna($kode, $warna) {
                if ($warna && $warna !== '#e2e8f0') return $warna;
                $kode = strtoupper($kode);
                if ($kode === 'REGULER') return '#66BB6A';
                if (str_starts_with($kode, 'P'))  return '#42A5F5';
                if (str_starts_with($kode, 'S'))  return '#FFA726';
                if (str_starts_with($kode, 'M'))  return '#AB47BC';
                return '#78909C';
            }
        }

        if (!function_exists('teksCerahGelap')) {
            function teksCerahGelap($hex) {
                $hex = ltrim($hex, '#');
                if (strlen($hex) < 6) return '#1e293b';
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                $lum = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
                return $lum > 0.55 ? '#1e293b' : '#ffffff';
            }
        }

        // Pre-compute label & color maps for Alpine
        $labelMap = [''=>'LIBUR'];
        $colorMap = [''=>'#f1f5f9'];
        $tipMap   = [''=>'Libur'];
        foreach ($shiftOptions as $o) {
            $labelMap[$o['id']] = labelSingkat($o['kode'], $o['nama']);
            $colorMap[$o['id']] = autoWarna($o['kode'], $o['warna']);
            $tipMap[$o['id']]   = $o['nama'] . ' (' . substr($o['jam_masuk'],0,5) . '–' . substr($o['jam_keluar'],0,5) . ')';
        }
        $jsonColorMap = json_encode($colorMap);
        $jsonLabelMap = json_encode($labelMap);
        $jsonTipMap   = json_encode($tipMap);

        $user = auth()->user();
        $isKabidReviewer = $user?->can('approve-jadwal-kabid') || $user?->isKepalaDept();
        $isWadirReviewer = $user?->can('approve-jadwal-wadir') || $user?->isWadir();
    @endphp

    <style>
        .jadwal-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
        .jadwal-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .jadwal-scroll::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }
        .jadwal-scroll::-webkit-scrollbar-thumb:hover { background: #64748b; }

        .col-nama {
            position: sticky; left: 0; z-index: 10; background: #fff;
            min-width: 170px; max-width: 170px; width: 170px;
        }
        .col-kat {
            position: sticky; left: 170px; z-index: 10; background: #fff;
            min-width: 80px; max-width: 80px; width: 80px;
        }
        .col-kat::after {
            content: ''; position: absolute; right: -6px; top: 0; bottom: 0; width: 6px;
            background: linear-gradient(to right, rgba(0,0,0,.06), transparent); pointer-events: none;
        }
        .hdr-nama {
            position: sticky; top: 0; left: 0; z-index: 30; background: #f1f5f9;
            min-width: 170px; max-width: 170px; width: 170px;
        }
        .hdr-kat {
            position: sticky; top: 0; left: 170px; z-index: 30; background: #f1f5f9;
            min-width: 80px; max-width: 80px; width: 80px;
        }
        .hdr-kat::after {
            content: ''; position: absolute; right: -6px; top: 0; bottom: 0; width: 6px;
            background: linear-gradient(to right, rgba(0,0,0,.06), transparent); pointer-events: none;
        }
        .hdr-tgl { position: sticky; top: 0; z-index: 20; background: #f8fafc; }
        tbody tr:hover .col-nama, tbody tr:hover .col-kat { background: #f8fafc; }

        .col-tgl { min-width: 60px; max-width: 60px; width: 60px; }

        .cell-edit { position: relative; cursor: pointer; }
        .cell-edit select {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            cursor: pointer;
            color: transparent;
            background: transparent;
            border: none; outline: none;
            -webkit-appearance: none; -moz-appearance: none; appearance: none;
            font-size: 13px;
        }
        .cell-edit select:focus { outline: none; box-shadow: none; }
        .cell-edit select option {
            color: #1e293b;
            background: #fff;
            padding: 4px 8px;
            font-size: 13px;
        }
        .cell-label {
            display: flex; align-items: center; justify-content: center;
            width: 100%; height: 100%;
            font-size: 9px; font-weight: 700; letter-spacing: 0.02em;
            pointer-events: none;
        }
    </style>

    {{-- ── Header Bar ── --}}
    <div class="flex items-center justify-between rounded-xl bg-white p-5 shadow-sm border border-slate-100">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-bold text-slate-800">Jadwal Kerja: {{ $jadwalKerja->ruangan->nama ?? '-' }}</h2>
                @if($jadwalKerja->isDokterSchedule())
                    <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 border border-blue-200 shadow-xs">
                        👨‍⚕️ Jadwal Dokter
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 border border-emerald-200 shadow-xs">
                        👥 Jadwal Karyawan
                    </span>
                @endif
            </div>
            <div class="text-sm text-slate-500 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                <span>Periode: <strong class="text-slate-700">{{ date('F', mktime(0, 0, 0, $jadwalKerja->bulan, 1)) }} {{ $jadwalKerja->tahun }}</strong></span>
                <span class="text-slate-300">•</span>
                <span class="flex items-center gap-1.5">
                    Status:
                    <x-ts:badge :color="$jadwalKerja->status->color()" text="{{ $jadwalKerja->status->nama() }}" />
                </span>
                @if($jadwalKerja->diketahuiOleh)
                    <span class="text-slate-300">•</span>
                    <span class="text-xs text-slate-500">
                        Diketahui Kabid: <strong class="text-slate-700">{{ $jadwalKerja->diketahuiOleh->nama }}</strong>
                        @if($jadwalKerja->diketahui_at)
                            <span class="font-medium text-slate-500">({{ $jadwalKerja->diketahui_at->format('d/m/Y H:i') }})</span>
                        @endif
                    </span>
                @endif
                @if($jadwalKerja->disetujuiOleh)
                    <span class="text-slate-300">•</span>
                    <span class="text-xs text-slate-500">
                        Disetujui Wadir: <strong class="text-slate-700">{{ $jadwalKerja->disetujuiOleh->nama }}</strong>
                        @if($jadwalKerja->disetujui_at)
                            <span class="font-medium text-slate-500">({{ $jadwalKerja->disetujui_at->format('d/m/Y H:i') }})</span>
                        @endif
                    </span>
                @endif
            </div>
        </div>

        <div class="flex gap-2 flex-shrink-0">
            <x-ts:button outline href="{{ route('kepegawaian.jadwal-kerja.index') }}" icon="tabler.arrow-left">Kembali</x-ts:button>
            <x-ts:button outline color="secondary" x-on:click="$tsui.open.modal('modal-riwayat'); Livewire.dispatch('load-riwayat', {jadwalKerjaId: {{ $jadwalKerja->id }}})" icon="tabler.history">Riwayat</x-ts:button>
            <x-ts:button outline color="secondary" x-on:click="$tsui.open.modal('modal-log-approval'); Livewire.dispatch('load-log-approval', {jadwalKerjaId: {{ $jadwalKerja->id }}})" icon="tabler.certificate">Log Persetujuan</x-ts:button>

            @if(!$isReadOnly)
                <x-ts:button outline color="primary" wire:click="save" loading="save" icon="tabler.device-floppy">
                    {{ $jadwalKerja->status === \App\Enums\StatusJadwalKerja::PUBLISHED ? 'Simpan Perubahan' : 'Simpan Draf' }}
                </x-ts:button>
            @endif

            {{-- Action Buttons per Status --}}
            @if(in_array($jadwalKerja->status, [\App\Enums\StatusJadwalKerja::DRAFT, \App\Enums\StatusJadwalKerja::DITOLAK]))
                @if($jadwalKerja->isDokterSchedule())
                    <x-ts:button color="info" wire:click="ajukanKeWadirLangsung" loading="ajukanKeWadirLangsung" icon="tabler.send">Ajukan ke Wadir</x-ts:button>
                @else
                    <x-ts:button color="info" wire:click="ajukanKeKabid" loading="ajukanKeKabid" icon="tabler.send">Ajukan ke Kabid</x-ts:button>
                @endif
            @elseif($jadwalKerja->status === \App\Enums\StatusJadwalKerja::MENUNGGU_KABID && $isKabidReviewer)
                <x-ts:button color="rose" outline wire:click="openRevisiModal" icon="tabler.arrow-back-up">Kembalikan (Revisi)</x-ts:button>
                <x-ts:button color="amber" wire:click="konfirmasiKabid" loading="konfirmasiKabid" icon="tabler.check">Konfirmasi (Diketahui Kabid)</x-ts:button>
            @elseif($jadwalKerja->status === \App\Enums\StatusJadwalKerja::MENUNGGU_WADIR && $isWadirReviewer)
                <x-ts:button color="rose" outline wire:click="openRevisiModal" icon="tabler.arrow-back-up">Kembalikan (Revisi)</x-ts:button>
                <x-ts:button color="emerald" wire:click="setujuiWadir" loading="setujuiWadir" icon="tabler.checks">Setujui & Dipublikasikan</x-ts:button>
            @endif
        </div>
    </div>

    {{-- ── Progress Stepper Alur Approval ── --}}
    <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-100">
        <div class="flex items-center justify-between">
            @php
                $isDokter = $jadwalKerja->isDokterSchedule();
                $targetKabidName = $jadwalKerja->diketahuiOleh?->full_nama ?? $jadwalKerja->getTargetApproverName(1);
                $targetWadirName = $jadwalKerja->disetujuiOleh?->full_nama ?? $jadwalKerja->getTargetApproverName(2);

                if ($isDokter) {
                    $currentStep = match ($jadwalKerja->status) {
                        \App\Enums\StatusJadwalKerja::DRAFT, \App\Enums\StatusJadwalKerja::DITOLAK => 1,
                        \App\Enums\StatusJadwalKerja::MENUNGGU_WADIR, \App\Enums\StatusJadwalKerja::MENUNGGU_KABID => 2,
                        \App\Enums\StatusJadwalKerja::PUBLISHED => 3,
                        default => 1,
                    };
                    $steps = [
                        1 => ['label' => 'Draf (Koor Dokter)', 'sub' => 'Penyusunan Jadwal Dokter'],
                        2 => ['label' => 'Disetujui Wadir', 'sub' => $jadwalKerja->disetujui_at ? $jadwalKerja->disetujui_at->format('d/m/Y H:i') : $targetWadirName],
                        3 => ['label' => 'Dipublikasikan', 'sub' => $jadwalKerja->published_at ? $jadwalKerja->published_at->format('d/m/Y H:i') : 'Berlaku bagi Dokter'],
                    ];
                } else {
                    $currentStep = $jadwalKerja->status->stepIndex();
                    $steps = [
                        1 => ['label' => 'Draf (Karu)', 'sub' => 'Penyusunan Jadwal'],
                        2 => ['label' => 'Diketahui Kepala Dept', 'sub' => $jadwalKerja->diketahui_at ? $jadwalKerja->diketahui_at->format('d/m/Y H:i') : $targetKabidName],
                        3 => ['label' => 'Disetujui Wadir', 'sub' => $jadwalKerja->disetujui_at ? $jadwalKerja->disetujui_at->format('d/m/Y H:i') : $targetWadirName],
                        4 => ['label' => 'Dipublikasikan', 'sub' => $jadwalKerja->published_at ? $jadwalKerja->published_at->format('d/m/Y H:i') : 'Berlaku bagi Karyawan'],
                    ];
                }
            @endphp
            @foreach($steps as $stepNo => $step)
                <div class="flex items-center flex-1 {{ $stepNo < count($steps) ? '' : 'flex-initial' }}">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors
                            {{ $currentStep > $stepNo ? 'bg-emerald-500 text-white' : ($currentStep === $stepNo ? ($jadwalKerja->status === \App\Enums\StatusJadwalKerja::DITOLAK ? 'bg-rose-600 text-white ring-4 ring-rose-100' : 'bg-indigo-600 text-white ring-4 ring-indigo-100') : 'bg-slate-100 text-slate-400') }}">
                            @if($currentStep > $stepNo)
                                <x-ts:icon name="tabler.check" class="w-4 h-4" />
                            @else
                                {{ $stepNo }}
                            @endif
                        </div>
                        <div>
                            <div class="text-xs font-bold {{ $currentStep >= $stepNo ? 'text-slate-800' : 'text-slate-400' }}">{{ $step['label'] }}</div>
                            <div class="text-[10px] text-slate-400">{{ $step['sub'] }}</div>
                        </div>
                    </div>
                    @if($stepNo < count($steps))
                        <div class="flex-1 mx-4 h-1 rounded transition-colors {{ $currentStep > $stepNo ? 'bg-emerald-500' : 'bg-slate-100' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Catatan Revisi Alert --}}
    @if($jadwalKerja->status === \App\Enums\StatusJadwalKerja::DITOLAK && $jadwalKerja->catatan_revisi)
        <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 flex items-start gap-3">
            <x-ts:icon name="tabler.alert-triangle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
            <div>
                <h4 class="text-xs font-bold text-rose-800">Catatan Revisi:</h4>
                <p class="text-xs text-rose-700 mt-0.5 font-medium">{{ $jadwalKerja->catatan_revisi }}</p>
                <p class="text-[10px] text-rose-500 mt-1">Silakan perbaiki shift di bawah ini lalu klik tombol "{{ $jadwalKerja->isDokterSchedule() ? 'Ajukan ke Wadir' : 'Ajukan ke Kabid' }}" setelah selesai.</p>
            </div>
        </div>
    @endif

    {{-- ── Legend ── --}}
    <div class="rounded-xl bg-white px-4 py-2.5 shadow-sm border border-slate-100 flex flex-wrap items-center gap-x-2 gap-y-1">
        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mr-1">Keterangan:</span>
        <span class="inline-flex items-center gap-1 text-[10px] text-slate-500 bg-slate-50 px-1.5 py-0.5 rounded">
            <span class="w-2.5 h-2.5 rounded-sm bg-slate-200 border border-slate-300"></span>
            <span class="font-bold">LIBUR</span> <span class="text-slate-400">Hari Libur</span>
        </span>
        <span class="inline-flex items-center gap-1 text-[10px] text-slate-500 bg-slate-50 px-1.5 py-0.5 rounded">
            <span class="w-2.5 h-2.5 rounded-sm bg-rose-100 border border-rose-200"></span>
            <span class="font-bold text-rose-700">CUTI</span> <span class="text-slate-400">Cuti / Izin Resmi</span>
        </span>
        @foreach($shiftOptions as $s)
            @php $sw = autoWarna($s['kode'], $s['warna']); @endphp
            <span class="inline-flex items-center gap-1 text-[10px] bg-slate-50 px-1.5 py-0.5 rounded" title="{{ $s['nama'] }} ({{ substr($s['jam_masuk'],0,5) }}–{{ substr($s['jam_keluar'],0,5) }})">
                <span class="w-2.5 h-2.5 rounded-sm border border-black/10" style="background:{{ $sw }}"></span>
                <span class="font-bold text-slate-700">{{ labelSingkat($s['kode'], $s['nama']) }}</span>
                <span class="text-slate-400">{{ $s['nama'] }}</span>
            </span>
        @endforeach
    </div>

    {{-- Modal Riwayat --}}
    <x-ts:modal id="modal-riwayat" title="Riwayat Perubahan Jadwal" size="4xl">
        <livewire:kepegawaian.jadwal-kerja.riwayat :jadwal-kerja-id="$jadwalKerja->id" />
    </x-ts:modal>

    {{-- Modal Log Persetujuan --}}
    <x-ts:modal id="modal-log-approval" title="Log & Histori Persetujuan Jadwal" size="3xl">
        <livewire:kepegawaian.jadwal-kerja.log-approval :jadwal-kerja-id="$jadwalKerja->id" />
    </x-ts:modal>

    {{-- Modal Catatan Revisi --}}
    @if($showRevisiModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-lg w-full p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <x-ts:icon name="tabler.arrow-back-up" class="w-5 h-5 text-rose-500" />
                        Kembalikan Jadwal (Catatan Revisi)
                    </h3>
                    <button wire:click="$set('showRevisiModal', false)" class="text-slate-400 hover:text-slate-600">
                        <x-ts:icon name="tabler.x" class="w-5 h-5" />
                    </button>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan Revisi <span class="text-rose-500">*</span></label>
                    <textarea wire:model.defer="catatanRevisiInput" rows="4" class="w-full text-xs rounded-lg border-slate-300 focus:border-rose-500 focus:ring-rose-500" placeholder="Tuliskan catatan perbaikan jadwal yang harus direvisi..."></textarea>
                    @error('catatanRevisiInput') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <x-ts:button outline color="secondary" wire:click="$set('showRevisiModal', false)">Batal</x-ts:button>
                    <x-ts:button color="rose" wire:click="confirmKembalikanDraft" icon="tabler.arrow-back-up">Kembalikan ke Draf</x-ts:button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Table ── --}}
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-auto max-h-[72vh] jadwal-scroll">
        <form wire:submit.prevent="save">
            <table class="border-separate border-spacing-0 text-xs table-fixed" style="width:{{ 250 + count($dates) * 60 }}px">
                <colgroup>
                    <col style="width:170px">
                    <col style="width:80px">
                    @foreach($dates as $d)<col style="width:60px">@endforeach
                </colgroup>

                <thead>
                    <tr>
                        <th class="hdr-nama border-b-2 border-r border-slate-200 px-3 py-2 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Karyawan</th>
                        <th class="hdr-kat border-b-2 border-slate-200 px-1 py-2 text-center text-[10px] font-bold text-slate-500 uppercase tracking-wider">Kategori</th>
                        @foreach($dates as $date)
                            <th class="hdr-tgl col-tgl border-b-2 border-r border-slate-100 py-1.5 text-center {{ $date->isWeekend() ? 'text-rose-500 !bg-rose-50' : 'text-slate-500' }}">
                                <div class="text-[7px] font-semibold uppercase leading-none opacity-60">{{ $date->isoFormat('ddd') }}</div>
                                <div class="text-[11px] font-bold leading-tight mt-0.5">{{ $date->format('d') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach($karyawans as $karyawan)
                        <tr class="group transition-colors hover:bg-slate-50/40">
                            <td class="col-nama border-b border-r border-slate-100 px-3 py-1.5 font-semibold text-slate-700 text-[11px] truncate whitespace-nowrap" title="{{ $karyawan['nama'] }}">
                                {{ $karyawan['nama'] }}
                            </td>
                            <td class="col-kat border-b border-slate-100 px-1 py-1.5 text-center truncate" title="{{ $karyawan['kategori'] }}">
                                <span class="inline-block px-1 py-0.5 rounded bg-slate-100 text-slate-500 text-[8px] font-semibold truncate max-w-full">{{ $karyawan['kategori'] }}</span>
                            </td>

                            @for($d = 1; $d <= count($dates); $d++)
                                @php
                                    $detail = $karyawan['details'][$d] ?? null;
                                    $dateStr = $dates[$d-1]->format('Y-m-d');
                                    $cutiNo = $cutiDates["{$karyawan['id']}-{$dateStr}"] ?? null;
                                    if ($detail && $detail->shift) {
                                        $cW = autoWarna($detail->shift->kode, $detail->shift->warna);
                                        $cT = teksCerahGelap($cW);
                                        $cL = labelSingkat($detail->shift->kode, $detail->shift->nama);
                                        $cTip = $detail->shift->nama . ' (' . substr($detail->shift->jam_masuk ?? '', 0, 5) . '–' . substr($detail->shift->jam_keluar ?? '', 0, 5) . ')';
                                    } else {
                                        $cW = '#f1f5f9'; $cT = '#94a3b8'; $cL = 'LIBUR'; $cTip = 'Libur';
                                    }
                                @endphp
                                <td class="col-tgl border-b border-r border-slate-50 p-0.5 text-center align-middle {{ $dates[$d-1]->isWeekend() ? 'bg-rose-50/20' : '' }}">
                                    @if(!$detail)
                                        <div class="h-6 rounded flex items-center justify-center text-[8px] font-medium text-slate-300 bg-slate-50 border border-dashed border-slate-200">—</div>
                                    @elseif($cutiNo)
                                        <div class="h-6 rounded flex items-center justify-center text-[9px] font-bold select-none border border-rose-200 bg-rose-100 text-rose-700" title="Cuti/Izin Resmi ({{ $cutiNo }})">
                                            CUTI
                                        </div>
                                    @elseif($isReadOnly)
                                        <div class="h-6 rounded flex items-center justify-center text-[9px] font-bold select-none border border-black/5"
                                             style="background:{{ $cW }};color:{{ $cT }}" title="{{ $cTip }}">
                                            {{ $cL }}
                                        </div>
                                    @else
                                        {{-- Editable: select invisible, label overlay visible --}}
                                        <div x-data="{
                                                v: '{{ $detail->shift_id }}',
                                                c: {{ $jsonColorMap }},
                                                l: {{ $jsonLabelMap }},
                                                t: {{ $jsonTipMap }},
                                                lum(hex) {
                                                    hex = hex.replace('#','');
                                                    let r = parseInt(hex.substr(0,2),16),
                                                        g = parseInt(hex.substr(2,2),16),
                                                        b = parseInt(hex.substr(4,2),16);
                                                    return (0.299*r+0.587*g+0.114*b)/255;
                                                }
                                             }"
                                             :style="'background:'+(c[v]||'#f1f5f9')+';color:'+(v?(lum(c[v]||'#f1f5f9')>0.55?'#1e293b':'#fff'):'#94a3b8')"
                                             :title="t[v]||'Libur'"
                                             class="cell-edit h-6 rounded border border-black/5 transition-colors">
                                            {{-- Label overlay --}}
                                            <span class="cell-label" x-text="l[v]||'LIBUR'"></span>
                                            {{-- Hidden select --}}
                                            <select wire:model.defer="state.{{ $detail->id }}" x-model="v">
                                                <option value="">LIBUR</option>
                                                @foreach($shiftOptions as $s)
                                                    <option value="{{ $s['id'] }}">{{ $s['nama'] }} ({{ substr($s['jam_masuk'],0,5) }}–{{ substr($s['jam_keluar'],0,5) }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
    </div>
</div>
