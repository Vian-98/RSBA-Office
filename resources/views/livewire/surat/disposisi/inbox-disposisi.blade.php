<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <x-ts:icon name="inbox-arrow-down" class="w-7 h-7 text-indigo-600 dark:text-indigo-400" />
                Inbox Disposisi Saya
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Daftar instruksi disposisi dari Direktur yang ditujukan kepada akun/jabatan Anda.
            </p>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:text-slate-200 font-semibold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="py-3.5 px-4">No. Agenda & Tgl</th>
                        <th class="py-3.5 px-4">No. Surat & Asal</th>
                        <th class="py-3.5 px-4">Perihal</th>
                        <th class="py-3.5 px-4">Instruksi RTL</th>
                        <th class="py-3.5 px-4">Status Tindak Lanjut</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($inboxItems as $item)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/50 transition">
                            <td class="py-4 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $item->disposisi->no_agenda ?? '-' }}
                                <div class="text-xs text-slate-500 font-sans font-normal">
                                    {{ $item->disposisi ? $item->disposisi->created_at->format('d/m/Y H:i') : '' }}
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-medium text-slate-800 dark:text-slate-100">{{ $item->disposisi->no_surat ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $item->disposisi->asal_surat ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4 max-w-xs font-medium text-slate-800 dark:text-slate-100 truncate" title="{{ $item->disposisi->perihal ?? '' }}">
                                {{ $item->disposisi->perihal ?? '-' }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-1">
                                    @if($item->is_info) <x-ts:badge color="indigo" text="Info" /> @endif
                                    @if($item->is_action) <x-ts:badge color="emerald" text="Action" /> @endif
                                    @if($item->is_arsip) <x-ts:badge color="amber" text="Arsip" /> @endif
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                @if($item->status_tindak_lanjut === 'done')
                                    <x-ts:badge color="emerald" icon="check" text="Selesai / Paraf" />
                                @else
                                    <x-ts:badge color="amber" text="Belum Paraf" />
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right space-x-2">
                                @if($item->status_tindak_lanjut === 'done')
                                    <x-ts:button size="xs" color="slate" outline disabled icon="check" class="opacity-60 cursor-not-allowed">
                                        Sudah Diparaf
                                    </x-ts:button>
                                @else
                                    <x-ts:button wire:click="openModalParaf({{ $item->id }})" x-on:click="$tsui.open('modal-paraf')" size="xs" color="indigo" icon="check-badge">
                                        Tindak Lanjut & Paraf
                                    </x-ts:button>
                                @endif
                                <x-ts:button href="{{ route('kepegawaian.surat.disposisi.show', $item->surat_disposisi_id) }}" size="xs" color="slate" outline icon="eye">
                                    Detail
                                </x-ts:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                Tidak ada disposisi masuk di inbox Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">
            {{ $inboxItems->links() }}
        </div>
    </div>

    <!-- Modal Tindak Lanjut & Paraf -->
    <x-ts:modal wire="modalParaf" id="modal-paraf" title="Tindak Lanjut & Paraf Disposisi">
        @if($selectedDetail)
            <div class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-900 p-3 rounded-xl text-xs space-y-1">
                    <div><strong>No. Agenda:</strong> {{ $selectedDetail->disposisi->no_agenda ?? '-' }}</div>
                    <div><strong>Perihal:</strong> {{ $selectedDetail->disposisi->perihal ?? '-' }}</div>
                    <div><strong>Catatan Direktur:</strong> {{ $selectedDetail->disposisi->catatan ?? '-' }}</div>
                </div>

                <x-ts:textarea wire:model="catatanPenerima" label="Catatan / Response Tindak Lanjut (Opsional)" placeholder="Tuliskan catatan tindak lanjut..." />
            </div>
        @endif
        <x-slot name="footer">
            <x-ts:button wire:click="$set('modalParaf', false)" x-on:click="$tsui.close('modal-paraf')" color="slate" outline>Batal</x-ts:button>
            <x-ts:button wire:click="submitParaf" color="emerald" icon="check-badge">Simpan & Paraf Digital</x-ts:button>
        </x-slot>
    </x-ts:modal>
</div>
