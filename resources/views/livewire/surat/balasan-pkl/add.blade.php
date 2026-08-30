<form wire:submit.prevent="submit" class="flex flex-col gap-4">
    {{-- Baris 1: Tanggal & Tujuan Universitas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <x-ts:input label="Tanggal Surat *" type="date" wire:model="tgl" />
        <x-ts:input label="Universitas / Institusi *" placeholder="contoh: Universitas Malahayati" wire:model="tujuan_universitas" />
    </div>

    {{-- Baris 2: Dekan/Penerima & Program Studi --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <x-ts:input label="Kepada Yth. (Dekan / Kaprodi)" placeholder="contoh: Dekan Fakultas Kedokteran" wire:model="tujuan_nama" />
        <x-ts:input label="Program Studi *" placeholder="contoh: Ilmu Keperawatan / Farmasi" wire:model="prodi" />
    </div>

    {{-- Baris 3: Surat Masuk dari Universitas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 rounded-lg bg-slate-50 p-3 border border-slate-200">
        <x-ts:input label="Nomor Surat Masuk dari Kampus" placeholder="contoh: 123/FK-UNIMAL/VII/2026" wire:model="nomor_surat_masuk" />
        <x-ts:input label="Tanggal Surat Masuk" type="date" wire:model="tgl_surat_masuk" />
    </div>

    {{-- Baris 4: Periode Pelaksanaan & Lama Bulan --}}
    <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3.5 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <x-tabler-calendar class="size-4 text-indigo-600" />
                Periode Pelaksanaan PKL & Durasi
            </span>
            {{-- Quick Presets --}}
            <div class="flex items-center gap-1">
                <span class="text-[11px] text-slate-400 mr-1 font-medium hidden sm:inline">Pilihan Cepat:</span>
                <button type="button" wire:click="setPresetBulan(1)" class="px-2 py-0.5 text-[11px] font-semibold rounded bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-slate-200 shadow-2xs transition">
                    1 Bln
                </button>
                <button type="button" wire:click="setPresetBulan(2)" class="px-2 py-0.5 text-[11px] font-semibold rounded bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-slate-200 shadow-2xs transition">
                    2 Bln
                </button>
                <button type="button" wire:click="setPresetBulan(3)" class="px-2 py-0.5 text-[11px] font-semibold rounded bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-slate-200 shadow-2xs transition">
                    3 Bln
                </button>
                <button type="button" wire:click="setPresetBulan(6)" class="px-2 py-0.5 text-[11px] font-semibold rounded bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-slate-200 shadow-2xs transition">
                    6 Bln
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-start">
            <x-ts:input label="Tanggal Mulai *" type="date" wire:model.live.debounce.300ms="tgl_mulai" />
            <x-ts:input label="Tanggal Selesai *" type="date" wire:model.live.debounce.300ms="tgl_selesai" />
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-semibold text-slate-700">
                        Lama Praktik (Bulan) *
                    </label>
                    <button type="button" 
                            wire:click="toggleManualBulan" 
                            class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded transition-all shadow-2xs {{ $is_manual_bulan ? 'bg-amber-500 text-white hover:bg-amber-600' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-300' }}"
                            title="{{ $is_manual_bulan ? 'Mode Manual Aktif. Klik gembok untuk hitung otomatis dari tanggal.' : 'Mode Otomatis Aktif (Dihitung dari tanggal). Klik gembok untuk ubah angka manual.' }}">
                        @if($is_manual_bulan)
                            <x-tabler-lock-open class="size-3 text-white" />
                            <span>Manual</span>
                        @else
                            <x-tabler-lock class="size-3 text-slate-500" />
                            <span>Otomatis</span>
                        @endif
                    </button>
                </div>
                <x-ts:input type="number" 
                            min="1" 
                            wire:model.live.debounce.300ms="lama_praktik_bulan" 
                            :readonly="!$is_manual_bulan" 
                            :class="!$is_manual_bulan ? 'bg-slate-100/80 font-bold text-indigo-700 cursor-not-allowed' : 'font-bold text-amber-800 focus:ring-amber-500'" />
                <p class="text-[10px] mt-1 {{ $is_manual_bulan ? 'text-amber-600 font-medium' : 'text-slate-500' }}">
                    @if($is_manual_bulan)
                        <span class="inline-flex items-center gap-0.5">
                            <x-tabler-pencil class="size-3" /> Input manual aktif.
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 font-medium text-indigo-700">
                            <x-tabler-calendar-event class="size-3.5 text-indigo-500" />
                            Durasi: <strong>{{ $total_hari }} hari</strong> (Dihitung: <strong>{{ $lama_praktik_bulan }} bulan</strong>)
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Baris 5: Snapshot Tarif yang Diterapkan --}}
    <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-3.5 text-xs">
        <div class="flex items-center justify-between font-bold text-indigo-900 mb-2">
            <span class="flex items-center gap-1.5">
                <x-tabler-calculator class="size-4 text-indigo-600" />
                Tarif PKL Aktif (Snapshot SK Direktur):
            </span>
            <span class="text-[11px] text-indigo-600 font-mono">SK: {{ $snap_nomor_sk ?: '-' }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Biaya Izin Praktik (/org/bln)</label>
                <x-ts:input type="number" min="0" wire:model.live="snap_biaya_praktik" prefix="Rp" />
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Biaya Orientasi (/org) — <span class="text-slate-400 font-normal">Isi 0 jika tidak ada</span></label>
                <x-ts:input type="number" min="0" wire:model.live="snap_biaya_orientasi" prefix="Rp" />
            </div>
        </div>

        <div class="mt-2.5 pt-2 border-t border-indigo-200/60 flex items-center justify-between text-xs font-bold text-slate-700">
            <span>Estimasi Total Biaya ({{ (int) ($jumlah_mahasiswa ?: 1) }} Mhs x {{ (int) ($lama_praktik_bulan ?: 1) }} Bln):</span>
            <span class="text-indigo-700 text-sm font-mono">
                Rp {{ number_format($this->totalEstimasi, 0, ',', '.') }}
            </span>
        </div>
    </div>

    {{-- Baris 6: Pilihan Direktur (Jika > 1) --}}
    @if(count($direkturOptions) > 1)
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Penandatangan Direktur *</label>
            <select wire:model="selectedDirekturIndex" wire:change="selectDirektur($event.target.value)" class="w-full rounded-lg border border-slate-300 p-2 text-xs">
                @foreach($direkturOptions as $idx => $dir)
                    <option value="{{ $idx }}">{{ $dir['label'] }} (NIP: {{ $dir['nip'] }})</option>
                @endforeach
            </select>
        </div>
    @else
        <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-200 text-xs flex items-center justify-between">
            <span class="text-slate-500 font-semibold">Penandatangan Direktur:</span>
            <span class="font-bold text-slate-800">{{ $direkturOptions[0]['label'] ?? 'dr. Rachmawati, MPH (Direktur)' }}</span>
        </div>
    @endif

    {{-- Baris 7: Daftar Mahasiswa --}}
    <div class="rounded-xl border border-slate-200 bg-white p-3.5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Daftar Mahasiswa ({{ count($mahasiswaList) }} Orang)</span>
            <x-ts:button type="button" xs outline color="primary" icon="tabler.plus" wire:click="addMahasiswa">
                Tambah Baris
            </x-ts:button>
        </div>

        <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
            @foreach($mahasiswaList as $index => $mhs)
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-400 w-5">{{ $index + 1 }}.</span>
                    <div class="flex-1">
                        <x-ts:input placeholder="Nama Lengkap Mahasiswa *" wire:model="mahasiswaList.{{ $index }}.nama" />
                    </div>
                    <div class="w-40">
                        <x-ts:input placeholder="NPM (Opsional)" wire:model="mahasiswaList.{{ $index }}.npm" />
                    </div>
                    @if(count($mahasiswaList) > 1)
                        <button type="button" wire:click="removeMahasiswa({{ $index }})" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-md">
                            <x-tabler-trash class="size-4" />
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="ml-auto flex items-center gap-2 pt-2">
        <x-ts:button type="button" outline color="secondary" sm x-on:click="$dispatch('close-modal', {id: 'modal-add-balasan-pkl'})">
            Batal
        </x-ts:button>
        <x-ts:button type="submit" color="primary" sm icon="tabler.send" loading="submit">
            Simpan & Ajukan ke Direktur
        </x-ts:button>
    </div>
</form>
