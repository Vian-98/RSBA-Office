<div class="flex flex-col gap-4">
    @if ($suratBalasanPkl)
        {{-- Card Header Ringkasan --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-indigo-50 px-2.5 py-1 font-mono text-xs font-bold text-indigo-700">
                        # {{ $suratBalasanPkl->no }}
                    </span>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $suratBalasanPkl->status === \App\Enums\StatusApproval::APPROVED ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($suratBalasanPkl->status === \App\Enums\StatusApproval::REJECTED ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
                        {{ $suratBalasanPkl->status->nama() }}
                    </span>
                </div>
                <span class="text-xs text-slate-400 font-medium">
                    Dibuat: {{ $suratBalasanPkl->tgl ? $suratBalasanPkl->tgl->format('d M Y') : '-' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                <div>
                    <span class="text-slate-400 uppercase font-semibold text-[10px] block">Tujuan Surat / Kampus:</span>
                    <span class="font-bold text-slate-800">{{ $suratBalasanPkl->tujuan_universitas }}</span>
                    @if($suratBalasanPkl->tujuan_nama)
                        <span class="text-slate-500 block">u.p. {{ $suratBalasanPkl->tujuan_nama }}</span>
                    @endif
                </div>
                <div>
                    <span class="text-slate-400 uppercase font-semibold text-[10px] block">Program Studi & Peserta:</span>
                    <span class="font-bold text-slate-800">{{ $suratBalasanPkl->prodi }}</span>
                    <span class="text-slate-600 block">{{ $suratBalasanPkl->jumlah_mahasiswa }} Orang ({{ $suratBalasanPkl->lama_praktik_bulan }} Bulan)</span>
                </div>
                <div>
                    <span class="text-slate-400 uppercase font-semibold text-[10px] block">Periode Praktik:</span>
                    <span class="font-bold text-slate-800 font-mono">
                        {{ $suratBalasanPkl->tgl_mulai ? $suratBalasanPkl->tgl_mulai->format('d/m/Y') : '-' }} s.d {{ $suratBalasanPkl->tgl_selesai ? $suratBalasanPkl->tgl_selesai->format('d/m/Y') : '-' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card Rincian Biaya (Lampiran) --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                <x-tabler-file-dollar class="size-4 text-emerald-600" />
                Rincian Biaya Praktik (Lampiran Surat)
            </h4>

            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="p-2.5 w-10 text-center">No</th>
                            <th class="p-2.5">Biaya Praktek Kerja Lapangan</th>
                            <th class="p-2.5 text-center">Jumlah Siswa</th>
                            <th class="p-2.5 text-center">Lama Praktik</th>
                            <th class="p-2.5 text-right">Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="p-2.5 text-center font-bold text-slate-400">1</td>
                            <td class="p-2.5">
                                <div class="font-bold text-slate-800">Izin Praktek</div>
                                <div class="text-[11px] text-slate-400 font-mono">Rp {{ number_format($suratBalasanPkl->snap_biaya_praktik, 0, ',', '.') }},- / orang / bulan</div>
                            </td>
                            <td class="p-2.5 text-center font-bold text-slate-700">{{ $suratBalasanPkl->jumlah_mahasiswa }} Orang</td>
                            <td class="p-2.5 text-center font-bold text-slate-700">{{ $suratBalasanPkl->lama_praktik_bulan }} Bulan</td>
                            <td class="p-2.5 text-right font-mono font-bold text-slate-800">
                                Rp {{ number_format($suratBalasanPkl->total_biaya_praktik, 0, ',', '.') }},-
                            </td>
                        </tr>
                        @if($suratBalasanPkl->snap_biaya_orientasi > 0)
                            <tr>
                                <td class="p-2.5 text-center font-bold text-slate-400">2</td>
                                <td class="p-2.5">
                                    <div class="font-bold text-slate-800">Orientasi</div>
                                    <div class="text-[11px] text-slate-400 font-mono">Rp {{ number_format($suratBalasanPkl->snap_biaya_orientasi, 0, ',', '.') }},- / orang</div>
                                </td>
                                <td class="p-2.5 text-center font-bold text-slate-700">{{ $suratBalasanPkl->jumlah_mahasiswa }} Orang</td>
                                <td class="p-2.5 text-center text-slate-400">-</td>
                                <td class="p-2.5 text-right font-mono font-bold text-slate-800">
                                    Rp {{ number_format($suratBalasanPkl->total_biaya_orientasi, 0, ',', '.') }},-
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot class="bg-indigo-50/70 border-t border-slate-200 font-bold text-slate-800">
                        <tr>
                            <td colspan="4" class="p-2.5 text-right uppercase tracking-wider text-[11px]">Total Keseluruhan</td>
                            <td class="p-2.5 text-right font-mono text-indigo-700 text-sm">
                                Rp {{ number_format($suratBalasanPkl->grand_total_biaya, 0, ',', '.') }},-
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Card Daftar Mahasiswa --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <x-tabler-users class="size-4 text-indigo-600" />
                Daftar Mahasiswa ({{ $suratBalasanPkl->mahasiswa->count() }} Orang)
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                @forelse($suratBalasanPkl->mahasiswa as $idx => $mhs)
                    <div class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="font-bold text-slate-400 w-5">{{ $idx + 1 }}.</span>
                        <div class="flex-1 min-w-0">
                            <span class="font-bold text-slate-800 block truncate">{{ $mhs->nama }}</span>
                            @if($mhs->npm)
                                <span class="text-[11px] text-slate-400 font-mono block">NPM: {{ $mhs->npm }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 text-center text-xs text-slate-400 italic py-2">Belum ada daftar mahasiswa.</div>
                @endforelse
            </div>
        </div>

        {{-- Kolom TTD Direktur --}}
        <div class="flex justify-end pt-1">
            <div class="rounded-xl border border-amber-200/80 bg-amber-50/40 p-3.5 text-xs text-center min-w-[220px]">
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Direktur Penandatangan</span>
                @if($suratBalasanPkl->status === \App\Enums\StatusApproval::APPROVED)
                    <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-xs bg-emerald-100 px-2 py-0.5 rounded-full mb-1">
                        <x-tabler-checks class="size-3.5" /> Disetujui
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-amber-700 font-semibold text-[11px] mb-1">
                        <x-tabler-clock class="size-3.5" /> Menunggu Persetujuan
                    </span>
                @endif
                <span class="font-bold text-slate-800 block text-xs mt-1">{{ optional($suratBalasanPkl->direktur)->full_nama ?? 'dr. Rachmawati, MPH' }}</span>
                <span class="text-slate-400 font-mono text-[10px] block">NIP: {{ optional($suratBalasanPkl->direktur)->nip ?? '24170002' }}</span>
            </div>
        </div>

        {{-- Tombol Cetak --}}
        <div class="ml-auto flex items-center gap-2 pt-2 border-t border-slate-100">
            <div id="print-balasan-pkl" class="hidden">
                <livewire:Surat.BalasanPkl.PrintBalasanPkl :$suratBalasanPkl />
            </div>
            <x-ts:button sm icon="tabler.printer" x-on:click="printArea('print-balasan-pkl')">
                Cetak Surat & Lampiran
            </x-ts:button>
        </div>
    @endif
</div>
