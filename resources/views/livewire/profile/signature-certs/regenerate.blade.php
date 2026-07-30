<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <x-ts:password wire:model.defer='password' placeholder="New Password Certificate" />
            <x-ts:password wire:model.defer='passwordConfirmation' placeholder="Konfirmasi Password Certificate" />
        </div>
        <div class="flex justify-end">
            <x-ts:button type="submit" sm icon="tabler.checks" loading="submit">Regenerate</x-ts:button>
        </div>
    </form>
</div>
