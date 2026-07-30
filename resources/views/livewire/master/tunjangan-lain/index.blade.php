<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center bg-white p-4 rounded-2xl border border-slate-100 shadow-2xs">
        <div>
            <h1 class="text-lg font-bold text-slate-800">Master Tunjangan Lain-Lain</h1>
            <p class="text-xs text-slate-500">Kelola daftar jenis tunjangan tidak tetap/tambahan yang dapat ditambahkan secara dinamis pada penggajian bulanan.</p>
        </div>
        <div>
            <x-ts:button size="sm" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold" wire:click="openModal()">
                <x-tabler-plus class="h-4 w-4 mr-1.5" />
                Tambah Jenis Tunjangan
            </x-ts:button>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-2xs">
        <div class="max-w-md">
            <x-ts:input wire:model.live.debounce.300ms="search" placeholder="Cari nama tunjangan..." icon="tabler.search" />
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-slate-600">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase text-slate-400">
                        <th class="px-6 py-4">Nama Tunjangan</th>
                        <th class="px-6 py-4">Keterangan</th>
                        <th class="px-6 py-4">Tanggal Dibuat</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($allowanceTypes as $type)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800">
                                {{ $type->nama }}
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ $type->keterangan ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400">
                                {{ \Carbon\Carbon::parse($type->created_at)->translatedFormat('d F Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-ts:button flat color="amber" size="xs" class="font-bold" wire:click="openModal({{ $type->id }})">
                                        <x-tabler-edit class="h-4 w-4" />
                                        Edit
                                    </x-ts:button>
                                    <x-ts:button flat color="red" size="xs" class="font-bold" wire:click="confirmDelete({{ $type->id }})">
                                        <x-tabler-trash class="h-4 w-4" />
                                        Hapus
                                    </x-ts:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                <x-tabler-database-x class="mx-auto h-12 w-12 text-slate-300 mb-3" />
                                <div class="text-sm font-semibold">Belum Ada Tunjangan Lain-Lain Terdaftar</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($allowanceTypes->hasPages())
            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                {{ $allowanceTypes->links() }}
            </div>
        @endif
    </div>

    <!-- Add/Edit Modal -->
    <x-ts:modal wire="isModalOpen" size="lg" class="relative z-50">
        <x-slot:title>
            <span class="flex items-center gap-1.5 font-semibold text-slate-700">
                <x-tabler-award class="h-5 w-5 text-indigo-500" />
                {{ $editingId ? 'Edit Jenis Tunjangan' : 'Tambah Jenis Tunjangan' }}
            </span>
        </x-slot:title>

        <form wire:submit.prevent="save" class="space-y-4 p-2">
            <div>
                <x-ts:input label="Nama Tunjangan" wire:model.defer="nama" placeholder="Contoh: Tunjangan Makan, Tunjangan Transport" />
            </div>

            <div>
                <x-ts:textarea label="Keterangan" wire:model.defer="keterangan" placeholder="Keterangan detail mengenai tunjangan..." rows="3" />
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                <x-ts:button type="button" flat color="slate" wire:click="closeModal">Batal</x-ts:button>
                <x-ts:button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm px-6">
                    Simpan
                </x-ts:button>
            </div>
        </form>
    </x-ts:modal>
</div>
