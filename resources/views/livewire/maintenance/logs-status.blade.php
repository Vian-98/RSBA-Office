<div class="flex flex-col gap-2">
    <div class="flex flex-col gap-2">


        <ol class="relative border-s border-gray-200 dark:border-gray-700">
            {{-- permintaan --}}
            <li class="mb-10 ms-4">
                <div class="absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full border border-white bg-gray-200 dark:border-gray-900 dark:bg-gray-700">
                </div>
                <time class="mb-1 text-sm font-normal leading-none text-gray-400 dark:text-gray-500">
                    {{ $request->created_at }}
                </time>

                <h3 class="font-semibold text-gray-600 dark:text-white">Permintaan dibuat oleh {{ $request->user_request ?? '-' }}</h3>
                <span class="text-sm font-normal text-gray-400">{{ $request->ket_reject ?? '-' }}</span>
            </li>

            @if ($request->user_verify)
                <li class="mb-10 ms-4">
                    <div class="absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full border border-white bg-gray-200 dark:border-gray-900 dark:bg-gray-700">
                    </div>
                    <time class="mb-1 text-sm font-normal leading-none text-gray-400 dark:text-gray-500">
                        {{ $request->updated_at }}
                    </time>

                    <h3 class="font-semibold text-gray-600 dark:text-white">Permintaan {{ $request->status }} oleh {{ $request->user_verify }}</h3>
                </li>
            @endif


            {{-- jadwal --}}
            @if ($jadwal)
                <li class="mb-10 ms-4">
                    <div class="absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full border border-white bg-gray-200 dark:border-gray-900 dark:bg-gray-700">
                    </div>
                    <time class="mb-1 text-sm font-normal leading-none text-gray-400 dark:text-gray-500">
                        {{ $jadwal->created_at }}
                    </time>

                    <h3 class="font-semibold text-gray-600 dark:text-white">Jadwal maintenance telah dibuat oleh {{ $request->user_verify }}.</h3>
                    <span class="text-sm font-normal text-gray-400">Dijadwalkan maintenance pada tanggal {{ $jadwal->tanggal }}</span>
                </li>
            @endif

            @if ($work)
                <li class="mb-10 ms-4">
                    <div class="absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full border border-white bg-gray-200 dark:border-gray-900 dark:bg-gray-700">
                    </div>
                    <time class="mb-1 text-sm font-normal leading-none text-gray-400 dark:text-gray-500">
                        {{ $work->mulai }}
                    </time>

                    <h3 class="font-semibold text-gray-600 dark:text-white">Maintenance dimulai oleh {{ $work->user_mulai }}.</h3>
                </li>


                @if ($work->selesai)
                    <li class="mb-10 ms-4">
                        <div class="absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full border border-white bg-gray-200 dark:border-gray-900 dark:bg-gray-700">
                        </div>
                        <time class="mb-1 text-sm font-normal leading-none text-gray-400 dark:text-gray-500">
                            {{ $work->selesai }}
                        </time>

                        <h3 class="font-semibold text-gray-600 dark:text-white">Maintenance selesai oleh {{ $work->user_selesai }}.</h3>
                        <p>{{ $work->catatan ?? '-' }}</p>
                    </li>
                @endif
            @endif

        </ol>

    </div>

    <div class="mt-4 flex justify-end">
        <x-ts:button sm outline color="gray" x-on:click="$dispatch('close-modal', { id: 'modal-maintenance-status' })">
            Tutup
        </x-ts:button>
    </div>
</div>
