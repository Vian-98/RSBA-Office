<div class="flex h-full w-full flex-col items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-6">


        <h4 class="w-full text-3xl font-bold text-gray-900">Registrasi</h4>
        {{-- <p class="text-lg text-gray-500">Sudah memiliki akun ?</p> --}}

        <form wire:submit.prevent="register" class="relative mt-10 w-full space-y-3" autocomplete="off">
            <div class="relative">
                <x-ts:select.styled wire:model.lazy="karyawan_id" searchable :request="route('api.karyawan.register')" select="label:nama|value:id" placeholder="Cari Nama Pegawai" />
            </div>

            <div class="relative">
                <x-ts:input wire:model.lazy="email" hint="Pastikan email anda aktif." placeholder="Email" />
            </div>

            <div class="relative">
                <x-ts:password wire:model.lazy="password" placeholder="Password" />
            </div>

            <div class="relative">
                <x-ts:password wire:model.lazy="passwordConfirmation" placeholder="Password Konfirmasi" />
            </div>
            <div class="relative">
                <x-ts:button type="submit" loading="register" class="w-full">
                    Registrasi
                </x-ts:button>
            </div>


            <hr class="border-gray-200">
            <div class="flex w-full flex-row">
                <div class="w-1/2">
                    <p class="max-w text-sm leading-5 text-gray-600">
                        Sudah mempunyai akun ?
                    </p>
                    <a wire:navigate href="{{ route('login') }}" class="font-medium text-indigo-600 transition duration-150 ease-in-out hover:text-indigo-500 focus:underline focus:outline-none">
                        Login
                    </a>
                </div>

                {{-- <div class="ml-auto">
                <x-ts:button type="submit" loading="register">
                    Registrasi
                </x-ts:button>
            </div> --}}
            </div>
        </form>
    </div>
</div>
