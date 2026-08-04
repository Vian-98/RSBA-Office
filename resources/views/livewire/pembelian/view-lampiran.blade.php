<div class="flex h-screen w-full flex-col">
    <div class="w-full flex-1 space-y-4 overflow-y-auto">
        @foreach ($lampirans ?? [] as $item)
            <iframe src="{{ asset('storage/' . $item) }}" type="application/pdf" class="h-[800px] w-full rounded border"></iframe>
        @endforeach
    </div>
</div>
