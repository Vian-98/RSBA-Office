<!-- Filters Panel -->
<div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-2xs">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <x-ts:input wire:model.live.debounce.300ms="search" placeholder="Cari nama / NIP..." icon="tabler.search" class="w-full" />
        </div>
        <div>
            <select wire:model.live="bagianFilter" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Bagian</option>
                @foreach($bagians as $bag)
                    <option value="{{ $bag->id }}">{{ $bag->nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="statusFilter" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Status Kerja</option>
                @foreach($statusOptions as $opt)
                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="payrollStatusFilter" class="w-full rounded-lg border-gray-300 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Status Input</option>
                <option value="generated">Selesai (Sudah Input)</option>
                <option value="pending">Belum Input Gaji</option>
            </select>
        </div>
        <div>
            <x-month-picker wire:model.live="periode" />
        </div>
    </div>
</div>
