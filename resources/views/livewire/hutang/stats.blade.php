 <div class="flex w-full flex-col gap-2 lg:grid lg:grid-cols-3">
     <x-ts:stats color="blue" icon="tabler.file-percent" title="Total Periode Ini" :number="formatRupiah($hutang, true, false)" />

     <x-ts:stats color="green" icon="tabler.checklist" title="Telah Dibayar" :number="formatRupiah($dibayar, true, false)" />

     <x-ts:stats color="red" icon="tabler.file-alert" title="Belum Dibayar" :number="formatRupiah($belumDibayar, true, false)" />
 </div>
