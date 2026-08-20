{{-- MODAL TAMBAH JENIS ARSIP BARU --}}
<x-ts:modal wire="modalKategori" title="Tambah Jenis / Kategori Arsip Surat" center blur>
    <form wire:submit.prevent="simpanKategori" class="space-y-4 text-xs">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Jenis / Kategori Surat <span class="text-rose-500">*</span></label>
            <input 
                wire:model.live="newNama" 
                type="text" 
                placeholder="Contoh: Surat Keputusan Direktur, Memo Internal" 
                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
            />
            @error('newNama') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Kode Unik Kategori <span class="text-rose-500">*</span></label>
            <input 
                wire:model="newKode" 
                type="text" 
                placeholder="sk_direktur" 
                class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
            />
            <span class="text-[10px] text-slate-400 block mt-0.5">Gunakan huruf kecil dan garis bawah (contoh: <code>kuitansi</code>, <code>memo_internal</code>).</span>
            @error('newKode') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Icon Kategori</label>
            <select wire:model="newIcon" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <option value="file-text">📄 File Text (Default)</option>
                <option value="school">🎓 Kampus / Akademik</option>
                <option value="microscope">🔬 Penelitian / Lab</option>
                <option value="clipboard-list">📋 Checklist / Perintah Tugas</option>
                <option value="file-alert">⚠️ Peringatan / SP3</option>
                <option value="receipt-2">🧾 Kuitansi / Keuangan</option>
                <option value="briefcase">💼 Kepegawaian / SDM</option>
            </select>
        </div>

        <div>
            <label class="block font-bold text-slate-700 mb-1">Deskripsi Kategori</label>
            <textarea 
                wire:model="newDeskripsi" 
                rows="2" 
                placeholder="Penjelasan singkat mengenai kategori arsip ini..." 
                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
            ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
            <x-ts:button type="button" wire:click="$set('modalKategori', false)" color="slate" variant="flat" size="sm">
                Batal
            </x-ts:button>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-xs transition-colors cursor-pointer">
                Simpan Kategori Baru
            </button>
        </div>
    </form>
</x-ts:modal>
