<!-- Modal Konfigurasi Pengiriman Otomatis Slip Gaji -->
<x-ts:modal wire="isAutoSendModalOpen" title="Konfigurasi Pengiriman Otomatis Slip Gaji (Email)" size="lg" class="relative z-50">
    <div class="space-y-5">
        <div class="p-4 bg-purple-50 border border-purple-100 rounded-2xl flex items-start gap-3">
            <div class="p-2 bg-purple-500 text-white rounded-xl shrink-0">
                <x-tabler-clock-play class="h-5 w-5" />
            </div>
            <div class="text-xs text-purple-900 space-y-1">
                <span class="font-bold block text-sm">Otomatisasi Email Slip Gaji Bulanan</span>
                <p class="text-purple-700">
                    Sistem akan menjalankan job background setiap bulan sesuai jadwal di bawah. Slip gaji hanya akan dikirimkan untuk periode yang sudah <b>Disetujui & Dikunci (`locked`)</b>.
                </p>
            </div>
        </div>

        <!-- Toggle Status Aktif -->
        <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-xl">
            <div>
                <span class="text-sm font-bold text-slate-800 block">Status Pengiriman Otomatis</span>
                <span class="text-xs text-slate-500">Aktifkan untuk mengizinkan sistem mengirim email slip otomatis setiap bulan</span>
            </div>
            <x-ts:toggle wire:model="autoSendEnabled" color="purple" />
        </div>

        <!-- Form Tanggal & Jam Pengiriman -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengiriman Bulanan</label>
                <select wire:model="autoSendDay" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-purple-500 focus:ring-purple-500">
                    @for($d = 1; $d <= 31; $d++)
                        <option value="{{ $d }}">Setiap Tanggal {{ $d }}</option>
                    @endfor
                    <option value="last_day">Hari Terakhir Bulan (Last Day of Month)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Jam Eksekusi Pengiriman</label>
                <input type="text" wire:model="autoSendTime" placeholder="08:00" class="w-full rounded-lg border-gray-300 text-sm font-mono shadow-2xs focus:border-purple-500 focus:ring-purple-500" />
                <span class="text-[10px] text-slate-400 block mt-0.5">Format 24 jam (misal: 08:00, 14:30)</span>
            </div>
        </div>

        <!-- Form Stabilitas Batching (Chunking) -->
        <div class="p-4 bg-slate-50/70 border border-slate-200 rounded-xl space-y-3">
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Pengaturan Stabilitas SMTP & Server</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Jumlah Email per Batch (Chunk Size)</label>
                    <x-ts:input wire:model="autoSendChunkSize" type="number" min="1" max="50" class="text-xs" placeholder="Default: 10" />
                    <span class="text-[10px] text-slate-400 block mt-0.5">Disarankan 10–20 email per batch</span>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Jeda Delay Antar Batch (Detik)</label>
                    <x-ts:input wire:model="autoSendDelaySeconds" type="number" min="1" max="60" class="text-xs" placeholder="Default: 3" />
                    <span class="text-[10px] text-slate-400 block mt-0.5">Mencegah server SMTP menganggap pesan sebagai SPAM</span>
                </div>
            </div>
        </div>

        <!-- Ringkasan Log Eksekusi Terakhir -->
        @if($autoSendLastRun)
            <div class="p-3.5 bg-slate-100 border border-slate-200 rounded-xl text-xs space-y-1">
                <span class="font-bold text-slate-700 block">Riwayat Eksekusi Otomatis Terakhir:</span>
                <div class="flex items-center justify-between text-slate-600">
                    <span>Waktu: {{ \Carbon\Carbon::parse($autoSendLastRun['executed_at'] ?? now())->translatedFormat('d F Y - H:i:s') }}</span>
                    <span>Periode: {{ $autoSendLastRun['periode'] ?? '-' }}</span>
                </div>
                <div class="flex items-center gap-3 text-slate-700 font-semibold pt-1">
                    <span class="text-emerald-600">Terkirim: {{ $autoSendLastRun['sent'] ?? 0 }}</span>
                    <span class="text-rose-600">Gagal: {{ $autoSendLastRun['failed'] ?? 0 }}</span>
                    <span class="text-slate-500">Dilewati: {{ $autoSendLastRun['skipped'] ?? 0 }}</span>
                </div>
            </div>
        @endif
    </div>

    <x-slot:footer>
        <div class="flex justify-end gap-2">
            <x-ts:button size="sm" flat color="slate" wire:click="closeAutoSendModal">Batal</x-ts:button>
            <x-ts:button size="sm" color="purple" wire:click="saveAutoSendSettings" loading="saveAutoSendSettings">
                <x-tabler-device-floppy class="h-4 w-4 mr-1" />
                Simpan Pengaturan
            </x-ts:button>
        </div>
    </x-slot:footer>
</x-ts:modal>
