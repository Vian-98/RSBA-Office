<div class="w-full rounded border border-gray-300 bg-white p-2" x-show="!$wire.init">
    <span wire:loading class="flex animate-pulse italic text-indigo-500">Loading...</span>

    <span wire:loading.remove class="w-full overflow-y-auto">
        <x-table-static :$headers :$rows :striped />

        <div class="text-md flex justify-between rounded-lg px-2 py-1 font-semibold text-indigo-500">
            <span>Total</span>
            <span class="rounded-lg bg-indigo-200/25 px-1">{{ formatRupiah($total, true, false) }}</span>
        </div>
    </span>

</div>
