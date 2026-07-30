<div class="flex h-full w-full flex-col items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-6">
        <h4 class="w-full text-3xl font-bold text-gray-900">Login</h4>

        <form wire:submit.prevent="submit" class="relative mt-10 w-full space-y-3">
            {{-- <div class=""> --}}
            <div class="relative">
                <x-ts:input wire:model.defer='email' label="Email" placeholder="Email" />
            </div>
            <div class="relative">
                <x-ts:password wire:model.defer='password' label="Password" placeholder="Password" />
            </div>
            <div class="relative">
                <x-ts:button type="submit" loading="submit" class="w-full">
                    <x-icon name="tabler-key" class="size-4" />
                    Login
                </x-ts:button>
            </div>

            <hr class="border-gray-200">
            <div class="flex w-full flex-col">
                <p class="max-w text-sm leading-5 text-gray-600">
                    Belum mempunyai akun ?
                </p>
                <a wire:navigate href="{{ route('register') }}" class="font-medium text-indigo-600 transition duration-150 ease-in-out hover:text-indigo-500 focus:underline focus:outline-none">
                    Registrasi
                </a>
            </div>
        </form>

    </div>
</div>
