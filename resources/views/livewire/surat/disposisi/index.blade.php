<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <x-ts:icon name="document-text" class="w-7 h-7 text-indigo-600 dark:text-indigo-400" />
                Surat Disposisi Direktur
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Kelola lembar penerus/disposisi Direktur, format agenda, dan distribusi otomatis ke jajaran penerima.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <x-ts:button wire:click="openModalSetting" outline icon="cog" color="slate">
                Format Agenda
            </x-ts:button>
            <x-ts:button href="{{ route('kepegawaian.surat.disposisi.add') }}" color="indigo" icon="plus">
                Buat Disposisi Baru
            </x-ts:button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex flex-col md:flex-row items-center gap-4">
        <div class="flex-1 w-full">
            <x-ts:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari No Agenda, No Surat, Perihal, Asal Surat..." />
        </div>
        <div class="w-full md:w-48">
            <x-ts:select.styled wire:model.live="statusFilter" :options="[
                ['label' => 'Semua Status', 'value' => 'all'],
                ['label' => 'Draft', 'value' => 'draft'],
                ['label' => 'Terdisposisi (Signed)', 'value' => 'dispatched'],
                ['label' => 'Terarsip', 'value' => 'archived'],
            ]" select="label:label|value:value" />
        </div>
    </div>

    <!-- Next Agenda Number Banner -->
    <div class="bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-indigo-600 text-white rounded-lg">
                <x-ts:icon name="hashtag" class="w-5 h-5" />
            </div>
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Prinjauan No. Agenda Berikutnya:</span>
                <p class="text-base font-bold text-slate-800 dark:text-slate-100">{{ $nextNoAgendaPreview }}</p>
            </div>
        </div>
        <button wire:click="openModalSetting" class="text-xs font-medium text-indigo-600 hover:underline">
            Ubah Template Format &rarr;
        </button>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:text-slate-200 font-semibold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="py-3.5 px-4">No. Agenda</th>
                        <th class="py-3.5 px-4">Tgl & No. Surat</th>
                        <th class="py-3.5 px-4">Perihal & Asal</th>
                        <th class="py-3.5 px-4">Penerima (Kepada YTH)</th>
                        <th class="py-3.5 px-4">Status & TTD</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($disposisiList as $item)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/50 transition">
                            <td class="py-4 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $item->no_agenda }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-medium text-slate-800 dark:text-slate-100">{{ $item->no_surat }}</div>
                                <div class="text-xs text-slate-500">{{ $item->tgl_surat->format('d M Y') }}</div>
                            </td>
                            <td class="py-4 px-4 max-w-xs">
                                <div class="font-medium text-slate-800 dark:text-slate-100 truncate" title="{{ $item->perihal }}">{{ $item->perihal }}</div>
                                <div class="text-xs text-slate-500 truncate" title="{{ $item->asal_surat }}">Asal: {{ $item->asal_surat }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($item->details as $det)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                            {{ $det->nama_tujuan }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                @if($item->signature_hash)
                                    <x-ts:badge color="emerald" icon="check-badge" text="TTD Digital Valid" />
                                @else
                                    <x-ts:badge color="amber" text="Draft" />
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right space-x-2">
                                <x-ts:button href="{{ route('kepegawaian.surat.disposisi.show', $item->id) }}" size="xs" color="indigo" outline icon="eye">
                                    Detail
                                </x-ts:button>
                                <x-ts:button href="{{ route('kepegawaian.surat.disposisi.print', $item->id) }}" target="_blank" size="xs" color="slate" outline icon="printer">
                                    Cetak
                                </x-ts:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                Belum ada data Surat Disposisi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-700">
            {{ $disposisiList->links() }}
        </div>
    </div>

    <!-- Modal Setting Pattern Agenda -->
    <x-ts:modal wire:model="modalSettingPattern" title="Pengaturan Format No. Agenda">
        <div class="space-y-4">
            <p class="text-sm text-slate-600 dark:text-slate-300">
                Atur template format nomor agenda secara dinamis. Anda dapat menggunakan variabel placeholder berikut:
            </p>
            <div class="text-xs bg-slate-100 dark:bg-slate-900 p-3 rounded-lg font-mono space-y-1 text-slate-700 dark:text-slate-300">
                <div><span class="font-bold text-indigo-600">{NUMBER:4}</span> : Urutan nomor dengan padding 4 digit (0001, 0002)</div>
                <div><span class="font-bold text-indigo-600">{YYYY}</span> : Tahun berjalan 4 digit (2026)</div>
                <div><span class="font-bold text-indigo-600">{MM}</span> : Bulan berjalan 2 digit (08)</div>
            </div>
            <x-ts:input wire:model="patternInput" label="Format Template No. Agenda" placeholder="Contoh: AG/{YYYY}/{NUMBER:4}" />
        </div>
        <x-slot name="footer">
            <x-ts:button wire:click="$set('modalSettingPattern', false)" color="slate" outline>Batal</x-ts:button>
            <x-ts:button wire:click="savePatternSetting" color="indigo">Simpan Format</x-ts:button>
        </x-slot>
    </x-ts:modal>
</div>
