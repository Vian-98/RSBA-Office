<form wire:submit.prevent="save" class="flex flex-col gap-4">
    <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-3 text-xs text-indigo-900 leading-relaxed">
        <strong>💡 Informasi Snapshot Tarif:</strong> Setiap surat balasan PKL baru yang dibuat akan mengunci (snapshot) tarif aktif dan nomor SK saat surat diterbitkan. Perubahan tarif di sini hanya berlaku untuk surat yang dibuat setelahnya.
    </div>

    {{-- Form Konfigurasi Tarif --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <x-ts:input label="Biaya Praktik per Mahasiswa / Bulan *" type="number" min="0" wire:model="biaya_praktik_per_bulan" prefix="Rp" />
        <x-ts:input label="Biaya Orientasi per Mahasiswa *" type="number" min="0" wire:model="biaya_orientasi_per_orang" prefix="Rp" hint="Isi 0 jika tidak ada biaya orientasi" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <x-ts:input label="Nomor SK Direktur *" placeholder="contoh: 023/Kpts-S4/PBA-A10/10.01.22" wire:model="nomor_sk" />
        <x-ts:input label="Tanggal Berlaku *" type="date" wire:model="tgl_berlaku" />
    </div>

    {{-- Pengaturan Format Nomor Surat --}}
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
        <x-ts:input label="Template Format Nomor Surat *" wire:model="format_nomor" hint="Tag: {no}, {kode_jabatan}, {tanggal}, {dd}, {mm}, {yyyy}" />
        <div class="mt-1.5 flex items-center gap-1.5 text-[11px] text-slate-500 font-mono">
            <span>Contoh hasil:</span>
            <span class="font-bold text-slate-700">1/S4/B-PKL/PBA-DIR/14.08.2026</span>
        </div>
    </div>

    <x-ts:textarea wire:model="keterangan" label="Catatan Perubahan (Opsional)" placeholder="Alasan penyesuaian tarif..." rows="2" />

    {{-- Riwayat Tarif --}}
    @if($riwayatTarif->isNotEmpty())
        <div class="rounded-xl border border-slate-200 p-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-2">Riwayat Tarif Terakhir</span>
            <div class="space-y-1.5 text-xs max-h-36 overflow-y-auto">
                @foreach($riwayatTarif as $rt)
                    <div class="flex items-center justify-between p-2 rounded bg-slate-50 border border-slate-100 text-slate-700">
                        <div>
                            <span class="font-bold">Praktik: Rp {{ number_format($rt->biaya_praktik_per_bulan, 0, ',', '.') }}</span>
                            @if($rt->biaya_orientasi_per_orang > 0)
                                <span class="text-slate-400">• Orientasi: Rp {{ number_format($rt->biaya_orientasi_per_orang, 0, ',', '.') }}</span>
                            @endif
                            <div class="text-[10px] text-slate-400">SK: {{ $rt->nomor_sk }}</div>
                        </div>
                        <span class="text-[11px] font-mono text-slate-500">{{ $rt->tgl_berlaku ? $rt->tgl_berlaku->format('d/m/Y') : '-' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="ml-auto flex items-center gap-2 pt-2">
        <x-ts:button type="button" outline color="secondary" sm x-on:click="$dispatch('close-modal', {id: 'modal-konfig-tarif-pkl'})">
            Batal
        </x-ts:button>
        <x-ts:button type="submit" color="primary" sm icon="tabler.device-floppy" loading="save">
            Simpan Konfigurasi
        </x-ts:button>
    </div>
</form>
