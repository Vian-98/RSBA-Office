<div class="flex flex-col gap-4">

    <div class="flex w-full flex-col gap-2 lg:grid lg:w-1/2 lg:grid-cols-3">
        <div wire:key="chapter-select-{{ $kegiatan_id }}">
            <x-ts:select.styled wire:model.live.blur="chapter_id" searchable :request="route('api.akreditasi.chapters', ['kegiatan' => $kegiatan_id])" select="label:singkatan|value:id" placeholder="Chapter" />
        </div>

        <div wire:key='sub-select-{{ $chapter_id }}'>
            <x-ts:select.styled wire:model.live.blur="sub_id" searchable :request="route('api.akreditasi.babs', ['chapter' => $chapter_id, 'type' => 'sub'])" select="label:nama|value:id" :disabled="!$chapter_id" placeholder="Bab / Sub" />
        </div>

        <div wire:key="element-select-{{ $sub_id }}">
            <x-ts:select.styled wire:model.live.blur="element_id" searchable :request="route('api.akreditasi.elements', ['sub' => $sub_id])" select="label:label|value:id" :disabled="!$sub_id" placeholder="Element" />
        </div>
    </div>

    <div>
        <livewire:Akreditasi.Documents.TablePencarian :$kegiatan_id :$chapter_id :$sub_id :$element_id :key="'table-pencarian-' . $chapter_id . $sub_id . $element_id" />
    </div>
</div>
