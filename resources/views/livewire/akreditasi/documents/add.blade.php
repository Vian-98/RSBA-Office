<div>
    <form wire:submit.prevent='submitDocument' autocomplete="off" enctype="multipart/form-data">
        <div class="space-y-2">
            <x-ts:input wire:model.defer='nama' label="Nama File" placeholder="Nama File" />

            <x-ts:upload wire:model='pdf_file' accept=".pdf" label="File" hint="Upload file format .pdf (max: 25mb)" tip="Drag and drop file disini." />


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


            {{-- Tombol Submit --}}
            <div class="flex justify-end gap-2">
                <button @click="open = false; $wire.resetForm()" type="button" class="rounded-md bg-gray-100 px-2 py-1 text-sm text-gray-700 transition-colors hover:bg-gray-200">
                    Batal
                </button>
                <x-ts:button type="submit" loading="submit" class="rounded-md bg-blue-600 px-2 py-1 text-sm text-white transition-colors hover:bg-blue-700">
                    Simpan
                </x-ts:button>
            </div>
        </div>
    </form>
</div>
