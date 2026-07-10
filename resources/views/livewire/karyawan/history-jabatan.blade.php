<div>
    @if ($karyawan)
        <h2 class="text-lg font-semibold mb-4">
            {{ $karyawan->nama }}
        </h2>

        <ol class="relative border-s border-gray-200 dark:border-gray-700">
            @foreach ($karyawan->historyjabatan as $jabatan)
                <li class="mb-10 ms-4">
                    <div class="{{ $loop->first ? 'bg-primary-500' : 'bg-gray-200' }} absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full border border-white dark:border-gray-900 dark:bg-gray-700">
                    </div>
                    <time class="{{ $loop->first ? 'text-primary-500' : 'text-gray-400' }} mb-1 text-sm font-normal leading-none dark:text-gray-500">{{ $jabatan->pivot->tgl_mulai }}
                        s/d {{ $jabatan->pivot->tgl_berakhir ?? ' Now' }}
                    </time>

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $jabatan->nama }}</h3>
                </li>
            @endforeach
        </ol>
    @else
        <div class="p-4 text-center text-gray-400">
            Pilih karyawan terlebih dahulu.
        </div>
    @endif
</div>
