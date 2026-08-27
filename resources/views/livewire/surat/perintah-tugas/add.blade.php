<form wire:submit.prevent="submit" class="flex flex-col gap-4">
    {{-- Baris 1: Tanggal & Tempat Pelaksanaan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <x-ts:input label="Tanggal Surat Dikeluarkan *" type="date" wire:model="tgl" />
        <x-ts:input label="Tempat Pelaksanaan Tugas *" placeholder="contoh: Aula Pertemuan Lt. 2 RS Bintang Amin" wire:model="tempat" />
    </div>

    {{-- Baris 2: Hari/Tanggal & Waktu --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <x-ts:input label="Hari / Tanggal Pelaksanaan *" placeholder="contoh: Senin / 18 Agustus 2026" wire:model="hari_tanggal" />
        <x-ts:input label="Waktu Pelaksanaan *" placeholder="contoh: 08:00 WIB s.d Selesai" wire:model="waktu" />
    </div>

    {{-- Baris 3: Isi Perintah Tugas --}}
    <div>
        <x-ts:textarea label="Isi Perintah / Penugasan *" placeholder="Tuliskan tugas atau kegiatan yang diperintahkan, contoh: Mengikuti Pelatihan Akreditasi Rumah Sakit Tahun 2026 yang diselenggarakan oleh..." wire:model="perihal" rows="3" />
    </div>

    {{-- Baris 4: Pilihan Direktur (Jika > 1) --}}
    @if(count($direkturOptions) > 1)
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Pemberi Perintah (Direktur) *</label>
            <select wire:model="selectedDirekturIndex" wire:change="selectDirektur($event.target.value)" class="w-full rounded-lg border border-slate-300 p-2 text-xs">
                @foreach($direkturOptions as $idx => $dir)
                    <option value="{{ $idx }}">{{ $dir['label'] }} (NIP: {{ $dir['nip'] }})</option>
                @endforeach
            </select>
        </div>
    @else
        <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-200 text-xs flex items-center justify-between">
            <span class="text-slate-500 font-semibold">Pemberi Perintah (Direktur):</span>
            <span class="font-bold text-slate-800">{{ $direkturOptions[0]['label'] ?? 'dr. Rachmawati, MPH (Direktur)' }}</span>
        </div>
    @endif

    {{-- Baris 5: Multi-select Karyawan yang Ditugaskan --}}
    <div class="rounded-xl border border-slate-200 bg-white p-3.5" x-data="{ searchKaryawan: '' }">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">
                Karyawan yang Ditugaskan ({{ count($selectedKaryawanIds) }} Orang Terpilih) *
            </span>
            <span class="text-[11px] text-slate-400">Minimal 1 orang</span>
        </div>

        {{-- Search input --}}
        <div class="mb-2">
            <input 
                type="text" 
                x-model="searchKaryawan" 
                placeholder="Cari nama karyawan atau jabatan..." 
                class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300"
            />
        </div>

        {{-- Daftar Karyawan yang Dipilih (Badges) --}}
        @if(!empty($selectedKaryawanIds))
            <div class="flex flex-wrap gap-1.5 p-2 rounded-lg bg-indigo-50/60 border border-indigo-100 mb-2">
                @foreach($karyawanList as $k)
                    @if(in_array($k['id'], $selectedKaryawanIds))
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-white px-2 py-1 text-xs font-medium text-slate-800 border border-indigo-200 shadow-2xs">
                            <span class="font-bold">{{ $k['nama'] }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">({{ $k['jabatan'] }})</span>
                            <button type="button" wire:click="removeKaryawan({{ $k['id'] }})" class="text-rose-500 hover:text-rose-700 font-bold">
                                &times;
                            </button>
                        </span>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- List Karyawan Selector --}}
        <div class="max-h-48 overflow-y-auto space-y-1 pr-1 border border-slate-100 rounded-lg p-1">
            @foreach($karyawanList as $k)
                <label 
                    x-show="!searchKaryawan || '{{ strtolower($k['label']) }}'.includes(searchKaryawan.toLowerCase())"
                    class="flex items-center gap-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer text-xs transition {{ in_array($k['id'], $selectedKaryawanIds) ? 'bg-indigo-50/50 font-semibold' : '' }}"
                >
                    <input 
                        type="checkbox" 
                        value="{{ $k['id'] }}" 
                        wire:click="toggleKaryawan({{ $k['id'] }})" 
                        {{ in_array($k['id'], $selectedKaryawanIds) ? 'checked' : '' }}
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <div class="flex-1">
                        <span class="text-slate-800 font-bold">{{ $k['nama'] }}</span>
                        <span class="text-slate-400 font-mono text-[11px]"> — NIP: {{ $k['nip'] }} ({{ $k['jabatan'] }})</span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="ml-auto flex items-center gap-2 pt-2">
        <x-ts:button type="button" outline color="secondary" sm x-on:click="$dispatch('close-modal', {id: 'modal-add-perintah-tugas'})">
            Batal
        </x-ts:button>
        <x-ts:button type="submit" color="primary" sm icon="tabler.send" loading="submit">
            Simpan & Ajukan ke Direktur
        </x-ts:button>
    </div>
</form>
