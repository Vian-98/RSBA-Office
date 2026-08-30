<div class="flex flex-col gap-2">
    <div class="flex flex-col gap-2 lg:grid lg:grid-cols-4">
        <div class="col-span-2 w-full">
            <livewire:Asset.Title :assetBarang="$assetBarang" :key="'asset-title-' . ($assetBarang?->id ?? 'non-asset')" />
        </div>

        <div x-data="durasiMaintenance()" class="col-span-2 w-full rounded-md border-2 border-gray-200 p-2">
            <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-400">Lama Waktu Maintenance</span>
                <div class="w-52">
                    <span class="text-2xl font-semibold text-red-500" x-text="displayDuration"></span>
                </div>
                <span class="text-xs text-gray-400" x-text="'Mulai : '+startTime"></span>
            </div>
        </div>
    </div>

    <div class="flex w-full flex-col gap-3 lg:grid lg:grid-cols-2">
        @if ($assetBarang)
            <div class="flex flex-col gap-3 rounded-md border-2 border-gray-200 p-2">
                <livewire:Maintenance.Work.ListKomponen :$assetBarang :key="'komponen-' . ($assetBarang?->id ?? 0)" />
            </div>
        @else
            <div class="flex flex-col gap-3 rounded-md border-2 border-gray-200 p-3 bg-white">
                <div class="flex items-center gap-2 border-b pb-2">
                    <span class="font-bold text-gray-800 text-sm">Informasi Pengaduan Non-Aset</span>
                </div>
                <div class="text-xs text-gray-600 space-y-1.5">
                    <p><strong>No. Tiket:</strong> <span class="font-mono text-indigo-600 font-bold">{{ $jadwal?->request?->nomor_tiket ?? ('#REQ-' . ($jadwal?->maintc_request_id ?? '-')) }}</span></p>
                    <p><strong>Ruangan / Lokasi:</strong> {{ $jadwal?->request?->ruangan?->nama ?? '-' }}</p>
                    <p><strong>Pelapor:</strong> {{ $jadwal?->request?->user_request ?? '-' }}</p>
                    <p><strong>Catatan Kerusakan:</strong> {{ $jadwal?->request?->note ?? '-' }}</p>
                    @if ($jadwal?->note)
                        <p><strong>Catatan Koordinasi:</strong> {{ $jadwal->note }}</p>
                    @endif
                </div>
            </div>
        @endif

        <div class="flex flex-col gap-3 rounded-md border-2 border-gray-200 p-2">
            <livewire:Maintenance.Work.ListRiwayat :assetBarang="$assetBarang" :workId="$jadwal?->work?->id" :jadwalId="$jadwal?->id" :key="'riwayat-' . ($assetBarang?->id ?? 'work-' . ($jadwal?->work?->id ?? $jadwal?->id))" />
        </div>
    </div>

    <div class="flex justify-end">
        <x-ts:button outline sm danger x-on:click="$dispatch('close-modal', { id: 'modal-maintenance-work' })">
            Tutup
        </x-ts:button>
    </div>
</div>

@script
    <script>
        Alpine.data('durasiMaintenance', () => {
            return {
                startTime: @entangle('tanggal_mulai').live,
                currentTime: new Date(),
                finishTime: null,
                isFinished: false,
                finalDuration: '',
                intervalId: null,
                inputStartTime: '',

                init() {
                    // Convert string dari Livewire ke Date object
                    if (typeof this.startTime === 'string') {
                        this.startTime = new Date(this.startTime.replace(' ', 'T'));
                    }

                    // Set default input value
                    this.inputStartTime = this.formatForInput(this.startTime);

                    // Start the counter
                    this.startCounter();
                },

                get displayDuration() {
                    if (this.isFinished) {
                        return this.finalDuration;
                    }

                    const diff = this.currentTime - this.startTime;
                    return this.formatDuration(diff);
                },

                formatDuration(milliseconds) {
                    const totalSeconds = Math.floor(milliseconds / 1000);
                    const hours = Math.floor(totalSeconds / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    if (hours > 0) {
                        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    } else {
                        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    }
                },

                formatDateTime(date) {
                    if (!date) return '';
                    return date.toLocaleString('id-ID', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                },

                formatForInput(date) {
                    if (!date) return '';
                    const year = date.getFullYear();
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const day = date.getDate().toString().padStart(2, '0');
                    const hours = date.getHours().toString().padStart(2, '0');
                    const minutes = date.getMinutes().toString().padStart(2, '0');
                    return `${year}-${month}-${day}T${hours}:${minutes}`;
                },


                startCounter() {
                    this.intervalId = setInterval(() => {
                        if (!this.isFinished) {
                            this.currentTime = new Date();
                        }
                    }, 1000);
                },


                finishWork() {
                    if (this.isFinished) return;

                    this.isFinished = true;
                    this.finishTime = new Date();

                    // Hitung durasi final
                    const totalDuration = this.finishTime - this.startTime;
                    this.finalDuration = this.formatDuration(totalDuration);

                    // Stop counter
                    if (this.intervalId) {
                        clearInterval(this.intervalId);
                    }

                    // Simulasi mengirim data ke server
                    console.log('Data yang akan dikirim ke server:', {
                        start_time: this.startTime.toISOString().slice(0, 19).replace('T', ' '),
                        finish_time: this.finishTime.toISOString().slice(0, 19).replace('T', ' '),
                        duration_seconds: Math.floor((this.finishTime - this.startTime) / 1000)
                    });

                    alert('Pengerjaan selesai!\nDurasi: ' + this.finalDuration);
                },


                setNewStartTime() {
                    if (!this.inputStartTime) return;

                    // Reset state
                    this.startTime = new Date(this.inputStartTime);
                    this.currentTime = new Date();
                    this.finishTime = null;
                    this.isFinished = false;
                    this.finalDuration = '';

                    // Restart counter
                    if (this.intervalId) {
                        clearInterval(this.intervalId);
                    }
                    this.startCounter();

                    console.log('Pengerjaan baru dimulai:', this.startTime);
                }
            }
        })
    </script>
@endscript
