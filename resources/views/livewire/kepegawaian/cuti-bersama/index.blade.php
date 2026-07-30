<div class="flex flex-col gap-4">
    {{-- Header --}}
    <div class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Kelola Event Cuti Bersama</h2>
            <p class="text-sm text-gray-500">Master pengajuan dan simulasi pemotongan cuti bersama institusi.</p>
        </div>
        <div>
            <x-ts:button icon="tabler.plus" wire:click="openModal">
                Tambah Event Cuti Bersama
            </x-ts:button>
        </div>
    </div>

    {{-- Search Filter & Table --}}
    <div class="rounded-lg bg-white p-4 shadow-sm flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4">
            <div class="w-72">
                <x-ts:input wire:model.live.debounce.300ms="search" placeholder="Cari nama event..." icon="tabler.search" />
            </div>
        </div>

        <div class="overflow-x-auto border rounded-lg">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                    <tr>
                        <th class="px-4 py-3">Nama Event</th>
                        <th class="px-4 py-3">Tanggal Event</th>
                        <th class="px-4 py-3">Sifat Event</th>
                        <th class="px-4 py-3">Potong Kuota</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Diproses Oleh</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($events as $event)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-900">
                                {{ $event->nama }}
                                @if($event->keterangan)
                                    <div class="text-xs font-normal text-gray-500">{{ $event->keterangan }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($event->tanggal as $tgl)
                                        <span class="rounded bg-indigo-50 px-2 py-0.5 text-xs text-indigo-700 font-mono">
                                            {{ $tgl->tanggal->format('d M Y') }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($event->potong_cuti_tahunan)
                                    <x-ts:badge color="red" light>Memotong Kuota</x-ts:badge>
                                @else
                                    <x-ts:badge color="emerald" light>Libur Bebas</x-ts:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                {{ $event->jenisCuti?->nama ?? 'Cuti Tahunan' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($event->status === 'draft')
                                    <x-ts:badge color="gray" light>Draft</x-ts:badge>
                                @elseif($event->status === 'disimulasikan')
                                    <x-ts:badge color="amber" light>Disimulasikan</x-ts:badge>
                                @elseif($event->status === 'diterapkan')
                                    <x-ts:badge color="emerald">Diterapkan</x-ts:badge>
                                @elseif($event->status === 'dibatalkan')
                                    <x-ts:badge color="red" light>Dibatalkan</x-ts:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($event->diprosesOleh)
                                    <div>{{ $event->diprosesOleh->name }}</div>
                                    <div class="text-gray-400">{{ $event->diproses_at?->format('d/m/Y H:i') }}</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <x-ts:button.circle sm color="indigo" href="{{ route('kepegawaian.cuti-bersama.show', $event->id) }}" icon="tabler.eye" title="Simulasi & Detail" />
                                    
                                    @if($event->status !== 'diterapkan')
                                        <x-ts:button.circle sm color="amber" wire:click="openModal({{ $event->id }})" icon="tabler.edit" title="Edit Event" />
                                        <x-ts:button.circle sm color="red" wire:click="delete({{ $event->id }})" wire:confirm="Yakin ingin menghapus event ini?" icon="tabler.trash" title="Hapus Event" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                Belum ada data event Cuti Bersama. Klik <strong>Tambah Event Cuti Bersama</strong> untuk membuat baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $events->onEachSide(1)->links('partials.pagination', ['paginatorLabel' => 'Event']) }}
            </div>
        @endif
    </div>

    {{-- Modal Form --}}
    <x-ts:modal wire="modalOpen" size="lg" class="relative z-50">
        <x-slot:title>
            {{ $selectedId ? 'Edit Event Cuti Bersama' : 'Tambah Event Cuti Bersama Baru' }}
        </x-slot:title>

        <form wire:submit.prevent="save" class="flex flex-col gap-4 p-2">
            <x-ts:input wire:model="nama" label="Nama Event" placeholder="Contoh: Cuti Bersama Idul Fitri 2026" required />
            
            <x-ts:textarea wire:model="keterangan" label="Keterangan / Surat Edaran" placeholder="Nomor SK atau penjelasan tambahan..." />

            <div>
                <x-ts:date multiple format="YYYY-MM-DD" wire:model="tgl_cuti" placeholder="Pilih Tanggal Cuti Bersama" wire:key="cuti-bersama-multiple"
                    hint="Klik tanggal satu per satu pada kalender untuk menambah/menghapus tanggal Cuti Bersama.">
                </x-ts:date>
            </div>

            <div class="flex justify-end gap-2 mt-4 pt-4 border-t">
                <x-ts:button color="gray" flat wire:click="closeModal">Batal</x-ts:button>
                <x-ts:button type="submit" color="indigo">Simpan Event</x-ts:button>
            </div>
        </form>
    </x-ts:modal>
</div>
