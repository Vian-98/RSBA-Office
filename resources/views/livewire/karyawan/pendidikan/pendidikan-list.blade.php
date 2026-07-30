<div class="flex flex-col gap-2">
    <div class="ml-auto flex justify-end">
        <x-ts:button xs x-on:click="$dispatch('open-modal',{id:'new-pendidikan'})" icon="tabler.plus">Tambah</x-ts:button>
    </div>

    <div class="w-full">
        <div class="flex flex-col">

            <div class="overflow-x-auto lg:-mx-8">
                <div class="inline-block min-w-full py-2 lg:px-8">

                    <div class="overflow-hidden border border-gray-100 rounded-lg shadow-2xs">
                        <table class="min-w-full text-left text-sm text-gray-700 divide-y divide-gray-100">
                            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Pendidikan</th>
                                    <th scope="col" class="px-6 py-3">Tahun Lulus</th>
                                    <th scope="col" class="px-6 py-3">Institusi</th>
                                    <th scope="col" class="px-6 py-3">Gelar</th>
                                    <th scope="col" class="px-6 py-3">Tingkat Pendidikan</th>
                                    <th scope="col" class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($pendidikans as $item)
                                    <tr class="transition-colors duration-200 hover:bg-gray-50/50">
                                        <td class="whitespace-nowrap px-6 py-3 font-medium text-gray-900">{{ $item->nama }}</td>
                                        <td class="whitespace-nowrap px-6 py-3 text-gray-600">{{ $item->tahun_lulus }}</td>
                                        <td class="whitespace-nowrap px-6 py-3 text-gray-600">{{ $item->instansi }}</td>
                                        <td class="whitespace-nowrap px-6 py-3 text-gray-600">{{ $item->gelar }}</td>
                                        <td class="whitespace-nowrap px-6 py-3 text-gray-600">{{ $item->tingkat->nama() }}</td>
                                        <td class="whitespace-nowrap px-6 py-3 text-right">
                                            <x-ts:button xs icon="tabler.trash" color="red" wire:click='delete({{ $item->id }})' loading="delete({{ $item->id }})" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-gray-400">
                                            <div class="flex flex-col items-center justify-center gap-1.5 py-4">
                                                <x-tabler-school class="h-8 w-8 text-gray-300" />
                                                <span class="text-xs font-semibold text-gray-400">Belum ada data pendidikan</span>
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
    </div>

    {{-- Modal new-pendidikan --}}
    <x-filament::modal id="new-pendidikan" width="xl" :autofocus="false" :close-by-clicking-away="false">
        <x-slot name="heading">
            Tambah Pendidikan
        </x-slot>
        {{-- Form new-pendidikan --}}
        <livewire:Karyawan.Pendidikan.Add @pendidikan-karyawan-created="$refresh" :$karyawan :key="Str::random()" />
    </x-filament::modal>
</div>
