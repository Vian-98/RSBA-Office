<div class="w-full">
    {!! $this->table->toHtml() !!}

    {{-- Modal Detail Kuitansi --}}
    <x-filament::modal id="modal-detail-kuitansi" width="max-w-3xl" :autofocus="false">
        <x-slot:heading>Detail Kuitansi</x-slot:heading>
        @if($kuitansi)
            <div class="flex flex-col gap-4">
                {{-- Header Summary Card --}}
                <div class="flex flex-col rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <span class="text-base font-bold text-indigo-600"># {{ $kuitansi->nomor }}</span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ is_object($kuitansi->status) && $kuitansi->status->color() === 'success' ? 'bg-emerald-100 text-emerald-700' : (is_object($kuitansi->status) && $kuitansi->status->color() === 'danger' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ is_object($kuitansi->status) ? $kuitansi->status->nama() : ucfirst($kuitansi->status) }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-3 text-xs">
                        <div>
                            <span class="text-slate-500 block">Tanggal:</span>
                            <span class="font-semibold text-slate-800">{{ date('d M Y', strtotime($kuitansi->tanggal)) }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Diterima Dari:</span>
                            <span class="font-semibold text-slate-800">{{ $kuitansi->diterima_dari ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Metode Bayar:</span>
                            <span class="font-semibold text-slate-800">{{ $kuitansi->metode_bayar }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Penerima (Kasir):</span>
                            <span class="font-semibold text-slate-800">{{ $kuitansi->penerima_nama }}</span>
                        </div>
                    </div>
                    <div class="mt-3 border-t border-slate-200 pt-2 text-xs">
                        <span class="text-slate-500 block">Untuk Pembayaran:</span>
                        <p class="font-medium text-slate-800 mt-0.5">{{ $kuitansi->keterangan }}</p>
                    </div>
                </div>

                {{-- Rincian Item --}}
                <div class="rounded-lg border border-slate-200 p-4">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-2">Rincian Pembayaran</span>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-100 text-slate-600">
                                <tr>
                                    <th class="py-2 px-3 rounded-l">No</th>
                                    <th class="py-2 px-3">Keterangan Item</th>
                                    <th class="py-2 px-3 text-right rounded-r">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($kuitansi->details as $idx => $det)
                                    <tr>
                                        <td class="py-2 px-3 text-slate-500">{{ $idx + 1 }}</td>
                                        <td class="py-2 px-3 text-slate-800">{{ $det->keterangan }}</td>
                                        <td class="py-2 px-3 text-right font-mono text-slate-800">{{ formatRupiah($det->nominal, true, false) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-2 px-3 text-center text-slate-400">Tidak ada rincian item.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="border-t-2 border-slate-200 font-bold">
                                <tr>
                                    <td colspan="2" class="py-2 px-3 text-slate-700">Total Pembayaran</td>
                                    <td class="py-2 px-3 text-right text-indigo-600 font-mono text-sm">{{ formatRupiah($kuitansi->jumlah, true, false) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-2 rounded bg-indigo-50/50 p-2 text-xs italic text-indigo-900">
                        <strong>Terbilang:</strong> {{ $kuitansi->terbilang }}
                    </div>
                </div>

                {{-- Status Persetujuan --}}
                <div class="rounded-lg border border-slate-200 p-4">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-2">Status Persetujuan</span>
                    <div class="flex flex-wrap gap-4">
                        @forelse($kuitansi->approvals as $app)
                            <div class="flex items-center gap-2 rounded-md border border-slate-200 bg-white p-2 text-xs">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-800">{{ $app->disetujuiOleh?->full_nama ?? $app->disetujuiOleh?->nama ?? 'Pejabat' }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $app->disetujuiOleh?->jabatan?->first()?->nama ?? 'Verifikator' }}</span>
                                    <span class="mt-1 inline-flex items-center gap-1 font-bold {{ $app->status === \App\Enums\StatusApproval::APPROVED ? 'text-emerald-600' : ($app->status === \App\Enums\StatusApproval::MANUAL ? 'text-blue-600' : 'text-amber-600') }}">
                                        @if($app->status === \App\Enums\StatusApproval::APPROVED)
                                            ✓ Disetujui ({{ $app->approved_at ? date('d/m/Y H:i', strtotime($app->approved_at)) : '-' }})
                                        @elseif($app->status === \App\Enums\StatusApproval::MANUAL)
                                            ✓ Persetujuan Manual
                                        @else
                                            ⏳ Menunggu Persetujuan
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @empty
                            <span class="text-xs text-slate-400">Tidak ada persetujuan yang terdaftar.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </x-filament::modal>

    {{-- Modal Approval Kuitansi --}}
    <x-filament::modal id="modal-approval-kuitansi" width="max-w-2xl" :autofocus="false" :close-by-clicking-away="false" x-on:update-approval-kuitansi="$dispatch('close-modal',{id:'modal-approval-kuitansi'})">
        <x-slot:heading>Persetujuan & Tanda Tangan Kuitansi</x-slot:heading>
        @if($kuitansi)
            <livewire:Kuitansi.Approval :$kuitansi :key="'app-'.$kuitansi->id.'-'.Str::random(5)" />
        @endif
    </x-filament::modal>

    {{-- Modal Print Kuitansi --}}
    <x-filament::modal id="modal-print-kuitansi" width="max-w-4xl" :autofocus="false">
        <x-slot:heading>Cetak Kuitansi</x-slot:heading>
        @if($kuitansi)
            <livewire:Kuitansi.PrintKuitansi :$kuitansi :key="'print-'.$kuitansi->id.'-'.Str::random(5)" />
        @endif
    </x-filament::modal>
</div>

