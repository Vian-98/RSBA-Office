<div>
    {{-- expandable form list --}}
    <div x-data="{ open: '' }" class="mb-6">
        {{-- Toggle Button --}}
        {{-- <button @click="open = !open" type="button" class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white p-2 shadow-sm transition-colors hover:bg-gray-50">
            <span class="text-sm text-gray-700">
                Tambah File
            </span>
            <svg x-bind:class="open ? 'rotate-180' : ''" class="h-5 w-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button> --}}

        <div class="flex flex-row gap-4">
            <x-ts:radio @click="open = 'new_file'" id="new" wire:model='file_is' value="new" label="Tambah File" />
            <x-ts:radio @click="open = 'linked_file'" id="linked" wire:model='file_is' value="linked" label="Linked File" />
        </div>

        {{-- Expandable Form --}}
        <div x-show="open === 'new_file'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2" class="mt-2 rounded-lg border border-gray-300 bg-white p-2 shadow-sm">

            <livewire:Akreditasi.Documents.Add :$element_id :key="'add-new-file-' . $element_id" @uploaded-files-element="$refresh" />
        </div>



        {{-- form linked files --}}
        <div x-show="open === 'linked_file'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2" class="mt-2 rounded-lg border border-gray-300 bg-white p-2 shadow-sm">

            <livewire:Akreditasi.Documents.AddLinked :key="'add-linked-file-' . $element_id" />
        </div>
        {{-- End form linked --}}

    </div>


    <div>
        {{ $this->table }}
    </div>


    <x-filament::modal id="modal-document-view" width="screen">
        <x-slot:heading>Document </x-slot:heading>

        <livewire:Akreditasi.Ep.Document :docSelectedId="$selectedDocId" :key="'view-doc-' . Str::random()" />


    </x-filament::modal>
</div>
