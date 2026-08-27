<form wire:submit.prevent="save" class="flex flex-col gap-4">
    <div class="rounded-xl border border-teal-100 bg-teal-50/50 p-3 text-xs text-teal-900 leading-relaxed">
        <strong>💡 Informasi Tarif Penelitian:</strong> Tambahkan standar tarif penelitian baru di sini. Saat membuat surat baru, tarif aktif dapat langsung digunakan.
    </div>

    {{-- Form Tambah Tarif --}}
    <x-ts:input label="Jenis / Nama Biaya Penelitian *" placeholder="contoh: Penelitian Skripsi / Presurvey" wire:model="jenis_penelitian" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <x-ts:input label="Jasa Sarana (Default) *" type="number" min="0" wire:model="jasa_sarana" prefix="Rp" />
        <x-ts:input label="Jasa Pelayanan (Default) *" type="number" min="0" wire:model="jasa_pelayanan" prefix="Rp" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <x-ts:input label="Nomor SK (Opsional)" placeholder="contoh: 024/Kpts-S4/PBA-A10/10.01.22" wire:model="nomor_sk" />
        <x-ts:input label="Tanggal Berlaku *" type="date" wire:model="tgl_berlaku" />
    </div>

    {{-- Pengaturan Format Nomor Surat --}}
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
        <x-ts:input label="Template Format Nomor Surat *" wire:model="format_nomor" hint="Tag: {no}, {kode_jabatan}, {tanggal}, {dd}, {mm}, {yyyy}" />
        <div class="mt-1.5 flex items-center gap-1.5 text-[11px] text-slate-500 font-mono">
            <span>Contoh hasil:</span>
            <span class="font-bold text-slate-700">1/S4/B-PNL/PBA-DIR/14.08.2026</span>
        </div>
    </div>

    {{-- Daftar Tarif Tersedia --}}
    @if($daftarTarif->isNotEmpty())
        <div class="rounded-xl border border-slate-200 p-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-2">Daftar Tarif Penelitian Standar</span>
            <div class="space-y-1.5 text-xs max-h-36 overflow-y-auto">
                @foreach($daftarTarif as $dt)
                    <div class="flex items-center justify-between p-2 rounded bg-slate-50 border border-slate-100 text-slate-700">
                        <div>
                            <span class="font-bold">{{ $dt->jenis_penelitian }}</span>
                            <div class="text-[11px] text-slate-500">Sarana: Rp {{ number_format($dt->jasa_sarana, 0, ',', '.') }} • Pelayanan: Rp {{ number_format($dt->jasa_pelayanan, 0, ',', '.') }}</div>
                        </div>
                        <span class="text-[11px] font-mono text-slate-500">{{ $dt->tgl_berlaku ? $dt->tgl_berlaku->format('d/m/Y') : '-' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="ml-auto flex items-center gap-2 pt-2">
        <x-ts:button type="button" outline color="secondary" sm x-on:click="$dispatch('close-modal', {id: 'modal-konfig-tarif-penelitian'})">
            Batal
        </x-ts:button>
        <x-ts:button type="submit" color="teal" sm icon="tabler.device-floppy" loading="save">
            Simpan Konfigurasi
        </x-ts:button>
    </div>
</form>
