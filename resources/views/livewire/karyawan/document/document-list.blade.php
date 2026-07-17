<div class="w-full">
    <div class="flex flex-col gap-2">
        <div class="ml-auto flex justify-end">
            <x-ts:button xs icon="tabler.plus" x-on:click="$dispatch('open-modal',{id:'add-document-karyawan'})">Upload</x-ts:button>
        </div>
        <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">

                <div class="overflow-hidden border border-gray-100 rounded-lg shadow-2xs">
                    <table class="min-w-full text-left text-sm text-gray-700 divide-y divide-gray-100">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th scope="col" class="px-6 py-3">Nama Document</th>
                                <th scope="col" class="px-6 py-3">Jenis</th>
                                <th scope="col" class="px-6 py-3">Tgl Upload</th>
                                <th scope="col" class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($documents as $item)
                                <tr class="transition-colors duration-200 hover:bg-gray-50/50">
                                    <td class="whitespace-nowrap px-6 py-3 font-medium text-gray-900">{{ $item->nama }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-gray-600">{{ $item->jenis }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-gray-600">{{ $item->created_at }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-right flex justify-end gap-1.5">
                                        <x-ts:button xs icon="tabler.folder-open" wire:click='view({{ $item->id }})' loading="view({{ $item->id }})" />
                                        <x-ts:button xs icon="tabler.trash" color="red" wire:click='delete({{ $item->id }})' loading="delete({{ $item->id }})" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-1.5 py-4">
                                            <x-tabler-file-type-doc class="h-8 w-8 text-gray-300" />
                                            <span class="text-xs font-semibold text-gray-400">Belum ada dokumen yang diupload</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <x-filament::modal id="add-document-karyawan" width="2xl" :close-by-clicking-away="false" :autofocus="false">
        <x-slot name="heading">
            Tambah Dokumen
        </x-slot>
        <livewire:Karyawan.Document.Add :id="$karyawanId" :key="Str::random()" @document-karyawan-created="$refresh" />
    </x-filament::modal>


    <x-filament::modal id="view-document-karyawan" width="6xl" class="h-screen min-h-full" :close-by-clicking-away="false" :autofocus="false">
        <x-slot name="heading">
            Document <span class="text-primary-500 font-semibold">{{ $documentsSelected?->nama }}</span>
        </x-slot>

        <livewire:Karyawan.Document.View :$documentsSelected :key="Str::random()" />
    </x-filament::modal>
</div>
