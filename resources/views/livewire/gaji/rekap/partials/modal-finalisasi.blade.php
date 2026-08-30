<!-- Modal Finalisasi & SP3 -->
<x-ts:modal wire="isFinalisasiModalOpen" size="md" class="relative z-50">
    <x-slot:title>
        <span class="flex items-center gap-1.5 font-bold text-slate-800">
            <x-tabler-lock class="h-5 w-5 text-emerald-500" />
            Finalisasi & Kirim ke SP3
        </span>
    </x-slot:title>

    <form wire:submit.prevent="submitFinalisasi" class="space-y-4 p-2">
        <!-- Summary Information -->
        <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 space-y-2 text-xs">
            <span class="text-[10px] text-emerald-700 font-bold uppercase tracking-wider block mb-1">Ringkasan Penggajian Bulanan</span>
            <div class="flex justify-between">
                <span class="text-slate-500 font-medium">Periode</span>
                <span class="text-slate-800 font-bold">
                    @if($finalisasiPeriode)
                        {{ \Carbon\Carbon::parse($finalisasiPeriode . '-01')->translatedFormat('F Y') }}
                    @endif
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 font-medium">Total Karyawan</span>
                <span class="text-slate-800 font-bold">{{ $finalisasiKaryawanCount }} Karyawan</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 font-medium">Total Potongan</span>
                <span class="text-slate-800 font-bold">Rp {{ number_format($finalisasiTotalPotongan, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between border-t border-emerald-100/50 pt-2 mt-1">
                <span class="text-emerald-700 font-bold">Total Gaji Bersih</span>
                <span class="text-emerald-800 font-black text-sm">Rp {{ number_format($finalisasiTotalGajiBersih, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="p-3 bg-amber-50 rounded-xl text-[10px] text-amber-700 border border-amber-100 leading-relaxed">
            <span class="font-bold block mb-0.5">Penting:</span>
            Tindakan ini akan mengunci payroll periode tersebut dari segala bentuk pengeditan dan secara otomatis menerbitkan dokumen pencairan dana (SP3).
        </div>

        <!-- SP3 Setup Form Fields -->
        <div class="space-y-3.5 pt-2">
            <div>
                <x-ts:input label="Tanggal SP3" wire:model.defer="formSp3Tgl" type="date" class="w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Metode Pembayaran</label>
                <select wire:model.defer="formSp3Bayar" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="trf">Transfer Bank</option>
                    <option value="tunai">Tunai</option>
                    <option value="giro">Giro</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Verifikator Keuangan (Tahap 1)</label>
                <select wire:model.defer="formSp3VerifikatorKeuanganId" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Pilih Verifikator Keuangan --</option>
                    @foreach($verifikatorKeuanganOptions as $vOpt)
                        <option value="{{ $vOpt['value'] }}">{{ $vOpt['label'] }}</option>
                    @endforeach
                </select>
                @error('formSp3VerifikatorKeuanganId')
                    <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Pejabat Menyetujui (Approval SP3)</label>
                <select wire:model.defer="formSp3JabatanId" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Pilih Pejabat --</option>
                    @foreach($mengetahuiOptions as $opt)
                        <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                    @endforeach
                </select>
                @error('formSp3JabatanId')
                    <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
            <x-ts:button type="button" flat color="slate" wire:click="closeFinalisasiModal">Batal</x-ts:button>
            <x-ts:button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm">
                Finalisasi & Kirim ke SP3
            </x-ts:button>
        </div>
    </form>
</x-ts:modal>
