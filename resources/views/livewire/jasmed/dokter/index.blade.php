<div>
    <x-ts:tab selected="Visit" x-on:navigate="$wire.set('tab',$event.detail.select)">

        {{-- tab pembelian --}}
        <x-ts:tab.items tab="Visit">
            <div class="flex flex-col gap-2">
                <form wire:submit.prevent='cariVisite' class="-2 flex w-full flex-col items-center justify-between gap-3 rounded-lg bg-white lg:grid lg:grid-cols-6">
                    <div class="w-full">
                        <x-ts:date wire:model.live.debounce='tgl_checkout' placeholder="Bulan Layanan" month-year-only />
                    </div>
                    <div class="w-full">
                        <x-ts:select.styled wire:model.lazy='search_option' :options="$searchOptions" select="label:label|value:value" placeholder="Pencarian Menggunakan" />
                    </div>
                    <div class="w-full lg:col-span-2">
                        <x-ts:input wire:model.lazy='cari' placeholder="Cari Pasien" />
                    </div>
                    <div class="w-10">
                        <x-ts:button sm outline type="submit" icon="tabler.zoom" loading="cariPembelian" position="left">Cari</x-ts:button>
                    </div>
                </form>
                <div class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2">
                    <div class="flex flex-wrap items-center gap-2 py-2">
                        <x-ts:checkbox wire:model.live.debounce='is_no_klaim' label="Belum Klaim" />
                        <x-ts:checkbox wire:model.live.debounce='has_no_dokter' label="Belum Ada Dokter" />
                    </div>

                    <livewire:Jasmed.Dokter.CheckVisit :$tgl_checkout :$cabar :$search_option :$cari :$is_no_klaim :$has_no_dokter :key="'dokter.visite.' . Str::random(5)" />
                </div>
            </div>
        </x-ts:tab.items>


        {{-- tab Distribusi --}}
        <x-ts:tab.items tab="Anastesi">

            <div class="flex flex-col gap-2">
                <form wire:submit.prevent='cariAnastesi' class="flex w-full flex-col items-center justify-between gap-3 rounded-lg bg-white lg:grid lg:grid-cols-6">
                    <div class="w-full">
                        <x-ts:date wire:model.live.debounce='tgl_checkout' placeholder="Bulan Layanan" month-year-only />
                    </div>
                    <div class="w-full">
                        <x-ts:select.styled wire:model.lazy='search_option' :options="$searchOptions" select="label:label|value:value" placeholder="Pencarian Menggunakan" />
                    </div>
                    <div class="w-full lg:col-span-2">
                        <x-ts:input wire:model.lazy='cari' placeholder="Cari Pasien" />
                    </div>
                    <div class="w-10">
                        <x-ts:button sm outline type="submit" loading="cariDistribusi" icon="tabler.zoom" position="left">Cari</x-ts:button>
                    </div>

                </form>
                <div class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2">
                    <span class="text-sm italic text-gray-500">Pasien (Operatif/SC/Operatif Mata) yang tidak di input dokter anastesi</span>
                    <livewire:Jasmed.Dokter.CheckAnastesi :$tgl_checkout :$cabar :$search_option :$cari :$is_no_klaim :$has_no_dokter :key="'dokter.anastesi.' . Str::random(5)" />
                </div>
            </div>
        </x-ts:tab.items>
    </x-ts:tab>
</div>
