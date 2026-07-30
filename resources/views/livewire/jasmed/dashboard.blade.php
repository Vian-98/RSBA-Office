<div class="space-y-6">
    {{-- Filter Header --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Periode Checkout</label>
            <x-ts:date wire:model.live='periode' wire:change='updateDashboard' month-year-only />
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Jenis Layanan</label>
            <x-ts:select.styled wire:model.live='layanan' wire:change='updateDashboard' placeholder="Semua Layanan" :options="$layanan_opt" select="label:label|value:value" />
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Cara Bayar</label>
            <x-ts:select.styled wire:model.live='cabar' wire:change='updateDashboard' placeholder="Semua Cara Bayar" :options="$cabar_opt" select="label:label|value:value" />
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Batch Klaim</label>
            <x-ts:select.styled wire:model.live='batch' wire:change='updateDashboard' placeholder="Semua Batch" :options="$batchOptions" select="label:label|value:value" />
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Diajukan --}}
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700">Total Klaim Diajukan</span>
                <span class="rounded-lg bg-indigo-100 p-1.5 text-indigo-600">
                    <x-ts:icon name="tabler.file-text" class="h-4 w-4" />
                </span>
            </div>
            <h3 class="mt-2 text-2xl font-extrabold text-indigo-900">Rp {{ $this->getStats['totalDiajukan'] }}</h3>
            <p class="text-xs text-indigo-600 font-semibold mt-1">{{ $this->getStats['pasienDiajukan'] }} Pasien</p>
        </div>

        {{-- Disetujui --}}
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Klaim Disetujui</span>
                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                    {{ $this->getStats['persenteseDisetujui'] }}
                </span>
            </div>
            <h3 class="mt-2 text-2xl font-extrabold text-emerald-900">Rp {{ $this->getStats['totalDisetujui'] }}</h3>
            <p class="text-xs text-emerald-600 font-semibold mt-1">{{ $this->getStats['pasienDisetujui'] }} Pasien Disetujui</p>
        </div>

        {{-- Pending --}}
        <div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700">Pending / Verifikasi</span>
                <span class="rounded-lg bg-amber-100 p-1.5 text-amber-600">
                    <x-ts:icon name="tabler.clock" class="h-4 w-4" />
                </span>
            </div>
            <h3 class="mt-2 text-2xl font-extrabold text-amber-900">Rp {{ $this->getStats['totalPending'] }}</h3>
            <p class="text-xs text-amber-600 font-semibold mt-1">{{ $this->getStats['pasienPending'] }} Pasien Pending</p>
        </div>

        {{-- Jasa Dokter --}}
        <div role="button" wire:click="detail('dokter')" class="rounded-2xl border border-purple-100 bg-purple-50/50 p-5 shadow-sm hover:bg-purple-100/50 transition-all cursor-pointer group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-700">Total Jasa Dokter</span>
                <span class="rounded-lg bg-purple-100 p-1.5 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <x-ts:icon name="tabler.user-check" class="h-4 w-4" />
                </span>
            </div>
            <h3 class="mt-2 text-2xl font-extrabold text-purple-900">Rp {{ $this->getStatsJasa }}</h3>
            <p class="text-xs text-purple-600 font-semibold mt-1 flex items-center gap-1">
                <span>Klik untuk Detail</span>
                <x-ts:icon name="tabler.arrow-right" class="h-3 w-3" />
            </p>
        </div>
    </div>

    {{-- Detail Area --}}
    @if($content === 'dokter')
        <div class="rounded-2xl border border-purple-100 bg-white p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-purple-100 pb-3">
                <h3 class="text-sm font-extrabold text-purple-900 flex items-center gap-2">
                    <x-ts:icon name="tabler.users" class="h-4 w-4 text-purple-600" />
                    Rincian Remunerasi Jasa Per Dokter
                </h3>
                <button wire:click="$set('content', null)" class="text-xs font-semibold text-slate-400 hover:text-slate-600">
                    Tutup Detail &times;
                </button>
            </div>
            <livewire:Jasmed.DetailsJasaDokter :$periode :$layanan :$cabar :$batch :key="'detail-jasa-dokter' . md5($periode . $layanan . $cabar . $batch)" />
        </div>
    @endif
</div>
