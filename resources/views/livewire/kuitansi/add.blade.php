<form wire:submit.prevent="submit" class="flex flex-col gap-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Tanggal --}}
        <div>
            <x-ts:date label="Tanggal Kuitansi *" wire:model.defer="tanggal" format="YYYY-MM-DD" required />
        </div>

        {{-- Metode Bayar --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Metode Bayar *</label>
            <div class="flex gap-2">
                <select wire:model.defer="metode_bayar" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300">
                    @foreach($metodeBayarOptions as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            @error('metode_bayar') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Telah Diterima Dari --}}
        <div>
            <x-ts:input label="Telah Diterima Dari (Nama Pasien / Pembayar)" wire:model.defer="diterima_dari" placeholder="Contoh: Bpk. Ahmad / Keluarga Pasien" />
        </div>

        {{-- Penerima (Kasir / Staf yang bertanda tangan) --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Penerima (Staf yang bertanda tangan) *</label>
            <x-ts:select.styled 
                wire:key="penerima-select"
                searchable 
                grouped
                wire:model.live="penerima_id" 
                placeholder="Pilih Staf Penerima Uang" 
                :request="route('api.karyawan.kuitansi.approver')" 
                select="label:label|value:id"
            />
            <div class="mt-1">
                <x-ts:input wire:model.defer="penerima_nama" placeholder="Nama Penerima Pada Cetakan" />
            </div>
            @error('penerima_nama') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Pejabat Persetujuan / Verifikator (Wajib dipilih minimal 1) --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Persetujuan / Verifikator *</label>
        <x-ts:select.styled 
            wire:key="approver-kuitansi-select"
            multiple 
            searchable 
            grouped
            wire:model.defer="approvers" 
            placeholder="Pilih Pejabat Persetujuan (Atasan / Staf Keuangan)" 
            :request="route('api.karyawan.kuitansi.approver')" 
            select="label:label|value:id"
        />
        @error('approvers') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
    </div>

    {{-- Untuk Pembayaran (Keterangan Utama) --}}
    <div>
        <x-ts:textarea label="Untuk Pembayaran (Keterangan) *" wire:model.defer="keterangan" rows="2" placeholder="Contoh: Biaya Pemeriksaan Poli Umum dan Obat..." required />
        @error('keterangan') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
    </div>

    {{-- Rincian Item (Opsional Itemisasi) --}}
    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-bold text-slate-700">Rincian Item Pembayaran (Opsional)</span>
            <button type="button" wire:click="addItem" class="flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                <x-tabler-plus class="size-4" />
                Tambah Baris Item
            </button>
        </div>

        <div class="flex flex-col gap-2">
            @foreach($items as $index => $item)
                <div class="flex items-center gap-2" wire:key="item-row-{{ $index }}">
                    <div class="flex-1">
                        <input type="text" wire:model.defer="items.{{ $index }}.keterangan" placeholder="Nama item/layanan (mis. Jasa Dokter / Obat)" class="w-full rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none" />
                    </div>
                    <div class="w-48">
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="items.{{ $index }}.nominal" 
                            x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" 
                            placeholder="Nominal (Rp)" 
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none text-right font-mono" 
                        />
                    </div>
                    @if(count($items) > 1)
                        <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 p-1">
                            <x-tabler-trash class="size-4" />
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Total Nominal --}}
    <div class="flex items-center justify-between rounded-lg bg-indigo-50 border border-indigo-100 p-4">
        <div>
            <span class="text-sm font-semibold text-slate-700">Total Nominal (Rp) *</span>
            <p class="text-xs text-slate-500">Jumlah total yang akan tercetak pada kuitansi</p>
        </div>
        <div class="w-60">
            <x-ts:input 
                wire:model.live.debounce.300ms="jumlah" 
                x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" 
                placeholder="0" 
                class="text-right font-bold text-lg font-mono" 
                required 
            />
            @error('jumlah') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
        </div>
    </div>


    {{-- Footer Actions --}}
    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
        <x-ts:button type="button" outline color="secondary" x-on:click="$dispatch('close-modal',{id:'modal-add-kuitansi'})">
            Batal
        </x-ts:button>
        <x-ts:button type="submit" color="primary" loading="submit">
            Simpan & Ajukan Persetujuan
        </x-ts:button>
    </div>
</form>
