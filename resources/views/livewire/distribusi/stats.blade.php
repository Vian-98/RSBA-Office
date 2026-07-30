<div class="flex w-full gap-2">

    <div class="w-full flex flex-col lg:grid lg:grid-cols-4 gap-4">
        <x-ts:stats icon="tabler.checklist" color="blue" title="Item Terdistribusi" :number="number_format($totalItemTerdistribusi, 0, ',', '.')" footer="Periode {{ $periode }}" />

        <x-ts:stats icon='tabler.chevrons-up' color="green" title="Top Item" :number="$topItemTerdistribusi" footer="{{ $totalTopItemTerdistribusi }} item diperiode {{ $periode }}" />

        <x-ts:stats icon='tabler.mood-up' color="green" title="Top Unit Tujuan" :number="$topTujuanTerdistribusi" footer="{{ $totalTopTujuanTerdistribusi }} transaksi diperiode {{ $periode }}" />

        <x-ts:stats icon='tabler.droplet-dollar' color="green" title="Higest Value" :number="$higestItemTerdistribusi" footer="Harga item : {{ formatRupiah($totalHigestItemTerdistribusi) }}" />
    </div>
</div>
