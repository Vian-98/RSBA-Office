<div class="flex w-11/12 flex-col">
    <div class="flex animate-pulse flex-col gap-3">

        <div class="relative space-y-3 overflow-hidden rounded-md">
            @isset($img)
                <div class="h-36 w-full rounded-lg bg-neutral-300"></div>
            @endisset

            {{-- skeleton header --}}
            <div class="space-y-3">
                <div class="h-5 w-8/12 rounded-full bg-neutral-300"></div>

                {{-- skeleton paragraf --}}
                <div class="space-y-1">
                    @for ($i = 1; $i <= (isset($paragraf) ? $paragraf : rand(2, 5)); $i++)
                        <div class="h-4 w-full rounded-full bg-neutral-300"></div>
                    @endfor
                </div>

                {{-- skeleton footer --}}
                <div class="flex flex-row gap-2">
                    @for ($i = 1; $i <= (isset($footer) ? $footer : rand(2, 4)); $i++)
                        <div class="w-{{ rand(12, 16) }} h-5 rounded-full bg-neutral-300"></div>
                    @endfor
                </div>
            </div>
        </div>

    </div>
</div>
