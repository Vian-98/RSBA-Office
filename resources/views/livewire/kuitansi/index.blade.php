<div class="flex flex-col gap-2">
    <div class="w-full overflow-visible rounded-lg bg-white">
        {{-- Header: Judul + Tombol Aksi + Filter --}}
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <span class="text-sm font-bold text-slate-700">Daftar Kuitansi</span>
            <div class="flex items-center gap-2">

                {{-- Filter Dropdown --}}
                <x-ts:dropdown position="bottom-end" width="sm">
                    <x-slot:action>
                        <button type="button"
                            x-on:click="show = !show"
                            class="relative flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition hover:border-indigo-400 hover:text-indigo-600">
                            <x-tabler-adjustments-horizontal class="size-4" />
                            Filter
                        </button>
                    </x-slot:action>

                    <div class="w-64 p-4">
                        <div class="mb-3 flex items-center justify-between border-b border-slate-100 pb-2">
                            <span class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                                <x-tabler-adjustments-horizontal class="size-4 text-indigo-500" />
                                Filter Kuitansi
                            </span>
                        </div>

                        {{-- Status --}}
                        <div class="mb-3">
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Status</label>
                            <select
                                x-on:change="$dispatch('kuitansi-filter-status', { value: $event.target.value })"
                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                <option value="">Semua</option>
                                @foreach(\App\Enums\StatusKuitansi::cases() as $status)
                                    <option value="{{ $status->value }}">{{ $status->nama() }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Metode Bayar --}}
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Metode Bayar</label>
                            <select
                                x-on:change="$dispatch('kuitansi-filter-metode', { value: $event.target.value })"
                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                <option value="">Semua</option>
                                @foreach(\App\Models\Keuangan\MetodeBayar::all() as $m)
                                    <option value="{{ $m->nama }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </x-ts:dropdown>

                @can('create-keuangan-kuitansi')
                <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal',{id:'modal-add-kuitansi'})">
                    Buat Kuitansi
                </x-ts:button>
                @endcan
            </div>
        </div>

        {{-- Tabel --}}
        <div>
            <livewire:Kuitansi.TableKuitansi key="table-kuitansi"
                x-on:kuitansi-filter-status.window="$wire.filterStatus = $event.detail.value; $wire.resetTable()"
                x-on:kuitansi-filter-metode.window="$wire.filterMetode = $event.detail.value; $wire.resetTable()"
            />
        </div>
    </div>

    {{-- Modal Add Kuitansi --}}
    <x-filament::modal id="modal-add-kuitansi" width="max-w-4xl" :close-by-clicking-away="false">
        <x-slot:heading>Input Kuitansi Baru</x-slot:heading>
        <livewire:Kuitansi.Add key="add-kuitansi" />
    </x-filament::modal>
</div>

