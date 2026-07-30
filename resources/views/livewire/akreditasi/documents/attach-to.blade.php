<div>
    <form wire:submit.prevent='submit' autocomplete="off" enctype="multipart/form-data">
        <div class="space-y-2">

            <div class="w-full space-y-1 rounded-md border border-gray-300 p-2 text-sm text-gray-500">
                <div class="flex">
                    <span class="w-32">Nama Files</span>
                    <span>: </span>
                    <span class="ml-2">{{ $documents->nama }}</span>
                </div>
                <div class="flex">
                    <span class="w-32">Kepemilikan</span>
                    <span>: </span>
                    <span class="ml-2">
                        {{-- @dd($this->kepemilikanDocuments) --}}
                        {{ $sumber_element }}
                    </span>
                </div>
                <div class="flex">
                    <span class="w-32">Upload Oleh</span>
                    <span>: </span>
                    <span class="ml-2">{{ $documents->user_upload }}</span>
                </div>
                <div class="flex">
                    <span class="w-32">Tgl Upload</span>
                    <span>: </span>
                    <span class="ml-2">{{ $documents->created_at }}</span>
                </div>
            </div>
            <span class="text-sm italic text-indigo-500">Ke :</span>
            <div class="grid w-full grid-cols-3 gap-2">
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

            @error('pdf_file')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            {{-- Loading Indicator --}}
            <div wire:loading wire:target="pdf_file" class="text-sm text-blue-600">
                Memproses file...
            </div>

            {{-- Upload Progress --}}
            <div wire:loading wire:target="submitManual" class="text-sm text-blue-600">
                Mengupload file...
            </div>

            <div class="flex justify-end gap-2">
                <x-ts:button type="submit" loading="submit" icon="tabler.file-export" class="rounded-md bg-blue-600 px-2 py-1 text-sm text-white transition-colors hover:bg-blue-700">
                    Attach
                </x-ts:button>
            </div>
        </div>
    </form>
</div>
