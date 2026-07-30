<div x-data="{ showModal: false }" {{ $attributes->merge(['class' => 'relative inline-block']) }} x-on:keydown.escape.window="showModal = false">

    {{-- Trigger (e.g., a button inside a form) --}}
    <div x-on:click.prevent="showModal = true">
        {{ $trigger }}
    </div>

    {{-- Confirmation Modal --}}
    <div class="absolute right-0 z-50 mt-2 max-w-fit rounded-lg bg-white p-4 shadow-lg" x-show="showModal" x-transition x-trap.noscroll="showModal" x-on:click.outside="showModal = false"
        x-on:confirm-action.window="showModal = false" role="dialog" aria-modal="true">

        {{-- Header --}}
        <div class="mb-3 flex items-center justify-between gap-4">
            <span class="flex flex-row items-center gap-2 whitespace-nowrap font-medium text-indigo-500">
                <x-ts:icon name="tabler.alert-circle" class="h-5 w-5" />
                {{ $title }}
            </span>
            <span role="button" x-on:click="showModal = false" class="ms-3 text-gray-400 hover:text-gray-600">
                &times;
            </span>
        </div>

        @isset($description)
            <div class="flex items-center justify-between gap-4">
                <span class="flex flex-row items-center gap-2 whitespace-nowrap text-sm font-light text-gray-500">
                    {{ $description }}
                </span>
            </div>
        @endisset

        {{-- Actions --}}
        <div class="mt-4 flex justify-end gap-3 whitespace-nowrap">

            <x-ts:button sm color="red" x-on:click="showModal = false">
                {{ $cancel ?? 'Batal' }}
            </x-ts:button>

            <x-ts:button sm type="submit" loading="submit" x-on:click="$dispatch('confirm-action'); showModal = false;" icon="tabler.checks">
                {{ $confirm ?? 'Ya' }}
            </x-ts:button>
        </div>
    </div>
</div>
