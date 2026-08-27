<div class="flex flex-col gap-4">
    @if($kuitansi)
        {{-- Ringkasan Dokumen --}}
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-xs">
            <div class="grid grid-cols-2 gap-2 mb-2">
                <div>
                    <span class="text-slate-500 font-medium">Nomor:</span>
                    <span class="font-bold text-slate-800">{{ $kuitansi->nomor }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium">Tanggal:</span>
                    <span class="font-bold text-slate-800">{{ $kuitansi->tanggal ? $kuitansi->tanggal->format('d M Y') : '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium">Diterima Dari:</span>
                    <span class="font-bold text-slate-800">{{ $kuitansi->diterima_dari ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium">Jumlah:</span>
                    <span class="font-bold text-emerald-600 font-mono text-sm">{{ formatRupiah($kuitansi->jumlah) }}</span>
                </div>
            </div>
            <div>
                <span class="text-slate-500 font-medium">Untuk Pembayaran:</span>
                <p class="text-slate-700 italic mt-0.5">{{ $kuitansi->keterangan }}</p>
            </div>
        </div>

        {{-- Form Approval --}}
        <form wire:submit.prevent="submit" class="flex flex-col gap-4">
            {{-- Pilihan Status Approval --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Keputusan Persetujuan *</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    @foreach($optionsApproval as $opt)
                        <label class="flex items-center gap-2 rounded-lg border p-3 cursor-pointer transition {{ $status === $opt['value'] ? 'border-indigo-500 bg-indigo-50/50 ring-1 ring-indigo-400' : 'border-slate-200 hover:border-slate-300' }}">
                            <input type="radio" wire:model.live="status" value="{{ $opt['value'] }}" class="text-indigo-600 focus:ring-indigo-500" />
                            <span class="text-xs font-semibold {{ $opt['color'] === 'green' ? 'text-emerald-700' : ($opt['color'] === 'red' ? 'text-red-700' : 'text-indigo-700') }}">
                                {{ $opt['label'] }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('status') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            {{-- Password PKCS#12 Digital Signature (jika setujui) --}}
            @if($status === 'approved')
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3">
                    <x-ts:password label="Password Sertifikat Digital (P12)" wire:model.defer="password" placeholder="Masukkan password sertifikat p12..." hint="Default: password123 jika belum diubah" />
                </div>
            @endif

            {{-- Keterangan / Alasan Tolak --}}
            <div>
                <x-ts:textarea label="Catatan / Keterangan {{ $status === 'rejected' ? '(Wajib Diisi)' : '(Opsional)' }}" wire:model.defer="keterangan" rows="2" placeholder="{{ $status === 'rejected' ? 'Jelaskan alasan penolakan...' : 'Catatan tambahan...' }}" />
                @error('keterangan') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <x-ts:button type="button" outline color="secondary" x-on:click="$dispatch('close-modal',{id:'modal-approval-kuitansi'})">
                    Tutup
                </x-ts:button>
                <x-ts:button type="submit" color="primary" loading="submit">
                    Kirim Persetujuan
                </x-ts:button>
            </div>
        </form>
    @else
        <div class="p-4 text-center text-slate-500 text-sm">
            Memuat data kuitansi...
        </div>
    @endif
</div>
