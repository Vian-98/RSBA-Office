<div>
    {{ $this->table }}

    <x-filament::modal id="detil-surat-cuti">
        <livewire:Surat.Cuti.DetilTanggalCuti :$surat :key="'detail-tgl-cuti-' . Str::random(3)" />
    </x-filament::modal>


    <x-filament::modal id="modal-status-cuti">
        <livewire:Surat.Cuti.ViewStatus :$surat :key="'view-status-cuti-' . Str::random(3)" />
    </x-filament::modal>


    <x-filament::modal id="modal-approval-cuti" width="3xl" :close-by-clicking-away="false" x-on:surat-cuti-approved.window="$dispatch('close-modal',{id:'modal-approval-cuti'})">
        <x-slot name="heading">
            Persetujuan Surat Cuti
        </x-slot>
        <livewire:Surat.Cuti.Approval :suratCuti="$surat" :key="Str::random(5)" @surat-cuti-approved="$refresh" />
    </x-filament::modal>

    <x-filament::modal id="modal-options-approval-manual" width="3xl" :close-by-clicking-away="false" x-on:surat-cuti-manual-approved.window="$dispatch('close-modal',{id:'modal-options-approval-manual'})">
        <x-slot name="heading">
            Print Pengajuan Manual
        </x-slot>
        <livewire:Surat.Cuti.ApprovalManual :suratCuti="$surat" :key="'manual-approve-' . Str::random(3)" @surat-cuti-manual-approved="$refresh" />
    </x-filament::modal>

    <x-filament::modal id="modal-adjust-cuti-melahirkan" width="xl">
        <x-slot name="heading">
            Penyesuaian Tanggal Melahirkan (H+45 Hari)
        </x-slot>

        @if ($surat)
            <div class="flex flex-col gap-4 py-2 text-sm">
                <div class="rounded-lg border border-amber-200 bg-amber-50/60 p-3 text-xs text-amber-800">
                    <span class="font-bold">Informasi Kebijakan SDM:</span>
                    <p class="mt-1">Apabila persalinan terjadi lebih cepat dari estimasi, tanggal akhir cuti disesuaikan menjadi <strong>H+45 hari sejak tanggal melahirkan aktual</strong>.</p>
                </div>

                <div class="grid grid-cols-2 gap-3 rounded-lg border border-gray-100 bg-gray-50/50 p-3">
                    <div>
                        <span class="text-xs text-gray-400 block">Karyawan</span>
                        <span class="font-semibold text-gray-800">{{ $surat->karyawan?->nama }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">No Surat</span>
                        <span class="font-semibold text-gray-800">{{ $surat->no_surat }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Tanggal Mulai Cuti</span>
                        <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($surat->tgl_mulai)->translatedFormat('d F Y') }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Tanggal Akhir Saat Ini</span>
                        <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($surat->tgl_akhir)->translatedFormat('d F Y') }} ({{ $surat->lama_cuti }} Hari)</span>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-700">Tanggal Melahirkan Aktual <span class="text-rose-500">*</span></label>
                    <input type="date" wire:model.live="tglMelahirkanAktual" class="w-full rounded-lg border-gray-300 text-sm focus:border-amber-500 focus:ring-amber-500" />
                </div>

                @if ($tglMelahirkanAktual)
                    @php
                        $tglAktualCarbon = \Carbon\Carbon::parse($tglMelahirkanAktual);
                        $tglAkhirEstimasi = $tglAktualCarbon->copy()->addDays(45);
                        $tglMulaiCarbon = \Carbon\Carbon::parse($surat->tgl_mulai);
                        $totalLamaCutiNew = $tglMulaiCarbon->diffInDays($tglAkhirEstimasi) + 1;
                    @endphp
                    <div class="rounded-lg border border-indigo-100 bg-indigo-50/60 p-3 text-xs text-indigo-900 flex flex-col gap-1">
                        <span class="font-bold text-indigo-700">Hasil Kalkulasi Ulang Cuti:</span>
                        <div class="flex justify-between mt-1">
                            <span>Tanggal Selesai Cuti Baru (H+45):</span>
                            <span class="font-bold text-indigo-600">{{ $tglAkhirEstimasi->translatedFormat('d F Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Hari Cuti Disesuaikan:</span>
                            <span class="font-bold text-indigo-600">{{ $totalLamaCutiNew }} Hari</span>
                        </div>
                    </div>
                @endif

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-700">Catatan Penyesuaian SDM</label>
                    <textarea wire:model="catatanPenyesuaian" rows="2" placeholder="Catatan internal/alasan penyesuaian..." class="w-full rounded-lg border-gray-300 text-xs focus:border-amber-500 focus:ring-amber-500"></textarea>
                </div>

                <div class="mt-3 flex justify-end gap-2">
                    <x-ts:button outline sm color="gray" x-on:click="$dispatch('close-modal', {id: 'modal-adjust-cuti-melahirkan'})">Batal</x-ts:button>
                    <x-ts:button sm color="amber" icon="tabler.check" wire:click="saveAdjustmentMelahirkan" loading="saveAdjustmentMelahirkan">Simpan Penyesuaian</x-ts:button>
                </div>
            </div>
        @endif
    </x-filament::modal>

    <div x-data x-on:trigger-print.window="$nextTick(() => printArea('print-cuti-approved', $event.detail?.noSurat ?? 'Surat_Cuti'))">
        <div class="hidden" id="print-cuti-approved">
            @if ($surat)
                <livewire:Surat.Cuti.PrintCuti :suratCuti="$surat" :key="'print-cuti-' . $surat->id . '-' . optional($surat->updated_at)->timestamp" />
            @endif
        </div>
    </div>
</div>
