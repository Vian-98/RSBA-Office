{{-- Alert jika Docstore Offline --}}
@if ($errorMessage)
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-3 shadow-xs">
        <x-tabler-alert-triangle class="size-5 text-rose-600 flex-shrink-0 mt-0.5" />
        <div>
            <strong class="font-bold block text-sm mb-0.5">Koneksi Bank Surat Docstore Terkendala</strong>
            <span>{{ $errorMessage }}</span>
        </div>
    </div>
@endif
