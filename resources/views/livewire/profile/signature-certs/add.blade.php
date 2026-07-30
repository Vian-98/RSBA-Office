<div>
    <form wire:submit.prevent='submit' class="flex flex-col gap-4" autocomplete="off">
        <div class="flex flex-col gap-2">
            <x-ts:input wire:model.defer='nama' id="nama" name="nama" autocomplete="name" placeholder="Nama" />
            <x-ts:input wire:model.defer='org' id="org" name="org" autocomplete="organization" placeholder="Org" />
            <x-ts:input wire:model.defer='org_unit' id="org_unit" name="org_unit" autocomplete="organization-title" placeholder="Org Unit" />
            <x-ts:input wire:model.defer='email' id="email" name="email" autocomplete="email" placeholder="Email" />
            <x-ts:password wire:model.defer='password' id="password" name="password" autocomplete="new-password" placeholder="Password" />
            <x-ts:password wire:model.defer='passwordConfirmation' id="passwordConfirmation" name="passwordConfirmation" autocomplete="new-password" placeholder="Konfirmasi Password" />

        </div>
        <div class="flex flex-row justify-end gap-2">
            <x-ts:button sm type="submit" icon="tabler.checks" loading="submit">Create</x-ts:button>
        </div>

    </form>
</div>
