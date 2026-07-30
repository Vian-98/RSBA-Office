<div class="flex h-screen w-full flex-col">
    <div class="w-full flex-1 overflow-y-auto">
        <embed src="{{ asset('storage/' . $file?->path) }}" type="application/pdf" class="h-full w-full">
    </div>
</div>
