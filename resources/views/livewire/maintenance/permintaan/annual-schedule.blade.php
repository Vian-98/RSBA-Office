<div class="flex flex-col gap-4 py-2">
    {{-- Form Tambah / Edit Jadwal Berkala (Hanya untuk Seksi Umum / Koordinator) --}}
    @can('approval-maintenance')
        <div class="rounded-lg border border-indigo-100 bg-indigo-50/40 p-3">
            <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-indigo-800">
                {{ $editingId ? 'Edit Jadwal Maintenance Berkala' : 'Atur Jadwal Maintenance Berkala Baru' }}
            </h4>

            <form wire:submit.prevent="saveSchedule" class="flex flex-col gap-3" autocomplete="off">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Nama / Judul Kegiatan Service</label>
                        <input type="text" wire:model.defer="judul" placeholder="Contoh: Service AC Rutin / Pengecekan TV"
                            class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        @error('judul') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Tanggal Mulai / Acuan Service</label>
                        <input type="date" wire:model.defer="tgl_mulai"
                            class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        @error('tgl_mulai') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Frekuensi / Interval Service</label>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">Setiap</span>
                            <input type="number" min="1" max="60" wire:model.defer="interval_value"
                                class="w-20 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                            <select wire:model.defer="interval_unit"
                                class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="month">Bulan</option>
                                <option value="year">Tahun</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Catatan Service (Opsional)</label>
                        <input type="text" wire:model.defer="catatan" placeholder="Detail bagian yang wajib dicek..."
                            class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>

                <div class="mt-1 flex items-center justify-end gap-2">
                    @if ($editingId)
                        <button type="button" wire:click="resetForm"
                            class="rounded-md bg-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-300">
                            Batal
                        </button>
                    @endif
                    <button type="submit"
                        class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-indigo-700">
                        <x-ts:icon name="tabler.calendar-plus" class="h-4 w-4" />
                        {{ $editingId ? 'Simpan Perubahan' : 'Tambah Jadwal Berkala' }}
                    </button>
                </div>
            </form>
        </div>
    @endcan

    {{-- Daftar Jadwal Maintenance Berkala Aset --}}
    <div class="flex flex-col gap-2">
        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">
            Daftar Jadwal Service & Pengecekan Berkala Aset Ini
        </h4>

        @if ($schedules->isEmpty())
            <div class="rounded-md border border-dashed border-gray-300 p-4 text-center text-xs text-gray-500">
                Belum ada jadwal maintenance berkala untuk aset ini.
            </div>
        @else
            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 uppercase text-gray-500">
                        <tr>
                            <th class="px-3 py-2">Kegiatan</th>
                            <th class="px-3 py-2">Frekuensi</th>
                            <th class="px-3 py-2">Service Berikutnya</th>
                            <th class="px-3 py-2">Terakhir Service</th>
                            <th class="px-3 py-2">Status</th>
                            @can('approval-maintenance')
                                <th class="px-3 py-2 text-right">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($schedules as $sched)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-3 py-2 font-medium text-gray-800">
                                    {{ $sched->judul }}
                                    @if ($sched->catatan)
                                        <div class="text-[11px] text-gray-400 font-normal">{{ $sched->catatan }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    Setiap {{ $sched->interval_value }} {{ $sched->interval_unit === 'year' ? 'Tahun' : 'Bulan' }}
                                </td>
                                <td class="px-3 py-2 font-semibold {{ $sched->tgl_berikutnya <= now() ? 'text-red-600' : 'text-indigo-600' }}">
                                    {{ $sched->tgl_berikutnya ? $sched->tgl_berikutnya->format('d M Y') : '-' }}
                                    @if ($sched->tgl_berikutnya && $sched->tgl_berikutnya <= now())
                                        <span class="inline-block rounded bg-red-100 px-1 py-0.5 text-[10px] text-red-700">Jatuh Tempo</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-gray-500">
                                    {{ $sched->terakhir_dilakukan ? $sched->terakhir_dilakukan->format('d M Y') : 'Belum pernah' }}
                                </td>
                                <td class="px-3 py-2">
                                    @can('approval-maintenance')
                                        <button type="button" wire:click="toggleSchedule({{ $sched->id }})"
                                            class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $sched->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $sched->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </button>
                                    @else
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $sched->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $sched->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    @endcan
                                </td>
                                @can('approval-maintenance')
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" wire:click="triggerNow({{ $sched->id }})"
                                                wire:confirm="Buat tiket perbaikan/inspeksi rutin sekarang untuk aset ini?"
                                                class="inline-flex items-center gap-0.5 rounded bg-amber-500 px-2 py-1 text-[11px] text-white hover:bg-amber-600"
                                                title="Trigger / Buat Tiket Sekarang">
                                                <x-ts:icon name="tabler.bolt" class="h-3.5 w-3.5" />
                                                Trigger Tiket
                                            </button>

                                            <button type="button" wire:click="editSchedule({{ $sched->id }})"
                                                class="rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-indigo-600" title="Edit">
                                                <x-ts:icon name="tabler.pencil" class="h-4 w-4" />
                                            </button>

                                            <button type="button" wire:click="deleteSchedule({{ $sched->id }})"
                                                wire:confirm="Yakin ingin menghapus jadwal ini?"
                                                class="rounded p-1 text-gray-500 hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-ts:icon name="tabler.trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
