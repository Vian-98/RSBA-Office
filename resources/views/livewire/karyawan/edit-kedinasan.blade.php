<div class="w-full">
    <form wire:submit.prevent='update' class="space-y-4">

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.status" searchable placeholder="Status Pegawai" :options="$status_options" select="label:label|value:value" />
                @if ($form->status != $status_init)
                    <div class="w-full mt-1.5">
                        <x-ts:date wire:model.lazy='form.tgl_status' placeholder="Tgl Status Baru" />
                    </div>
                @endif
            </div>
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.kategori_kerja" placeholder="Kategori Kerja" :options="$kategori_options" select="label:label|value:value" />
            </div>
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.jabatan" searchable placeholder="Jabatan" :options="$jabatan_options" select="label:nama|value:id" />
            </div>
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.bagian" searchable placeholder="Bagian / Departemen" :options="$bagian_options" select="label:nama|value:id" />
            </div>
        </div>

        {{-- Conditional Block: Form Input SK & Tanggal jika Jabatan/Bagian Berubah --}}
        @if ($form->jabatan != $jabatan_init || $form->bagian != $bagian_init)
            <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl space-y-2">
                <div class="flex items-center gap-1.5 text-xs font-bold text-amber-800 uppercase tracking-wider">
                    <x-tabler-file-certificate class="w-4 h-4 text-amber-600" />
                    Kelengkapan Mutasi / SK Jabatan Baru
                </div>
                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Tanggal Mulai / Penugasan Baru <span class="text-red-500">*</span></label>
                        <x-ts:date wire:model.lazy='form.tgl_jabatan' placeholder="Pilih Tanggal Mulai" />
                        @error('form.tgl_jabatan') <span class="text-[11px] text-red-500 italic">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Nomor SK Jabatan <span class="text-red-500">*</span></label>
                        <x-ts:input wire:model.lazy='form.no_sk_jabatan' placeholder="Contoh: 045/SK-DIR/RSBA/VIII/2026" />
                        @error('form.no_sk_jabatan') <span class="text-[11px] text-red-500 italic">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Pilih Dokumen SK (Profil / Dokumen)</label>
                        <x-ts:select.styled wire:model.live='form.document_id_jabatan' placeholder="Pilih Dokumen yang Diupload" :options="$document_options" select="label:nama|value:id" searchable />
                        @error('form.document_id_jabatan') <span class="text-[11px] text-red-500 italic">{{ $message }}</span> @enderror
                    </div>
                </div>
                <p class="text-[11px] text-amber-700/90 italic">
                    💡 Dokumen SK dapat dipilih dari file yang telah diunggah di tab <b>Dokumen</b> (tersentralisasi di menu profil/dokumen karyawan).
                </p>
            </div>
        @endif

        {{-- Row Ruangan --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="w-full">
                <x-ts:select.styled wire:model.live="form.ruangan" placeholder="Ruangan Utama" :request="route('api.ruangan')" select="label:nama|value:id" />
            </div>
        </div>

        {{-- Conditional Block: Form Input SK & Tanggal jika Ruangan Berubah --}}
        @if ($form->ruangan != $ruangan_init)
            <div class="p-3.5 bg-indigo-50/70 border border-indigo-200 rounded-xl space-y-2">
                <div class="flex items-center gap-1.5 text-xs font-bold text-indigo-800 uppercase tracking-wider">
                    <x-tabler-file-certificate class="w-4 h-4 text-indigo-600" />
                    Kelengkapan Rotasi / SK Penugasan Ruangan Baru
                </div>
                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Tanggal Mulai Ruangan Baru <span class="text-red-500">*</span></label>
                        <x-ts:date wire:model.lazy='form.tgl_ruangan' placeholder="Pilih Tanggal Mulai" />
                        @error('form.tgl_ruangan') <span class="text-[11px] text-red-500 italic">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Nomor SK Ruangan <span class="text-red-500">*</span></label>
                        <x-ts:input wire:model.lazy='form.no_sk_ruangan' placeholder="Contoh: 046/SK-DIR/RSBA/VIII/2026" />
                        @error('form.no_sk_ruangan') <span class="text-[11px] text-red-500 italic">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Pilih Dokumen SK (Profil / Dokumen)</label>
                        <x-ts:select.styled wire:model.live='form.document_id_ruangan' placeholder="Pilih Dokumen yang Diupload" :options="$document_options" select="label:nama|value:id" searchable />
                        @error('form.document_id_ruangan') <span class="text-[11px] text-red-500 italic">{{ $message }}</span> @enderror
                    </div>
                </div>
                <p class="text-[11px] text-indigo-700/90 italic">
                    💡 Dokumen SK dapat dipilih dari file yang telah diunggah di tab <b>Dokumen</b> (tersentralisasi di menu profil/dokumen karyawan).
                </p>
            </div>
        @endif

        <div class="flex justify-end gap-2 pt-2">
            <x-ts:button loading="update" xs outline icon="tabler.briefcase" type="submit">Update</x-ts:button>
        </div>
    </form>

    <hr class="my-6 border-slate-200" />

    {{-- Section Riwayat Penugasan Ruangan & Jabatan --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
        
        {{-- Tabel Riwayat Penugasan Ruangan --}}
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                    🏢 Riwayat Penugasan Ruangan
                </h3>
                <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full font-semibold">
                    {{ $form->karyawan?->historyRuangan->count() ?? 0 }} Record
                </span>
            </div>

            @if($form->karyawan?->historyRuangan->isEmpty())
                <div class="text-center py-6 text-xs text-slate-400 border border-dashed border-slate-200 rounded-lg">
                    Belum ada riwayat penugasan ruangan.
                </div>
            @else
                <div class="overflow-x-auto border border-slate-100 rounded-lg">
                    <table class="w-full text-xs text-left text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2">Ruangan</th>
                                <th class="px-3 py-2">No. SK</th>
                                <th class="px-3 py-2">Periode</th>
                                <th class="px-3 py-2 text-center">Dokumen</th>
                                <th class="px-3 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($form->karyawan->historyRuangan as $histR)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-3 py-2 font-semibold text-slate-800">
                                        {{ $histR->nama }}
                                        @if($histR->pivot->is_utama)
                                            <span class="text-[10px] text-emerald-600 font-bold ml-1">(Utama)</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 font-medium text-slate-700">
                                        {{ $histR->pivot->no_sk ?: '-' }}
                                    </td>
                                    <td class="px-3 py-2 font-medium text-slate-600">
                                        {{ $histR->pivot->tgl_mulai ? date('d/m/Y', strtotime($histR->pivot->tgl_mulai)) : '-' }}
                                        <span class="text-slate-400 mx-0.5">-</span>
                                        {{ $histR->pivot->tgl_berakhir ? date('d/m/Y', strtotime($histR->pivot->tgl_berakhir)) : 'Sekarang' }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if($histR->pivot->document_id)
                                            <button type="button" wire:click="viewDocument({{ $histR->pivot->document_id }})" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                                                <x-tabler-file-text class="w-3.5 h-3.5" />
                                                Lihat SK
                                            </button>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if(is_null($histR->pivot->tgl_berakhir))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                                Riwayat
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Tabel Riwayat Jabatan --}}
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                    👔 Riwayat Mutasi Jabatan
                </h3>
                <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full font-semibold">
                    {{ $form->karyawan?->historyJabatan->count() ?? 0 }} Record
                </span>
            </div>

            @if($form->karyawan?->historyJabatan->isEmpty())
                <div class="text-center py-6 text-xs text-slate-400 border border-dashed border-slate-200 rounded-lg">
                    Belum ada riwayat mutasi jabatan.
                </div>
            @else
                <div class="overflow-x-auto border border-slate-100 rounded-lg">
                    <table class="w-full text-xs text-left text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2">Jabatan</th>
                                <th class="px-3 py-2">Bagian</th>
                                <th class="px-3 py-2">No. SK</th>
                                <th class="px-3 py-2">Periode</th>
                                <th class="px-3 py-2 text-center">Dokumen</th>
                                <th class="px-3 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($form->karyawan->historyJabatan as $histJ)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-3 py-2 font-semibold text-slate-800">
                                        {{ $histJ->nama }}
                                    </td>
                                    <td class="px-3 py-2 font-medium text-slate-600">
                                        {{ \App\Models\Sdm\Bagian::find($histJ->pivot->bagian_id ?? $histJ->bagian_id)?->nama ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 font-medium text-slate-700">
                                        {{ $histJ->pivot->no_sk ?: '-' }}
                                    </td>
                                    <td class="px-3 py-2 font-medium text-slate-600">
                                        {{ $histJ->pivot->tgl_mulai ? date('d/m/Y', strtotime($histJ->pivot->tgl_mulai)) : '-' }}
                                        <span class="text-slate-400 mx-0.5">-</span>
                                        {{ $histJ->pivot->tgl_berakhir ? date('d/m/Y', strtotime($histJ->pivot->tgl_berakhir)) : 'Sekarang' }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if($histJ->pivot->document_id)
                                            <button type="button" wire:click="viewDocument({{ $histJ->pivot->document_id }})" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                                                <x-tabler-file-text class="w-3.5 h-3.5" />
                                                Lihat SK
                                            </button>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if(is_null($histJ->pivot->tgl_berakhir))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                                Riwayat
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    {{-- Modal Preview Dokumen SK --}}
    <x-filament::modal id="view-sk-document-modal" width="5xl" class="h-screen min-h-full" :close-by-clicking-away="false" :autofocus="false">
        <x-slot name="heading">
            Pratinjau Dokumen SK: <span class="text-primary-600 font-semibold">{{ $previewDocument?->nama }}</span>
        </x-slot>
        
        <div class="flex h-[75vh] w-full flex-col">
            <div class="w-full flex-1 overflow-hidden rounded-lg border border-slate-200">
                @if($previewDocument && $previewDocument->filename)
                    <embed src="{{ asset('storage/' . $previewDocument->filename) }}" type="application/pdf" class="h-full w-full">
                @else
                    <div class="flex items-center justify-center h-full text-slate-400 text-sm">
                        File tidak ditemukan.
                    </div>
                @endif
            </div>
            <div class="ml-auto mt-3 flex justify-end gap-2">
                @if($previewDocument && $previewDocument->filename)
                    <a href="{{ asset('storage/' . $previewDocument->filename) }}" target="_blank" download class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                        <x-tabler-download class="w-4 h-4" />
                        Download File
                    </a>
                @endif
                <x-ts:button outline sm x-on:click="$dispatch('close-modal',{id:'view-sk-document-modal'})">Tutup</x-ts:button>
            </div>
        </div>
    </x-filament::modal>
</div>
