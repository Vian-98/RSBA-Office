<div class="flex flex-col gap-1 p-1 text-sm">

    @foreach ($getRecord()->documents as $item)
        <span class="flex flex-col rounded-md border border-gray-200 px-1 py-0 hover:-translate-y-1 hover:border-indigo-400 hover:shadow" role="button"
            wire:click="modalViewDocument({{ $item->id }},'modal-view-document-ep')">
            <span class="text-xs hover:text-indigo-500">
                {{ Str::limit($item->nama, 20, '...') }}
            </span>
            <div class="text-[10px] italic text-gray-400">
                {{ $item->created_at }}
            </div>
        </span>
    @endforeach
</div>
