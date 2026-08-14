<div class="flex flex-col gap-4">
    @if($suratSp3)
        {{-- Detail Ringkas SP3 --}}
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-xs">
            <div class="grid grid-cols-2 gap-2 mb-2">
                <div>
                    <span class="text-slate-500 font-medium">Nomor SP3:</span>
                    <span class="font-bold text-slate-800">{{ $suratSp3->no }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium">Tanggal:</span>
                    <span class="font-bold text-slate-800">{{ $suratSp3->tgl }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium">Rekanan / Vendor:</span>
                    <span class="font-bold text-slate-800">{{ $suratSp3->rekanan }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium">Metode Pembayaran:</span>
                    <span class="font-bold text-slate-800">{{ $suratSp3->method_bayar }}</span>
                </div>
            </div>

            <div class="mb-2">
                <span class="text-slate-500 font-medium">Perihal / Keterangan:</span>
                <p class="text-slate-700 italic mt-0.5">{{ $suratSp3->keterangan }}</p>
            </div>

            {{-- Itemized Details --}}
            @if($suratSp3->details && $suratSp3->details->count() > 0)
                <div class="border-t border-slate-200 pt-2 mt-2">
                    <span class="text-slate-500 font-semibold mb-1 block">Rincian Pembayaran:</span>
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-500 border-b">
                                <th class="pb-1">Keterangan</th>
                                <th class="pb-1 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suratSp3->details as $det)
                                <tr class="border-b border-slate-100">
                                    <td class="py-1">{{ $det->keterangan }}</td>
                                    <td class="py-1 text-right font-mono font-semibold">{{ formatRupiah($det->nominal) }}</td>
                                </tr>
                            @endforeach
                            <tr class="font-bold">
                                <td class="pt-1">Total</td>
                                <td class="pt-1 text-right font-mono text-emerald-600 font-bold">{{ formatRupiah($suratSp3->details->sum('nominal')) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Form Verifikasi --}}
        <form wire:submit.prevent="submit" class="flex flex-col gap-4">
            {{-- Pilihan Keputusan --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Hasil Verifikasi Keuangan *</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($optionsVerifikasi as $opt)
                        <label class="flex items-center gap-2 rounded-lg border p-3 cursor-pointer transition {{ $status === $opt['value'] ? 'border-indigo-500 bg-indigo-50/50 ring-1 ring-indigo-400' : 'border-slate-200 hover:border-slate-300' }}">
                            <input type="radio" wire:model.live="status" value="{{ $opt['value'] }}" class="text-indigo-600 focus:ring-indigo-500" />
                            <span class="text-xs font-semibold {{ $opt['color'] === 'green' ? 'text-emerald-700' : 'text-red-700' }}">
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

            {{-- Catatan / Keterangan --}}
            <div>
                <x-ts:textarea label="Catatan Verifikasi {{ $status === 'rejected' ? '(Wajib Diisi)' : '(Opsional)' }}" wire:model.defer="keterangan" rows="2" placeholder="{{ $status === 'rejected' ? 'Jelaskan alasan penolakan / koreksi yang dibutuhkan...' : 'Catatan hasil verifikasi data keuangan...' }}" />
                @error('keterangan') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                <x-ts:button type="button" outline color="secondary" x-on:click="$dispatch('close-modal',{id:'modal-verifikasi-keuangan-sp3'})">
                    Tutup
                </x-ts:button>
                <x-ts:button type="submit" color="primary" loading="submit">
                    Simpan Keputusan Verifikasi
                </x-ts:button>
            </div>
        </form>
    @else
        <div class="p-4 text-center text-slate-500 text-sm">
            Memuat data SP3...
        </div>
    @endif
</div>
