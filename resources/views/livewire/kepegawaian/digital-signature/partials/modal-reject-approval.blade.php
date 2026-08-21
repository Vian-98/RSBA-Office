{{-- MODAL REJECT / TOLAK --}}
<x-ts:modal title="Konfirmasi Penolakan Surat" wire="showRejectModal" center size="md">
    @if ($selectedDocument)
        <div class="space-y-4 text-xs">
            <div class="bg-rose-50 p-3.5 rounded-xl border border-rose-100">
                <div class="font-bold text-slate-800 text-sm">{{ $selectedDocument->title }}</div>
                <div class="text-xs font-mono text-rose-600 mt-0.5">Nomor: {{ $selectedDocument->document_number }}</div>
            </div>

            <div class="space-y-1.5">
                <label class="font-bold text-rose-900 block">Alasan / Catatan Penolakan (Wajib Diisi):</label>
                <textarea wire:model="rejectionReason" 
                          rows="3" 
                          placeholder="Tuliskan catatan perbaikan atau alasan penolakan..." 
                          class="w-full p-2.5 border border-rose-300 rounded-xl focus:ring-2 focus:ring-rose-500 text-xs"></textarea>
            </div>

            <div class="pt-2 flex justify-end space-x-2">
                <x-ts:button color="slate" variant="outline" wire:click="$set('showRejectModal', false)">Batal</x-ts:button>
                <x-ts:button color="rose" wire:click="rejectDocument">Tolak Pengajuan Surat</x-ts:button>
            </div>
        </div>
    @endif
</x-ts:modal>
