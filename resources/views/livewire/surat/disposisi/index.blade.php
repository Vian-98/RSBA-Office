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
            <x-ts:button x-on:click="$dispatch('open-setting-pattern'); $tsui.open('modal-setting-pattern')" outline icon="cog" color="slate">
                Format Agenda
            </x-ts:button>
            <x-ts:button href="{{ route('kepegawaian.surat.disposisi.add') }}" color="indigo" icon="plus">
                Buat Disposisi Baru
            </x-ts:button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
        <div class="w-full sm:w-80">
            <x-ts:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari Agenda / No / Perihal / Asal..." />
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <x-ts:select.styled wire:model.live="statusFilter" :options="[
                ['label' => 'Semua Status', 'value' => 'all'],
                ['label' => 'Pending / Draft', 'value' => 'draft'],
                ['label' => 'Valid / Disetujui', 'value' => 'disetujui'],
            ]" select="label:label|value:value" class="w-48" />
        </div>
    </div>

    <!-- Preview Agenda Banner -->
    <div class="bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 p-4 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-600 text-white rounded-lg">
                <x-ts:icon name="hashtag" class="w-5 h-5" />
            </div>
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Prinjauan No. Agenda Berikutnya:</span>
                <p class="text-base font-bold text-slate-800 dark:text-slate-100">{{ $nextNoAgendaPreview }}</p>
            </div>
        </div>
        <button x-on:click="$dispatch('open-setting-pattern'); $tsui.open('modal-setting-pattern')" class="text-xs font-medium text-indigo-600 hover:underline">
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
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $item->no_surat }}</div>
                                <div class="text-xs text-slate-400">{{ $item->tgl_surat->format('d M Y') }}</div>
                            </td>
                            <td class="py-4 px-4 max-w-xs">
                                <div class="font-semibold text-slate-800 dark:text-slate-200 truncate" title="{{ $item->perihal }}">
                                    {{ $item->perihal }}
                                </div>
                                <div class="text-xs text-slate-400 truncate">
                                    Asal: {{ $item->asal_surat }}
                                </div>
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
                            <td class="py-4 px-4 text-right">
                                <x-ts:button x-on:click="$dispatch('open-preview-disposisi', { id: {{ $item->id }} }); $tsui.open('modal-preview')" size="xs" color="indigo" outline icon="eye" class="cursor-pointer">
                                    Preview & Aksi
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

    <!-- Sub-Komponen Livewire Independen (On-Demand Modal) -->
    <livewire:surat.disposisi.modal-setting-pattern />
    <livewire:surat.disposisi.modal-preview />

    <script>
    function printDocument(url) {
        let iframe = document.getElementById('print-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'print-iframe';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            iframe.style.visibility = 'hidden';
            document.body.appendChild(iframe);
        }
        iframe.src = url;
        iframe.onload = function() {
            setTimeout(function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 300);
        };
    }
    </script>
</div>
