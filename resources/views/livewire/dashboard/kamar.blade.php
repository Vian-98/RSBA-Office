<div class="space-y-4">
    <div class="flex flex-col items-center justify-center text-center text-lg sm:text-2xl font-extrabold text-indigo-600 tracking-tight">
        <span>
            DASHBOARD KAPASITAS TEMPAT TIDUR
        </span>
        <span class="text-slate-800 text-sm sm:text-xl font-bold mt-0.5">
            {{ Str::upper($rs->nama) }}
        </span>
    </div>
    <h1 class="flex flex-row items-center justify-center text-xs sm:text-base font-medium text-rose-500">
        {{ Carbon\Carbon::now('Asia/Jakarta')->isoFormat('dddd, D MMM Y - HH:mm:ss') }}
    </h1>

    {{-- interval in 5 minutes (300 = in second) --}}
    <div x-data="{ counter: 600 }" x-init="setInterval(() => {
        counter--;
        if (counter <= 0) {
            $wire.getData();
            counter = 600;
        }
    }, 1000)">

        <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-xs bg-white">
            <table class="min-w-full text-left text-xs sm:text-sm text-slate-700">
                <thead class="border-b border-slate-200 bg-indigo-50/70 font-bold uppercase text-indigo-900">
                    <tr>
                        <th scope="col" class="px-3 sm:px-6 py-2.5 sm:py-3">Kelas</th>
                        <th scope="col" class="px-3 sm:px-6 py-2.5 sm:py-3">Kapasitas</th>
                        <th scope="col" class="px-3 sm:px-6 py-2.5 sm:py-3">Tersedia</th>
                        <th scope="col" class="px-3 sm:px-6 py-2.5 sm:py-3">Terisi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kapasitas as $item)
                        <tr class="border-b border-slate-100 transition duration-150 ease-in-out even:bg-slate-50/50 hover:bg-indigo-50/20">
                            <td class="whitespace-nowrap px-3 sm:px-6 py-2.5 sm:py-3 font-semibold text-slate-800">{{ $item['kelas'] }}</td>
                            <td class="whitespace-nowrap px-3 sm:px-6 py-2.5 sm:py-3">{{ $item['kapasitas'] }}</td>
                            <td class="whitespace-nowrap px-3 sm:px-6 py-2.5 sm:py-3 font-semibold text-emerald-600">{{ $item['tersedia'] }}</td>
                            <td class="whitespace-nowrap px-3 sm:px-6 py-2.5 sm:py-3">
                                <x-ts:progress.circle :percent="$item['prosentase']" xs :color="$item['prosentase_color']" :stroke-circle="1" :stroke-percent="2" />
                            </td>
                        </tr>
                    @empty
                        <tr class="border-b border-slate-200">
                            <td colspan="5" class="bg-rose-50 text-center italic text-rose-600 py-4">Tidak ada data
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="flex flex-col sm:flex-row items-center sm:justify-end gap-1 pt-3 text-xs text-slate-500">
            <h3> Data update dalam
                <span class="font-bold text-slate-800" x-text="Math.floor(counter / 60).toString().padStart(2, '0')"></span> :
                <span class="font-bold text-slate-800" x-text="(counter % 60).toString().padStart(2, '0')"></span>
            </h3>
        </div>
    </div>

</div>
