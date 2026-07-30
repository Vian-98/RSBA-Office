<div class="flex flex-col gap-3">
    <livewire:Asset.Title :assetBarang="$assetBarang" :key="'title' . $assetBarang->id" />


    <ol class="relative border-s border-gray-200 dark:border-gray-700">
        @foreach ($logs as $item)
            <li class="mb-10 ms-4">
                <div class="{{ $loop->first ? 'bg-primary-500' : 'bg-gray-200' }} absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full border border-white dark:border-gray-900 dark:bg-gray-700">
                </div>
                <time class="{{ $loop->first ? 'text-primary-500' : 'text-gray-400' }} mb-1 font-semibold leading-none dark:text-gray-500">
                    {{ $item->created_at }}
                </time>

                <h3 class="text-sm italic text-gray-500 dark:text-white">User : {{ $item->user->karyawan->nama }}</h3>
                <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                    {!! $item->keterangan !!}
                </p>
            </li>
        @endforeach
    </ol>


</div>
