<div class="flex flex-col gap-4">
    <div class="rounded-lg bg-indigo-50 border border-indigo-200 p-3 text-xs text-indigo-800">
        💡 <strong>Catatan:</strong> Aturan yang dikonfigurasi di sini akan menjadi <em>override</em> khusus untuk pegawai di Departemen <strong>{{ $bagian?->nama ?? '-' }}</strong>. Jika tidak diatur, sistem akan menggunakan Aturan Umum RSBA sebagai default.
    </div>

    @php
        $isSdmUser = auth()->user()?->hasRole(['Super-Admin', 'Staff-SDM', 'Wakil-Direktur', 'Wadir-SDM-Umum']);
    @endphp

    {{-- Form Tambah/Edit Aturan (Hanya untuk SDM ke atas) --}}
    @if($isSdmUser)
        <form wire:submit.prevent="saveAturan" class="p-3 bg-slate-50 border border-slate-200 rounded-lg flex flex-col gap-3">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                {{ $editingAturanId ? 'Edit Aturan Khusus' : 'Tambah Aturan Khusus Baru' }}
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <x-ts:select.styled 
                        label="Jenis Aturan" 
                        wire:model="kode" 
                        :options="$optionsKode" 
                        select="label:label|value:value" 
                    />
                </div>
                <div>
                    <x-ts:input label="Nilai / Target Aturan" wire:model="nilai" placeholder="misal: 6" />
                </div>
            </div>
            <div class="flex items-center justify-between mt-1">
                <x-ts:toggle label="Status Aktif" wire:model="aktif" />
                <div class="flex items-center gap-2">
                    @if($editingAturanId)
                        <x-ts:button type="button" secondary sm wire:click="resetForm">
                            Batal
                        </x-ts:button>
                    @endif
                    <x-ts:button type="submit" primary sm icon="tabler.check">
                        {{ $editingAturanId ? 'Simpan Perubahan' : 'Tambah Aturan' }}
                    </x-ts:button>
                </div>
            </div>
        </form>
    @endif

    {{-- Daftar Aturan Khusus Departemen Ini --}}
    <div class="flex flex-col gap-2 mt-2">
        <h4 class="text-xs font-bold text-slate-700">Daftar Aturan Khusus Departemen Ini:</h4>
        @if($aturanList->isEmpty())
            <div class="text-center py-6 border border-dashed border-slate-300 rounded-lg text-xs text-slate-500">
                Belum ada aturan khusus untuk departemen ini. Departemen ini menggunakan Aturan Umum RSBA.
            </div>
        @else
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="w-full text-xs text-left text-slate-600">
                    <thead class="bg-slate-100 text-slate-700 uppercase font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-3 py-2">Jenis Aturan</th>
                            <th class="px-3 py-2">Nilai</th>
                            <th class="px-3 py-2">Status</th>
                            @if($isSdmUser)
                                <th class="px-3 py-2 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($aturanList as $item)
                            @php
                                $enumKode = \App\Enums\KodeAturanJadwal::tryFrom($item->kode);
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 font-medium text-slate-800">
                                    {{ $enumKode?->nama() ?? $item->kode }}
                                </td>
                                <td class="px-3 py-2 font-semibold text-indigo-700">
                                    {{ $item->nilai }}
                                </td>
                                <td class="px-3 py-2">
                                    <x-ts:badge :color="$item->aktif ? 'emerald' : 'slate'" text="{{ $item->aktif ? 'Aktif' : 'Non-aktif' }}" />
                                </td>
                                @if($isSdmUser)
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <x-ts:button icon="tabler.pencil" color="amber" xs wire:click="editAturan({{ $item->id }})" />
                                            <x-ts:button icon="tabler.trash" color="rose" xs wire:click="deleteAturan({{ $item->id }})" />
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
