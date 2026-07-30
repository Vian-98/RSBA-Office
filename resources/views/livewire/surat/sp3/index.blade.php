<div class="flex flex-col gap-2">
    <div class="w-full overflow-visible rounded-lg bg-white">
        {{-- Header: Judul + Tombol Aksi + Filter --}}
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <span class="text-sm font-bold text-slate-700">Daftar SP3</span>
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
                        {{-- Header --}}
                        <div class="mb-3 flex items-center justify-between border-b border-slate-100 pb-2">
                            <span class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                                <x-tabler-adjustments-horizontal class="size-4 text-indigo-500" />
                                Filter SP3
                            </span>
                        </div>

                        {{-- Status --}}
                        <div class="mb-3">
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Status</label>
                            <select
                                x-on:change="$dispatch('sp3-filter-status', { value: $event.target.value })"
                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                <option value="">Semua</option>
                                @foreach(\App\Enums\StatusApproval::cases() as $status)
                                    <option value="{{ $status->value }}">{{ $status->nama() }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kategori --}}
                        <div class="mb-3">
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Kategori</label>
                            <select
                                x-on:change="$dispatch('sp3-filter-kategori', { value: $event.target.value })"
                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                <option value="">Semua</option>
                                <option value="gaji">Gaji Karyawan</option>
                                <option value="umum">Umum / Non-Gaji</option>
                            </select>
                        </div>

                        {{-- Jabatan Pengaju --}}
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-500">Jabatan Pengaju</label>
                            <select
                                x-on:change="$dispatch('sp3-filter-jabatan', { value: $event.target.value })"
                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                <option value="">Semua</option>
                                @foreach(\App\Models\Sdm\Jabatan::orderBy('nama')->get() as $jabatan)
                                    <option value="{{ $jabatan->id }}">{{ $jabatan->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </x-ts:dropdown>

                <x-ts:button sm outline icon="tabler.checks" x-on:click="$dispatch('open-modal',{id:'modal-verify-sp3'})">
                    Verify
                </x-ts:button>

                <x-ts:button sm icon="tabler.plus" x-on:click="$dispatch('open-modal',{id:'modal-add-sp3'})">
                    Tambah
                </x-ts:button>
            </div>
        </div>

        {{-- Tabel --}}
        <div>
            <livewire:Surat.Sp3.TableSp3 :key="Str::random()"
                x-on:sp3-filter-status.window="$wire.filterStatus = $event.detail.value; $wire.resetTable()"
                x-on:sp3-filter-kategori.window="$wire.filterKategori = $event.detail.value; $wire.resetTable()"
                x-on:sp3-filter-jabatan.window="$wire.filterJabatan = $event.detail.value ? parseInt($event.detail.value) : null; $wire.resetTable()"
            />
        </div>
    </div>

    <x-filament::modal id="modal-verify-sp3" width="max-w-4xl" :close-by-clicking-away="false">
        <x-slot:heading>Verifi SP3</x-slot:heading>

        <livewire:Surat.Sp3.Verify key="verify-sp3" />
    </x-filament::modal>


    <x-filament::modal id="modal-add-sp3" width="max-w-4xl" x-on:created-sp3="$dispatch('close-modal',{id:'modal-add-sp3'})" :close-by-clicking-away="false">
        <x-slot:heading>Buat SP3</x-slot:heading>

        <livewire:Surat.Sp3.Add key="new-sp3" />
    </x-filament::modal>

</div>
