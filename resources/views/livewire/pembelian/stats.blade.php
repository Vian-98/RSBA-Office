<div class="flex w-full flex-col gap-2">

    <div class="flex w-full flex-col gap-2 lg:grid lg:grid-cols-5">
        <x-ts:stats icon="tabler.checklist" color="blue" title="Total Pembelian" :footer="'Pembelian dibulan ' . $bulan" :number="formatRupiah($nilaiPembelian)" />

        <x-ts:stats icon='tabler.file-report' color="orange" title="On Proses" :footer="'Dari total ' . $countTransaksiBulan . ' transaksi dibulan ' . $bulan" :number="$onWaiting" />

        {{-- 
        <x-ts:stats icon='file-alert' color="red" title="Belum Dibayarkan" :footer="'Dari total ' . $countTransaksiBulan . ' transaksi dibulan ' . $bulan" :number="$countBelumDibayar" /> 
        --}}

        <x-ts:stats icon='tabler.chevrons-up' color="orange" title="Top Item" :number="$topItemDibeli" />

        <x-ts:stats icon='tabler.mood-up' color="red" title="Top Distributor" :number="$topDistibutor" />

        <x-ts:stats icon='tabler.droplet-dollar' color="green" title="Higest Value" :number="$higestItemValue" />

    </div>
</div>
