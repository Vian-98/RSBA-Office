<div class="flex w-full flex-col gap-4">

    <div class="flex w-full flex-col gap-2">
        <label class="text-xs">Import data karyawan.
            <span role="button" class="text-blue-500" wire:click='downloadTemplate'>Template</span>
            <x-spinner target='downloadTemplate' xs />
        </label>
        <x-ts:upload wire:model='excelKaryawan'>
            <x-slot:footer when-uploaded>
                <x-ts:button wire:click="importKaryawan" loading="importKaryawan" icon="tabler.database-import" class="w-full">Upload</x-ts:button>
            </x-slot:footer>
        </x-ts:upload>
    </div>
</div>
