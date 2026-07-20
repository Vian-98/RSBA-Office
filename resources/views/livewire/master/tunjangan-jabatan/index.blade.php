<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center bg-white p-4 rounded-2xl border border-slate-100 shadow-2xs">
        <div>
            <nav class="flex text-xs text-slate-400 font-semibold mb-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <span class="inline-flex items-center">
                            <x-tabler-moneybag class="mr-1.5 h-3.5 w-3.5" />
                            Penggajian
                        </span>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <x-tabler-chevron-right class="h-3 w-3 text-slate-400 mx-1" />
                            <span class="text-slate-650">Tunjangan Jabatan</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-lg font-bold text-slate-800">
                Konfigurasi Tunjangan Jabatan
            </h1>
        </div>
        <div>
            <x-ts:button size="sm" type="button" @click="$dispatch('open-modal', {id: 'new-jabatan'})" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold">
                <x-tabler-plus class="h-4 w-4 mr-1.5" />
                Tambah Jabatan Baru
            </x-ts:button>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="grid grid-cols-1 gap-6">
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm space-y-6">
            <!-- Header and Search -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-50 pb-4">
                <h2 class="text-base font-semibold text-slate-700 flex items-center gap-2">
                    <x-tabler-award class="h-5 w-5 text-indigo-500" />
                    Nominal Tunjangan Per Jabatan
                </h2>
                <div class="w-full sm:w-72">
                    <x-ts:input wire:model.live.debounce.300ms="search" placeholder="Cari nama jabatan..." />
                </div>
            </div>

            <!-- Form -->
            <form wire:submit.prevent="save" class="space-y-6">
                <!-- Grid of Jabatans -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($jabatans as $jab)
                        <div class="p-4 bg-slate-50/50 border border-slate-100 rounded-2xl space-y-2 hover:border-slate-200 hover:bg-slate-50 transition-all duration-200">
                            <div class="space-y-0.5">
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Jabatan ID #{{ $jab->id }}</span>
                                <span class="text-sm font-bold text-slate-700 block line-clamp-1">{{ $jab->nama }}</span>
                            </div>
                            <div>
                                <x-ts:input wire:model.defer="allowances.{{ $jab->id }}" type="text" placeholder="0" prefix="Rp" class="font-semibold text-slate-700" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-slate-400">
                            <x-tabler-database-x class="mx-auto h-12 w-12 text-slate-300 mb-3" />
                            <div class="text-sm font-semibold">Tidak Ada Jabatan yang Ditemukan</div>
                        </div>
                    @endforelse
                </div>

                <!-- Footer Action -->
                @if($jabatans->isNotEmpty())
                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <x-ts:button type="submit" loading="save" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm w-full sm:w-auto justify-center px-6">
                            Simpan Tunjangan Jabatan
                        </x-ts:button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Modal Tambah Jabatan Baru -->
    <x-ts:modal id="new-jabatan" title="Tambah Jabatan Baru" size="md">
        @livewire('master.jabatan.add')
    </x-ts:modal>
</div>
