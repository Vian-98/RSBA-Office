<form wire:submit.prevent="submit" class="flex flex-col gap-4">
    {{-- Pilih Asset --}}
    <div class="flex flex-col gap-1">
        <label class="text-sm font-semibold text-gray-700">Unit Asset Barang <span class="text-red-500">*</span></label>
        <x-ts:select.styled wire:model.defer="asset_id" :request="route('api.asset.ref')" select="label:label|value:value" searchable placeholder="Cari Kode Asset / Nama Barang / Ruangan..." />
        @error('asset_id')
            <span class="text-xs text-red-500">{{ $message }}</span>
        @enderror
    </div>

    {{-- Radio Opsi Alur --}}
    <div class="flex flex-col gap-1 border-y border-gray-100 py-2">
        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pilih Alur Pembuatan Tiket</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
            <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-all {{ $workflow_type === 'approved' ? 'border-indigo-500 bg-indigo-50/50 text-indigo-900 font-semibold' : 'border-gray-200 hover:bg-gray-50' }}">
                <input type="radio" wire:model.live="workflow_type" value="approved" class="text-indigo-600 focus:ring-indigo-500" />
                <div class="flex flex-col">
                    <span class="text-sm">Disetujui & Dijadwalkan Langsung</span>
                    <span class="text-[11px] text-gray-500 font-normal">Tiket langsung berstatus Approved & Teknisi ditentukan</span>
                </div>
            </label>
            <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-all {{ $workflow_type === 'pending' ? 'border-indigo-500 bg-indigo-50/50 text-indigo-900 font-semibold' : 'border-gray-200 hover:bg-gray-50' }}">
                <input type="radio" wire:model.live="workflow_type" value="pending" class="text-indigo-600 focus:ring-indigo-500" />
                <div class="flex flex-col">
                    <span class="text-sm">Simpan Sebagai Permintaan (Pending)</span>
                    <span class="text-[11px] text-gray-500 font-normal">Masuk ke antrean daftar permintaan untuk disetujui nanti</span>
                </div>
            </label>
        </div>
    </div>

    {{-- Detail Kerusakan / Note --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="text-xs font-semibold text-gray-600">Prioritas Ticket</label>
            <x-ts:select.styled wire:model.live="priority" :options="$priorityOptions" select="label:label|value:value" :clearable="false" />
        </div>
        @if ($priority !== 'normal')
            <div>
                <label class="text-xs font-semibold text-gray-600">Alasan Prioritas <span class="text-red-500">*</span></label>
                <x-ts:input wire:model.defer="ket_priority" placeholder="Contoh: AC Mati Total di Ruang Operasi" />
                @error('ket_priority')
                    <span class="text-xs text-red-500">{{ $message }}</span>
                @enderror
            </div>
        @endif
    </div>

    <div>
        <label class="text-xs font-semibold text-gray-600">Keterangan Kendala / Masalah <span class="text-red-500">*</span></label>
        <x-ts:textarea wire:model.defer="note" placeholder="Deskripsikan bagian barang yang bermasalah atau detail perbaikan..." rows="3" />
        @error('note')
            <span class="text-xs text-red-500">{{ $message }}</span>
        @enderror
    </div>

    {{-- Skenario Approved (Direct Scheduling & Teknisi) --}}
    @if ($workflow_type === 'approved')
        <div class="flex flex-col gap-3 p-3 bg-indigo-50/40 rounded-lg border border-indigo-100">
            <span class="text-xs font-semibold text-indigo-700 uppercase tracking-wider flex items-center gap-1">
                <x-ts:icon name="tabler.calendar-event" class="h-4 w-4 text-indigo-600" />
                Penjadwalan & Penunjukan Teknisi
            </span>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-600">Tanggal Pelaksanaan <span class="text-red-500">*</span></label>
                    <x-ts:date wire:model.defer="jadwal" placeholder="Tanggal Jadwal Maintenance" />
                    @error('jadwal')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Teknisi Penanggung Jawab <span class="text-red-500">*</span></label>
                    <x-ts:select.styled wire:model.defer="teknisi_id" placeholder="Pilih Teknisi (Leader/Helper)" :request="route('api.users.ref')" select="label:nama|value:id" multiple />
                    @error('teknisi_id')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600">Catatan Khusus Teknisi (Opsional)</label>
                <x-ts:textarea wire:model.defer="catatan_teknisi" placeholder="Instruksi awal untuk teknisi yang bertugas..." rows="2" />
            </div>
        </div>
    @endif

    {{-- Lampiran Foto --}}
    <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-gray-600">Lampiran Dokumentasi (Opsional)</label>
        <x-ts:upload wire:model.defer="lampirans" multiple delete accept="image/*"></x-ts:upload>
    </div>

    {{-- Footer Actions --}}
    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
        <x-ts:button sm type="button" outline color="gray" x-on:click="$dispatch('close-modal', { id: 'modal-direct-create-maintenance' })">
            Batal
        </x-ts:button>
        <x-ts:button sm type="submit" color="indigo" icon="tabler.device-floppy" loading="submit">
            Buat Tiket Maintenance
        </x-ts:button>
    </div>
</form>
