{{-- SIDE KIRI: Form Pengisian Identitas & Metadata (Width: 38%) --}}
<div style="flex: 0 0 38%; width: 38%; min-width: 320px;" class="space-y-5">
    <div class="border-b border-slate-100 pb-3">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Identitas & Informasi Surat</h3>
        <p class="text-xs text-slate-400">Lengkapi metadata sebelum penandatanganan</p>
    </div>

    {{-- Stamp Resizer & Opacity Controller in Sidebar --}}
    <div class="bg-indigo-50/60 p-4 rounded-2xl border border-indigo-100 space-y-4">
        {{-- Scale Slider --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-indigo-900 uppercase tracking-wider">
                <span>Ukuran Stempel (Scale)</span>
                <span class="font-mono text-indigo-700 font-bold" x-text="scale + '%'"></span>
            </div>
            <div class="flex items-center space-x-3">
                <input type="range" min="50" max="180" step="5" x-model="scale" class="w-full accent-indigo-600 h-2 bg-indigo-200/80 rounded-lg cursor-pointer" />
            </div>
            <div class="flex justify-between gap-1 text-[11px]">
                <button type="button" @click="scale = 65" class="px-2 py-1 bg-white border border-indigo-200 rounded-lg text-slate-700 hover:text-indigo-600 font-semibold transition-colors">Kecil (65%)</button>
                <button type="button" @click="scale = 100" class="px-2 py-1 bg-white border border-indigo-200 rounded-lg text-slate-700 hover:text-indigo-600 font-semibold transition-colors">Normal (100%)</button>
                <button type="button" @click="scale = 135" class="px-2 py-1 bg-white border border-indigo-200 rounded-lg text-slate-700 hover:text-indigo-600 font-semibold transition-colors">Besar (135%)</button>
            </div>
        </div>

        {{-- Transparency / Opacity Slider --}}
        <div class="space-y-2 pt-3 border-t border-indigo-200/60">
            <div class="flex items-center justify-between text-xs font-bold text-indigo-900 uppercase tracking-wider">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Transparansi Stempel
                </span>
                <span class="font-mono text-indigo-700 font-bold" x-text="opacity + '%'"></span>
            </div>
            <div class="flex items-center space-x-3">
                <input type="range" min="20" max="100" step="5" x-model="opacity" class="w-full accent-indigo-600 h-2 bg-indigo-200/80 rounded-lg cursor-pointer" />
            </div>
            <div class="flex justify-between gap-1 text-[11px]">
                <button type="button" @click="opacity = 50" class="px-2 py-1 bg-white border border-indigo-200 rounded-lg text-slate-700 hover:text-indigo-600 font-semibold transition-colors">Transparan (50%)</button>
                <button type="button" @click="opacity = 80" class="px-2 py-1 bg-white border border-indigo-200 rounded-lg text-slate-700 hover:text-indigo-600 font-semibold transition-colors">Semi (80%)</button>
                <button type="button" @click="opacity = 100" class="px-2 py-1 bg-white border border-indigo-200 rounded-lg text-slate-700 hover:text-indigo-600 font-semibold transition-colors">Solid (100%)</button>
            </div>
            <p class="text-[10px] text-slate-500 mt-1 italic">
                💡 Turunkan transparansi jika stempel menutupi teks/tabel pada lembar surat agar tulisan di bawahnya tetap terbaca.
            </p>
        </div>
    </div>

    {{-- Revision Banner (If revising a rejected document) --}}
    @if ($revises_document_id && $revised_from_number)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-2xl space-y-2 text-xs">
            <div class="flex items-center justify-between">
                <span class="font-bold text-amber-900 flex items-center gap-1.5">
                    Mode Revisi Surat Ditolak
                </span>
                <button type="button" wire:click="cancelRevision" class="text-[11px] text-amber-700 hover:text-amber-900 font-bold underline">
                    Batal Revisi
                </button>
            </div>
            <div class="text-amber-800 space-y-1">
                <div><strong>Revisi dari Surat No:</strong> <span class="font-mono font-bold">{{ $revised_from_number }}</span></div>
                <div><strong>Nomor Surat Baru:</strong> <span class="font-mono font-bold text-indigo-700">{{ $document_number }}</span> <span class="text-[10px] text-amber-700">(Nomor baru wajib beda dari awal)</span></div>
                @if ($originalDocument && $originalDocument->approvals->where('status', 'rejected')->first())
                    <div class="bg-amber-100/80 p-2 rounded-lg text-amber-900 font-mono text-[11px] mt-1">
                        <strong>Alasan Penolakan Sebelumnya:</strong><br>
                        "{{ $originalDocument->approvals->where('status', 'rejected')->first()?->rejection_reason }}"
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Judul Surat --}}
    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Judul / Nama Surat</label>
        <input type="text" wire:model="title" placeholder="Judul Surat..." class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white" />
        @error('title') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
    </div>

    {{-- Nomor Surat --}}
    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Nomor Surat 
            @if ($revises_document_id)
                <span class="text-[10px] font-normal text-indigo-600">(Nomor Surat Baru)</span>
            @endif
        </label>
        <input type="text" wire:model="document_number" placeholder="Nomor Surat..." class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono text-slate-800 bg-white" />
        @error('document_number') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
    </div>

    {{-- Jenis / Kategori Arsip Surat --}}
    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Jenis / Kategori Arsip Surat <span class="text-rose-500">*</span>
        </label>
        <select 
            wire:model="document_type" 
            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white cursor-pointer font-medium"
        >
            @if(isset($kategoriList) && count($kategoriList) > 0)
                @foreach($kategoriList as $cat)
                    <option value="{{ $cat->kode }}">
                        {{ $cat->nama }}
                    </option>
                @endforeach
            @else
                <option value="file_text">📄 File Text / Umum</option>
            @endif
        </select>
        <span class="text-[11px] text-slate-400 mt-1 block">Pilih kategori agar terarsip otomatis secara terdata di Bank Surat.</span>
        @error('document_type') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
    </div>

    {{-- Keterangan --}}
    <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Perihal (Opsional)</label>
        <textarea wire:model="keterangan" rows="2" placeholder="Catatan perihal surat..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-white"></textarea>
    </div>

    {{-- Catatan Revisi / Perbaikan --}}
    @if ($revises_document_id)
        <div>
            <label class="block text-xs font-bold text-indigo-700 uppercase tracking-wider mb-1.5">
                Catatan Revisi / Perbaikan <span class="text-rose-500">*</span>
            </label>
            <textarea wire:model="catatan_revisi" rows="2" placeholder="Jelaskan perbaikan yang dilakukan pada berkas revisi ini..." class="w-full px-4 py-2.5 rounded-xl border border-indigo-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 bg-indigo-50/30"></textarea>
            <span class="text-[11px] text-slate-400 mt-1 block">Catatan ini dapat dilihat oleh pejabat penandatangan saat meninjau revisi.</span>
        </div>
    @endif

    {{-- Assign Penandatangan Bertingkat (Multi-Tier Signing Chain) --}}
    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-3">
        <div class="flex items-center justify-between">
            <label class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                Penandatangan Bertingkat (Multi-Tier)
            </label>
            <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                {{ count($signer_ids) }} Tingkat
            </span>
        </div>
        <p class="text-[11px] text-slate-500 leading-relaxed">
            Tentukan daftar pejabat yang akan menyetujui surat secara berjenjang. Stempel QR Code pada fisik dokumen akan menampilkan identitas <strong>pejabat dengan tingkat/jabatan tertinggi</strong>.
        </p>

        {{-- Selected Signers List --}}
        <div class="space-y-2">
            @foreach($signer_ids as $idx => $sId)
                @php
                    $u = isset($allUsers) ? $allUsers->firstWhere('id', $sId) : null;
                    $jabatanNama = $u?->karyawan?->jabatan?->first()?->nama ?? 'Pegawai';
                @endphp
                <div class="flex items-center justify-between bg-white p-2.5 rounded-xl border border-slate-200 shadow-sm text-xs">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-6 h-6 bg-indigo-600 text-white font-bold rounded-full flex items-center justify-center text-[10px] shrink-0">
                            T{{ $idx + 1 }}
                        </span>
                        <div>
                            <div class="font-bold text-slate-800">{{ $u?->name ?? 'User #' . $sId }}</div>
                            <div class="text-[10px] text-slate-500">{{ $jabatanNama }}</div>
                        </div>
                    </div>
                    @if(count($signer_ids) > 1)
                        <button type="button" wire:click="removeSignerUser({{ $idx }})" class="text-rose-500 hover:text-rose-700 font-bold px-2 py-1">
                            ✕
                        </button>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Add Signer Dropdown --}}
        <div class="flex items-center space-x-2 pt-2 border-t border-slate-200">
            <select wire:model="selected_add_user_id" class="flex-1 px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500">
                <option value="0">+ Tambah Penandatangan Tingkat Berikutnya...</option>
                @if(isset($allUsers))
                    @foreach($allUsers as $userOpt)
                        @if(!in_array($userOpt->id, $signer_ids))
                            <option value="{{ $userOpt->id }}">
                                {{ $userOpt->name }} ({{ $userOpt->karyawan?->jabatan?->first()?->nama ?? 'Pegawai' }})
                            </option>
                        @endif
                    @endforeach
                @endif
            </select>
            <button type="button" wire:click="addSignerUser" class="px-3 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition-colors">
                Tambah
            </button>
        </div>
    </div>

    {{-- Submit Button --}}
    <div class="pt-4 w-full">
        <button 
            type="submit" 
            wire:loading.attr="disabled"
            wire:target="openPasswordModal"
            class="w-full py-3.5 px-6 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-xl shadow-sm transition-all flex items-center justify-center space-x-2 text-base cursor-pointer disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="openPasswordModal">Tanda Tangan</span>
            <span wire:loading.flex wire:target="openPasswordModal" class="inline-flex items-center justify-center gap-2 whitespace-nowrap">
                <svg class="animate-spin h-5 w-5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Menyiapkan Modal Konfirmasi...</span>
            </span>
        </button>
    </div>
</div>
