<form wire:submit.prevent="submit" class="flex flex-col gap-4">
    @if($suratBalasanPkl)
        <div class="rounded-lg bg-slate-50 p-3 border border-slate-200 text-xs">
            <span class="font-bold text-slate-700 block">{{ $suratBalasanPkl->no }}</span>
            <span class="text-slate-500 block">{{ $suratBalasanPkl->tujuan_universitas }} — Prodi {{ $suratBalasanPkl->prodi }}</span>
            <span class="text-slate-600 font-medium block mt-1">Total: Rp {{ number_format($suratBalasanPkl->grand_total_biaya, 0, ',', '.') }} ({{ $suratBalasanPkl->jumlah_mahasiswa }} Mhs)</span>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Keputusan Persetujuan</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($optionsApproval as $item)
                    <button type="button" wire:click="$set('status', '{{ $item['value'] }}')" class="flex items-center justify-center gap-2 p-2.5 rounded-lg border text-xs font-bold transition {{ $status === $item['value'] ? ($item['value'] === 'approved' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-rose-600 text-white border-rose-600') : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                        @if($item['value'] === 'approved')
                            <x-tabler-checks class="size-4" />
                        @else
                            <x-tabler-x class="size-4" />
                        @endif
                        {{ $item['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        <div x-show="$wire.status === 'rejected'">
            <x-ts:textarea wire:model="catatan" label="Catatan Penolakan *" placeholder="Tuliskan alasan penolakan..." />
        </div>

        <div class="ml-auto flex items-center gap-2 pt-2">
            <x-ts:button type="button" outline color="secondary" sm x-on:click="$dispatch('close-modal', {id: 'modal-approval-balasan-pkl'})">
                Batal
            </x-ts:button>
            <x-ts:button type="submit" color="{{ $status === 'approved' ? 'emerald' : 'rose' }}" sm icon="tabler.checks" loading="submit">
                Simpan Keputusan
            </x-ts:button>
        </div>
    @endif
</form>
