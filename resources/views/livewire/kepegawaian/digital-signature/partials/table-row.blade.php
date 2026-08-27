<tr class="hover:bg-slate-50/80 transition-colors">
    <td class="px-6 py-4">
        <div class="font-bold text-slate-800 text-sm">{{ $doc->title }}</div>
        <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $doc->document_number }}</div>
        <div class="flex items-center space-x-2 mt-1.5">
        </div>
    </td>
    <td class="px-6 py-4">
        <div class="text-sm font-semibold text-slate-800">
            {{ optional($doc->user)->name ?? optional(optional($doc->user)->karyawan)->nama ?? optional($doc->user)->email ?? 'Pengguna' }}
        </div>
        <div class="text-xs text-slate-400 font-mono mt-0.5">
            {{ $doc->created_at->format('d M Y H:i') }} WIB
        </div>
    </td>
    <td class="px-6 py-4 text-center">
        @if ($doc->docstore_key)
            <div class="inline-flex flex-col items-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Synced to Vault
                </span>
                <span class="text-[10px] font-mono text-slate-400 mt-1" title="Docstore ID">{{ $doc->docstore_key }}</span>
            </div>
        @else
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                Pending Sync
            </span>
        @endif
    </td>
    <td class="px-6 py-4 text-center whitespace-nowrap">
        @if ($doc->docstore_key)
            <button 
                wire:click="openPrintModal({{ $doc->id }})" 
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all cursor-pointer"
            >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
                Aksi & Opsi Surat
            </button>
        @else
            <button disabled class="inline-flex items-center px-3.5 py-2 bg-slate-200 text-slate-400 text-xs font-medium rounded-xl cursor-not-allowed">
                Print N/A
            </button>
        @endif
    </td>
</tr>
