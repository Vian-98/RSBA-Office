{{-- MODAL SIGN / APPROVE --}}
<x-ts:modal title="Konfirmasi Tanda Tangan Digital" wire="showSignModal" center size="md">
    @if ($selectedDocument)
        <div class="space-y-4 text-xs">
            <div class="bg-indigo-50 p-3.5 rounded-xl border border-indigo-100">
                <div class="font-bold text-slate-800 text-sm">{{ $selectedDocument->title }}</div>
                <div class="text-xs font-mono text-indigo-600 mt-0.5">Nomor: {{ $selectedDocument->document_number }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Pengirim: <span class="font-medium text-slate-700">{{ $selectedDocument->user?->name }}</span></div>
            </div>

            @if ($selectedDocument->revised_from_number)
                <div class="text-xs bg-amber-50 border border-amber-200 p-2.5 rounded-lg text-amber-900">
                    <div><strong>Surat Hasil Revisi</strong> (Revisi dari No: <span class="font-mono font-bold">{{ $selectedDocument->revised_from_number }}</span>)</div>
                    @if ($selectedDocument->catatan_revisi)
                        <div class="mt-1 font-mono text-[11px] text-amber-800">"{{ $selectedDocument->catatan_revisi }}"</div>
                    @endif
                </div>
            @endif

            <div class="space-y-1.5">
                <label class="font-bold text-slate-700 block">Password Akun Anda untuk Verifikasi TTD:</label>
                <input type="password" 
                       wire:model="accountPassword" 
                       wire:keydown.enter="approveDocument"
                       placeholder="Masukkan password akun Anda..." 
                       class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-xs">
                <p class="text-[10px] text-slate-400">Tanda tangan digital akan dibubuhkan secara otomatis pada dokumen PDF.</p>
            </div>

            <div class="pt-2 flex justify-end space-x-2">
                <x-ts:button color="slate" variant="outline" wire:click="$set('showSignModal', false)">Batal</x-ts:button>
                <x-ts:button color="emerald" wire:click="approveDocument">Setujui & TTD Dokumen</x-ts:button>
            </div>
        </div>
    @endif
</x-ts:modal>
