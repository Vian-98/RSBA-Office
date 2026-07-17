<div class="flex flex-col gap-4">
    @php
        // ── Label Singkat: menghasilkan label pendek untuk cell dari kode+nama ──
        function labelSingkat($kode, $nama) {
            $kode = strtoupper(trim($kode));
            if ($kode === 'REGULER') return 'REG';
            if (in_array($kode, ['PAGI', 'SIANG', 'MALAM'])) return $kode;

            // Room-specific: ambil singkatan dari nama
            // Format nama: "Pagi IGD", "Siang Laboratorium", "Malam Ruang Rawat Inap Melati"
            $parts = explode(' ', trim($nama), 3);
            $waktu  = $parts[0] ?? '';  // Pagi/Siang/Malam
            $second = $parts[1] ?? '';

            $w = strtoupper(mb_substr($waktu, 0, 1)); // P, S, M

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

        // ── Auto-assign warna untuk shift yang null ──
        function autoWarna($kode, $warna) {
            if ($warna && $warna !== '#e2e8f0') return $warna;
            $kode = strtoupper($kode);
            if ($kode === 'REGULER') return '#66BB6A';
            if (str_starts_with($kode, 'P'))  return '#42A5F5';
            if (str_starts_with($kode, 'S'))  return '#FFA726';
            if (str_starts_with($kode, 'M'))  return '#AB47BC';
            return '#78909C';
        }

        // ── Kontras teks otomatis (luminance) ──
        function teksCerahGelap($hex) {
            $hex = ltrim($hex, '#');
            if (strlen($hex) < 6) return '#1e293b';
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $lum = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
            return $lum > 0.55 ? '#1e293b' : '#ffffff';
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

        /* Editable cell: select text is invisible (overlay shows label), but dropdown renders normally */
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
        /* Dropdown popup: restore visible text & styling */
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
            pointer-events: none; /* clicks pass through to select */
        }
    </style>

    {{-- ── Header Bar ── --}}
    <div class="flex items-center justify-between rounded-xl bg-white p-5 shadow-sm border border-slate-100">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Jadwal Kerja: {{ $jadwalKerja->ruangan->nama ?? '-' }}</h2>
            <p class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                Periode: <strong class="ml-1">{{ date('F', mktime(0, 0, 0, $jadwalKerja->bulan, 1)) }} {{ $jadwalKerja->tahun }}</strong>
                <span class="text-slate-300">•</span> Status:
                <x-ts:badge :color="$jadwalKerja->status->color()" text="{{ $jadwalKerja->status->nama() }}" />
            </p>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <x-ts:button outline href="{{ route('kepegawaian.jadwal-kerja.index') }}" icon="tabler.arrow-left">Kembali</x-ts:button>
            <x-ts:button outline color="secondary" x-on:click="$dispatch('open-modal', {id:'modal-riwayat'}); $dispatch('load-riwayat', {jadwalKerjaId: {{ $jadwalKerja->id }}})" icon="tabler.history">Riwayat</x-ts:button>
            @if(!$isReadOnly)
                <x-ts:button outline color="primary" wire:click="save" loading="save" icon="tabler.device-floppy">Simpan Draf</x-ts:button>
                @if($jadwalKerja->status === \App\Enums\StatusJadwalKerja::DRAFT)
                    <x-ts:button color="success" wire:click="publish" icon="tabler.send">Publikasikan</x-ts:button>
                @endif
            @endif
        </div>
    </div>

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
    <x-filament::modal id="modal-riwayat" width="4xl" :autofocus="false">
        <x-slot name="heading">Riwayat Perubahan Jadwal</x-slot>
        <livewire:Kepegawaian.JadwalKerja.Riwayat />
    </x-filament::modal>

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
                                                c: @js($colorMap),
                                                l: @js($labelMap),
                                                t: @js($tipMap),
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
