<div class="space-y-6">
    <!-- Header Navigation & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
        <div class="flex items-center gap-3">
            <x-ts:button href="{{ route('kepegawaian.surat.disposisi.index') }}" outline color="indigo" icon="chevron-left" size="sm">
                Kembali ke Daftar Disposisi
            </x-ts:button>
        </div>
        <div class="flex items-center gap-2">
            <x-ts:button wire:click="openModalPattern" x-on:click="$tsui.open('modal-pattern')" outline icon="cog" color="slate" size="sm">
                Format Agenda
            </x-ts:button>
            <x-ts:button wire:click="openModalMention" x-on:click="$tsui.open('modal-mention')" color="amber" icon="at-symbol" size="sm">
                Mention Surat Terarsip
            </x-ts:button>
        </div>
    </div>

    <!-- Mention Status Banner -->
    @if($suratMasukId)
        <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <x-ts:icon name="check-circle" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                <div>
                    <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">Surat Terarsip Di-mention: {{ $noSurat }}</h4>
                    <p class="text-xs text-amber-700 dark:text-amber-400">Data Tgl Surat, No Surat, Perihal, dan Asal Surat telah diisi otomatis.</p>
                </div>
            </div>
            <x-ts:button wire:click="clearMention" size="xs" color="amber" outline>
                Lepas Mention (Isi Manual)
            </x-ts:button>
        </div>
    @endif

    <!-- Form Container matching Physical Layout -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-300 dark:border-slate-700 p-6 shadow-sm space-y-6">
        
        <!-- Lembar Header Title -->
        <div class="text-center border-b border-slate-300 dark:border-slate-700 pb-4">
            <h2 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider">
                LEMBAR PENERUS/DISPOSISI
            </h2>
            <h3 class="text-md font-bold text-indigo-700 dark:text-indigo-400">
                RS BINTANG AMIN - DIREKTUR
            </h3>
        </div>

        <!-- Top Fields Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">No. Agenda (Auto-Increment)</label>
                <div class="flex items-center gap-2">
                    <x-ts:input wire:model="noAgenda" disabled class="bg-slate-100 dark:bg-slate-800 font-mono font-bold text-indigo-600" />
                    <x-ts:button wire:click="openModalPattern" x-on:click="$tsui.open('modal-pattern')" icon="cog" color="slate" outline size="sm" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Tgl Surat *</label>
                <x-ts:input type="date" wire:model="tglSurat" />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">No. Surat *</label>
                <x-ts:input wire:model="noSurat" placeholder="Masukkan nomor surat" />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Asal Surat *</label>
                <x-ts:input wire:model="asalSurat" placeholder="Masukkan asal instansi/pengirim" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Perihal *</label>
                <x-ts:input wire:model="perihal" placeholder="Masukkan perihal surat" />
            </div>
        </div>

        <!-- Kepada YTH Table (Master Data Jabatan & Karyawan) -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                    Kepada YTH (Daftar Penerima Disposisi & RTL)
                </h3>
                <div class="flex items-center gap-3">
                    <x-ts:button wire:click="addCustomRecipient" color="indigo" icon="plus" size="xs">
                        + Tambah Baris Penerima
                    </x-ts:button>
                </div>
            </div>
            
            <div class="border border-slate-300 dark:border-slate-700 rounded-xl overflow-hidden">
                <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200">
                    <thead class="bg-slate-100 dark:bg-slate-900 font-bold border-b border-slate-300 dark:border-slate-700 text-xs uppercase">
                        <tr>
                            <th class="py-3 px-3 w-12 text-center">No</th>
                            <th class="py-3 px-4">Kepada YTH (Jabatan / Karyawan)</th>
                            <th class="py-3 px-4 text-center w-48" colspan="3">RTL (Rencana Tindak Lanjut)</th>
                            <th class="py-3 px-4 text-center w-36">Tanda Terima</th>
                        </tr>
                        <tr class="bg-slate-50 dark:bg-slate-800 text-[10px]">
                            <th></th>
                            <th></th>
                            <th class="py-1 text-center border-l border-slate-300 dark:border-slate-700">Info</th>
                            <th class="py-1 text-center border-l border-slate-300 dark:border-slate-700">Action</th>
                            <th class="py-1 text-center border-l border-slate-300 dark:border-slate-700">Arsip</th>
                            <th class="py-1 text-center border-l border-slate-300 dark:border-slate-700">Paraf / Tgl</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($recipients as $idx => $item)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/50">
                                <td class="py-2.5 px-3 text-center font-bold text-xs">{{ $idx + 1 }}</td>
                                <td class="py-2.5 px-4 font-medium">
                                    @if(!empty($item['is_custom']))
                                        <div class="flex items-center gap-2">
                                            <select wire:model.live="recipients.{{ $idx }}.karyawan_id" class="flex-1 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-1.5 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                                                <option value="">-- Pilih Karyawan --</option>
                                                @foreach($allKaryawanList as $karyawan)
                                                    <option value="{{ $karyawan['id'] }}">{{ $karyawan['nama'] }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" wire:click="removeCustomRecipient({{ $idx }})" class="text-rose-500 hover:text-rose-700 p-1" title="Hapus Baris">
                                                <x-ts:icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    @else
                                        {{ $item['nama_jabatan'] }}
                                        @if(!empty($item['karyawan_nama']))
                                            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-normal">({{ $item['karyawan_nama'] }})</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-2.5 px-2 text-center border-l border-slate-200 dark:border-slate-700">
                                    <input type="checkbox" wire:model="recipients.{{ $idx }}.is_info" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer" />
                                </td>
                                <td class="py-2.5 px-2 text-center border-l border-slate-200 dark:border-slate-700">
                                    <input type="checkbox" wire:model="recipients.{{ $idx }}.is_action" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer" />
                                </td>
                                <td class="py-2.5 px-2 text-center border-l border-slate-200 dark:border-slate-700">
                                    <input type="checkbox" wire:model="recipients.{{ $idx }}.is_arsip" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer" />
                                </td>
                                <td class="py-2.5 px-3 text-center text-xs text-slate-400 border-l border-slate-200 dark:border-slate-700">
                                    -
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Catatan Direktur Section -->
        <div>
            <label class="block text-xs font-bold uppercase text-slate-700 dark:text-slate-300 mb-1">
                Catatan / Instruksi Direktur *
            </label>
            <textarea wire:model="catatan" rows="4" class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 text-sm focus:ring-indigo-500 focus:border-indigo-500 p-3" placeholder="Tuliskan catatan atau instruksi disposisi dari Direktur..."></textarea>
        </div>

        <!-- Footer Konfirmasi Penerimaan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Diterima Oleh</label>
                <x-ts:input wire:model="diterimaOleh" placeholder="Nama penerima fisik/staf" />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Tanggal Diterima</label>
                <x-ts:input type="date" wire:model="tglDiterima" />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Pukul / Jam</label>
                <x-ts:input type="time" wire:model="jamDiterima" />
            </div>
        </div>

        <!-- Submit Button Section -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
            <x-ts:button href="{{ route('kepegawaian.surat.disposisi.index') }}" color="slate" outline size="sm">
                Batal
            </x-ts:button>
            <x-ts:button wire:click="submit" color="indigo" icon="check-badge" class="px-6">
                Assign & Tanda Tangan Digital Direktur
            </x-ts:button>
        </div>
    </div>

    <!-- Modal Mention Surat Arsip -->
    <x-ts:modal wire="modalMention" id="modal-mention" title="Pilih Surat Masuk Terarsip (Mention)" center blur>
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <x-ts:input wire:model="searchArsip" placeholder="Cari nomor surat / perihal..." class="flex-1" />
                <x-ts:button wire:click="searchArsipLetters" color="indigo" icon="magnifying-glass">Cari</x-ts:button>
            </div>

            @if($loadingArsip)
                <div class="text-center py-8 text-slate-500">Memuat data arsip...</div>
            @else
                <div class="max-h-60 overflow-y-auto divide-y divide-slate-200 dark:divide-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl">
                    @forelse($arsipResults as $arsip)
                        <div wire:click="selectArsipSurat({{ json_encode($arsip) }})" class="p-3 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 cursor-pointer transition flex items-center justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-sm text-indigo-700 dark:text-indigo-400 font-mono truncate">
                                    {{ $arsip['no_surat'] }}
                                </div>
                                <div class="text-xs text-slate-700 dark:text-slate-200 font-medium truncate mt-0.5">
                                    {{ $arsip['perihal'] }}
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    Asal: <strong class="text-slate-600 dark:text-slate-300">{{ $arsip['asal_surat'] }}</strong> | Tgl: {{ $arsip['tgl_surat'] }}
                                </div>
                            </div>
                            <x-ts:button size="xs" color="indigo" outline class="shrink-0">Pilih</x-ts:button>
                        </div>
                    @empty
                        <div class="p-4 text-center text-xs text-slate-400">
                            Tidak ditemukan surat terarsip yang cocok.
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
        <x-slot name="footer">
            <x-ts:button wire:click="$set('modalMention', false)" color="slate" outline>Tutup</x-ts:button>
        </x-slot>
    </x-ts:modal>

    <!-- Modal Pattern Agenda -->
    <x-ts:modal wire="modalPattern" id="modal-pattern" title="Format Template No. Agenda" center blur>
        <div class="space-y-4">
            <p class="text-xs text-slate-600 dark:text-slate-300">
                Ubah format template penomoran agenda dinamis.
            </p>
            <x-ts:input wire:model="patternInput" label="Pattern Template" placeholder="Contoh: AG/{YYYY}/{NUMBER:4}" />
        </div>
        <x-slot name="footer">
            <x-ts:button wire:click="$set('modalPattern', false)" color="slate" outline>Batal</x-ts:button>
            <x-ts:button wire:click="savePattern" color="indigo">Simpan</x-ts:button>
        </x-slot>
    </x-ts:modal>
</div>
