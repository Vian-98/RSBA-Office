<form wire:submit.prevent="submit" class="flex flex-col gap-4">
    {{-- Baris 1: Tanggal & Kampus --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <x-ts:input label="Tanggal Surat *" type="date" wire:model="tgl" />
        <x-ts:input label="Universitas / Perguruan Tinggi *" placeholder="contoh: Universitas Malahayati" wire:model="tujuan_universitas" />
    </div>

    {{-- Baris 2: Fakultas & Penerima --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <x-ts:input label="Fakultas *" placeholder="contoh: Fakultas Kedokteran" wire:model="tujuan_fakultas" />
        <x-ts:input label="Kepada Yth. (Dekan / Pimpinan)" placeholder="contoh: Dekan Fakultas Kedokteran" wire:model="tujuan_nama" />
    </div>

    {{-- Baris 3: Surat Masuk dari Kampus --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 rounded-lg bg-slate-50 p-3 border border-slate-200">
        <x-ts:input label="Nomor Surat Masuk" placeholder="contoh: 456/UNIMAL/VIII/2026" wire:model="nomor_surat_masuk" />
        <x-ts:input label="Tanggal Surat Masuk" type="date" wire:model="tgl_surat_masuk" />
        <x-ts:input label="Perihal Surat Masuk *" placeholder="contoh: Izin Penelitian Skripsi" wire:model="perihal_surat_masuk" />
    </div>

    {{-- Baris 4: Pilihan Direktur (Jika > 1) --}}
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

    {{-- Baris 5: Daftar Identitas Mahasiswa Peneliti --}}
    <div class="rounded-xl border border-slate-200 bg-white p-3.5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Identitas Mahasiswa Peneliti ({{ count($mahasiswaList) }} Orang)</span>
            <x-ts:button type="button" xs outline color="primary" icon="tabler.plus" wire:click="addMahasiswa">
                Tambah Mahasiswa
            </x-ts:button>
        </div>

        <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
            @foreach($mahasiswaList as $index => $mhs)
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 relative text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-indigo-700">Mahasiswa #{{ $index + 1 }}</span>
                        @if(count($mahasiswaList) > 1)
                            <button type="button" wire:click="removeMahasiswa({{ $index }})" class="text-rose-500 hover:text-rose-700">
                                <x-tabler-trash class="size-4" />
                            </button>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <x-ts:input placeholder="Nama Lengkap *" wire:model="mahasiswaList.{{ $index }}.nama" />
                        <x-ts:input placeholder="NPM / NIM" wire:model="mahasiswaList.{{ $index }}.npm" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <x-ts:input placeholder="Fakultas / Perguruan Tinggi" wire:model="mahasiswaList.{{ $index }}.fakultas_pt" />
                        <x-ts:input placeholder="Judul / Topik Penelitian" wire:model="mahasiswaList.{{ $index }}.judul_penelitian" />
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Baris 6: Rincian Biaya Penelitian & Pendidikan (Lampiran) --}}
    <div class="rounded-xl border border-teal-200 bg-teal-50/40 p-3.5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-teal-900 flex items-center gap-1.5">
                <x-tabler-file-dollar class="size-4 text-teal-600" />
                Rincian Biaya Penelitian & Pendidikan (Lampiran Surat)
            </span>
            <x-ts:button type="button" xs outline color="teal" icon="tabler.plus" wire:click="addBiaya">
                Tambah Item Biaya
            </x-ts:button>
        </div>

        <div class="space-y-3">
            @foreach($biayaList as $bIndex => $biaya)
                @php
                    $jmlOrg = (int)($biaya['jumlah_orang'] ?? 1);
                    $sarana = (double)($biaya['jasa_sarana'] ?? 0);
                    $pelayanan = (double)($biaya['jasa_pelayanan'] ?? 0);
                    $subtotal = ($sarana + $pelayanan) * $jmlOrg;
                @endphp
                <div class="bg-white p-3 rounded-lg border border-teal-100 shadow-2xs space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-teal-800 flex items-center gap-1">
                            Item Biaya #{{ $bIndex + 1 }}
                        </span>
                        @if(count($biayaList) > 1)
                            <button type="button" wire:click="removeBiaya({{ $bIndex }})" class="p-1 text-rose-500 hover:bg-rose-50 rounded" title="Hapus Item Biaya">
                                <x-tabler-trash class="size-4" />
                            </button>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-5">
                            <x-ts:input label="Keterangan / Nama Biaya *" placeholder="contoh: Penelitian Skripsi / Tugas Akhir" wire:model="biayaList.{{ $bIndex }}.keterangan" />
                        </div>
                        <div class="md:col-span-2">
                            <x-ts:input label="Jml Peneliti *" type="number" min="1" wire:model.live="biayaList.{{ $bIndex }}.jumlah_orang" suffix="Org" />
                        </div>
                        <div class="md:col-span-2">
                            <x-ts:input label="Jasa Sarana (Rp)" type="number" min="0" wire:model.live="biayaList.{{ $bIndex }}.jasa_sarana" prefix="Rp" />
                        </div>
                        <div class="md:col-span-3">
                            <x-ts:input label="Jasa Pelayanan (Rp)" type="number" min="0" wire:model.live="biayaList.{{ $bIndex }}.jasa_pelayanan" prefix="Rp" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1 text-xs text-slate-500 border-t border-slate-100">
                        <span>Subtotal ({{ $jmlOrg }} Orang x Rp {{ number_format($sarana + $pelayanan, 0, ',', '.') }}):</span>
                        <span class="font-bold text-teal-700 font-mono">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3 pt-2.5 border-t border-teal-200/80 flex items-center justify-between text-xs font-bold text-teal-900">
            <span>Total Estimasi Biaya Penelitian:</span>
            <span class="text-teal-800 text-sm font-mono font-bold">
                Rp {{ number_format($this->totalEstimasi, 0, ',', '.') }}
            </span>
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="ml-auto flex items-center gap-2 pt-2">
        <x-ts:button type="button" outline color="secondary" sm x-on:click="$dispatch('close-modal', {id: 'modal-add-balasan-penelitian'})">
            Batal
        </x-ts:button>
        <x-ts:button type="submit" color="primary" sm icon="tabler.send" loading="submit">
            Simpan & Ajukan ke Direktur
        </x-ts:button>
    </div>
</form>
