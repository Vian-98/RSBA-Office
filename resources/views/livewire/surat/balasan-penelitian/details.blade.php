<div class="flex flex-col gap-4">
    @if ($suratBalasanPenelitian)
        {{-- Header Ringkasan --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-teal-50 px-2.5 py-1 font-mono text-xs font-bold text-teal-700">
                        # {{ $suratBalasanPenelitian->no }}
                    </span>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $suratBalasanPenelitian->status === \App\Enums\StatusApproval::APPROVED ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($suratBalasanPenelitian->status === \App\Enums\StatusApproval::REJECTED ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
                        {{ $suratBalasanPenelitian->status->nama() }}
                    </span>
                </div>
                <span class="text-xs text-slate-400 font-medium">
                    Dibuat: {{ $suratBalasanPenelitian->tgl ? $suratBalasanPenelitian->tgl->format('d M Y') : '-' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                <div>
                    <span class="text-slate-400 uppercase font-semibold text-[10px] block">Universitas & Fakultas:</span>
                    <span class="font-bold text-slate-800">{{ $suratBalasanPenelitian->tujuan_universitas }}</span>
                    <span class="text-slate-500 block">{{ $suratBalasanPenelitian->tujuan_fakultas }}</span>
                </div>
                <div>
                    <span class="text-slate-400 uppercase font-semibold text-[10px] block">Perihal Surat Masuk:</span>
                    <span class="font-bold text-slate-800">{{ $suratBalasanPenelitian->perihal_surat_masuk }}</span>
                    <span class="text-slate-500 block">No: {{ $suratBalasanPenelitian->nomor_surat_masuk ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 uppercase font-semibold text-[10px] block">Total Biaya & Peneliti:</span>
                    <span class="font-bold text-teal-700 font-mono text-sm block">
                        Rp {{ number_format($suratBalasanPenelitian->total_biaya, 0, ',', '.') }}
                    </span>
                    <span class="text-slate-500">{{ $suratBalasanPenelitian->mahasiswa->count() }} Orang Peneliti</span>
                </div>
            </div>
        </div>

        {{-- Tabel Identitas Mahasiswa Peneliti --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                <x-tabler-users class="size-4 text-indigo-600" />
                Identitas Mahasiswa Peneliti
            </h4>

            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="p-2.5 w-10 text-center">No</th>
                            <th class="p-2.5">Nama</th>
                            <th class="p-2.5">NPM</th>
                            <th class="p-2.5">Fakultas / Perguruan Tinggi</th>
                            <th class="p-2.5">Judul / Topik Penelitian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($suratBalasanPenelitian->mahasiswa as $idx => $mhs)
                            <tr>
                                <td class="p-2.5 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                <td class="p-2.5 font-bold text-slate-800">{{ $mhs->nama }}</td>
                                <td class="p-2.5 font-mono text-slate-600">{{ $mhs->npm ?: '-' }}</td>
                                <td class="p-2.5 text-slate-700">{{ $mhs->fakultas_pt }}</td>
                                <td class="p-2.5 text-slate-600 italic">{{ $mhs->judul_penelitian ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-xs text-slate-400 italic">Belum ada data mahasiswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabel Rincian Biaya (Lampiran) --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                <x-tabler-file-dollar class="size-4 text-teal-600" />
                Rincian Biaya Penelitian & Pendidikan (Lampiran)
            </h4>

            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="p-2.5 w-10 text-center">No</th>
                            <th class="p-2.5">Biaya Penelitian & Pendidikan</th>
                            <th class="p-2.5 text-right">Jasa Sarana</th>
                            <th class="p-2.5 text-right">Jasa Pelayanan</th>
                            <th class="p-2.5 text-right">Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($suratBalasanPenelitian->biaya as $bIdx => $item)
                            <tr>
                                <td class="p-2.5 text-center font-bold text-slate-400">{{ $bIdx + 1 }}</td>
                                <td class="p-2.5">
                                    <div class="font-bold text-slate-800">{{ $item->keterangan }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $item->jumlah_orang }} orang x Rp {{ number_format($item->jasa_sarana + $item->jasa_pelayanan, 0, ',', '.') }}</div>
                                </td>
                                <td class="p-2.5 text-right font-mono text-slate-700">Rp {{ number_format($item->jasa_sarana, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right font-mono text-slate-700">Rp {{ number_format($item->jasa_pelayanan, 0, ',', '.') }}</td>
                                <td class="p-2.5 text-right font-mono font-bold text-slate-800">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-teal-50/70 border-t border-slate-200 font-bold text-slate-800">
                        <tr>
                            <td colspan="4" class="p-2.5 text-right uppercase tracking-wider text-[11px]">Total Keseluruhan</td>
                            <td class="p-2.5 text-right font-mono text-teal-800 text-sm">
                                Rp {{ number_format($suratBalasanPenelitian->total_biaya, 0, ',', '.') }},-
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Kolom TTD Direktur --}}
        <div class="flex justify-end pt-1">
            <div class="rounded-xl border border-teal-200/80 bg-teal-50/40 p-3.5 text-xs text-center min-w-[220px]">
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Direktur Penandatangan</span>
                @if($suratBalasanPenelitian->status === \App\Enums\StatusApproval::APPROVED)
                    <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-xs bg-emerald-100 px-2 py-0.5 rounded-full mb-1">
                        <x-tabler-checks class="size-3.5" /> Disetujui
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-amber-700 font-semibold text-[11px] mb-1">
                        <x-tabler-clock class="size-3.5" /> Menunggu Persetujuan
                    </span>
                @endif
                <span class="font-bold text-slate-800 block text-xs mt-1">{{ optional($suratBalasanPenelitian->direktur)->full_nama ?? 'dr. Rachmawati, MPH' }}</span>
                <span class="text-slate-400 font-mono text-[10px] block">NIP: {{ optional($suratBalasanPenelitian->direktur)->nip ?? '24170002' }}</span>
            </div>
        </div>

        {{-- Tombol Cetak --}}
        <div class="ml-auto flex items-center gap-2 pt-2 border-t border-slate-100">
            <div id="print-balasan-penelitian" class="hidden">
                <livewire:Surat.BalasanPenelitian.PrintBalasanPenelitian :$suratBalasanPenelitian />
            </div>
            <x-ts:button sm icon="tabler.printer" x-on:click="printArea('print-balasan-penelitian')">
                Cetak Surat & Lampiran
            </x-ts:button>
        </div>
    @endif
</div>
