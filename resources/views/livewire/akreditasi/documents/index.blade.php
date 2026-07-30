<div class="flex flex-col gap-2">
    <div x-data="{ open: '' }" class="mb-6">
        {{-- Button Switch --}}
        <div class="flex flex-row gap-4">
            <x-ts:radio @click="open = 'new_file'" id="new" wire:model='file_is' value="new" label="Tambah File" />
            {{-- <x-ts:radio @click="open = 'linked_file'" id="linked" wire:model='file_is' value="linked" label="Linked File" /> --}}
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

            <livewire:Akreditasi.Documents.AddLinked :$element_id :key="'add-linked-file-' . $element_id" />
        </div>
        {{-- End form linked --}}

    </div>

    <div>
        <livewire:Akreditasi.Documents.TableDocuments :elementId="$element_id" :key="'table-docs-' . Str::random()" />
    </div>
</div>
