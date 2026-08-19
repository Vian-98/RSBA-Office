<div class="flex flex-col gap-4">
    @if ($suratPerintahTugas)
        {{-- Header Ringkasan --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-amber-50 px-2.5 py-1 font-mono text-xs font-bold text-amber-700">
                        # {{ $suratPerintahTugas->no }}
                    </span>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $suratPerintahTugas->status === \App\Enums\StatusApproval::APPROVED ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($suratPerintahTugas->status === \App\Enums\StatusApproval::REJECTED ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
                        {{ $suratPerintahTugas->status->nama() }}
                    </span>
                </div>
                <span class="text-xs text-slate-400 font-medium">
                    Dikeluarkan: {{ $suratPerintahTugas->tgl ? $suratPerintahTugas->tgl->format('d M Y') : '-' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs mb-3">
                <div class="md:col-span-3">
                    <span class="text-slate-400 uppercase font-semibold text-[10px] block">Perintah / Penugasan:</span>
                    <span class="font-bold text-slate-800 text-sm leading-relaxed block">{{ $suratPerintahTugas->perihal }}</span>
                </div>
                <div>
                    <span class="text-slate-400 uppercase font-semibold text-[10px] block">Hari & Tanggal:</span>
                    <span class="font-bold text-slate-800">{{ $suratPerintahTugas->hari_tanggal }}</span>
                </div>
                <div>
                    <span class="text-slate-400 uppercase font-semibold text-[10px] block">Waktu:</span>
                    <span class="font-bold text-slate-800">{{ $suratPerintahTugas->waktu }}</span>
                </div>
                <div>
                    <span class="text-slate-400 uppercase font-semibold text-[10px] block">Tempat:</span>
                    <span class="font-bold text-slate-800">{{ $suratPerintahTugas->tempat }}</span>
                </div>
            </div>
        </div>

        {{-- Tabel Karyawan yang Ditugaskan --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                <x-tabler-users class="size-4 text-indigo-600" />
                Karyawan yang Ditugaskan ({{ $suratPerintahTugas->karyawanTugas->count() }} Orang)
            </h4>

            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="p-2.5 w-10 text-center">No</th>
                            <th class="p-2.5">Nama Karyawan</th>
                            <th class="p-2.5">NIP</th>
                            <th class="p-2.5">Jabatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($suratPerintahTugas->karyawanTugas as $idx => $item)
                            <tr>
                                <td class="p-2.5 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                <td class="p-2.5 font-bold text-slate-800">{{ $item->nama }}</td>
                                <td class="p-2.5 font-mono text-slate-600">{{ $item->nip }}</td>
                                <td class="p-2.5 text-slate-700">{{ $item->jabatan_nama }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-xs text-slate-400 italic">Belum ada karyawan yang ditugaskan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kolom TTD Direktur --}}
        <div class="flex justify-end pt-1">
            <div class="rounded-xl border border-amber-200/80 bg-amber-50/40 p-3.5 text-xs text-center min-w-[220px]">
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Pemberi Perintah (Direktur)</span>
                @if($suratPerintahTugas->status === \App\Enums\StatusApproval::APPROVED)
                    <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-xs bg-emerald-100 px-2 py-0.5 rounded-full mb-1">
                        <x-tabler-checks class="size-3.5" /> Disetujui
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-amber-700 font-semibold text-[11px] mb-1">
                        <x-tabler-clock class="size-3.5" /> Menunggu Persetujuan
                    </span>
                @endif
                <span class="font-bold text-slate-800 block text-xs mt-1">{{ optional($suratPerintahTugas->direktur)->full_nama ?? 'dr. Rachmawati, MPH' }}</span>
                <span class="text-slate-400 font-mono text-[10px] block">NIP: {{ optional($suratPerintahTugas->direktur)->nip ?? '24170002' }}</span>
            </div>
        </div>

        <div class="ml-auto flex items-center gap-2 pt-2 border-t border-slate-100">
            <div id="print-perintah-tugas" class="hidden">
                <livewire:Surat.PerintahTugas.PrintPerintahTugas :$suratPerintahTugas :key="'print-perintah-tugas-'.$suratPerintahTugas->id.'-'.($suratPerintahTugas->docstore_key ?? 'draft')" />
            </div>
            <x-ts:button sm icon="tabler.printer" x-on:click="printArea('print-perintah-tugas')">
                Cetak Surat Perintah Tugas
            </x-ts:button>
        </div>
    @endif
</div>
