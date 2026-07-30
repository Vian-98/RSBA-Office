<div>
    @if(count($logs) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-2">Waktu</th>
                        <th scope="col" class="px-4 py-2">Pegawai</th>
                        <th scope="col" class="px-4 py-2">Tgl Jadwal</th>
                        <th scope="col" class="px-4 py-2">Perubahan Shift</th>
                        <th scope="col" class="px-4 py-2">Diubah Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr class="bg-white border-b">
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-2 font-medium text-gray-900">
                                {{ $log->karyawan->nama ?? '-' }}
                            </td>
                            <td class="px-4 py-2">
                                {{ optional($log->detail)->tanggal?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-2">
                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2 py-0.5 rounded border border-gray-400">
                                    {{ $log->shiftLama->kode ?? 'LIBUR' }}
                                </span>
                                <x-ts:icon name="tabler.arrow-right" class="w-4 h-4 inline mx-1 text-gray-400" />
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded border border-blue-400">
                                    {{ $log->shiftBaru->kode ?? 'LIBUR' }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                {{ $log->pembuat->nama ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <span class="font-medium">Belum ada riwayat!</span> Tidak ada perubahan yang tercatat untuk jadwal ini.
        </div>
    @endif
</div>
