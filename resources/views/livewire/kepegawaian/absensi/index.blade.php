<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Kontrol Utama Absensi</h2>
        <div class="flex space-x-2">
            <x-ts:button href="{{ route('kepegawaian.absensi.rekap') }}" color="secondary">
                Lihat Rekapitulasi
            </x-ts:button>
            <x-ts:button href="{{ route('kepegawaian.absensi.import') }}" color="primary">
                Import Absensi Baru
            </x-ts:button>
        </div>
    </div>

    <x-ts:card header="Riwayat Import Absensi">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Tanggal Import</th>
                        <th scope="col" class="px-6 py-3">Nama File</th>
                        <th scope="col" class="px-6 py-3">Periode</th>
                        <th scope="col" class="px-6 py-3">Total Baris</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $log->nama_file }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $log->periode_awal ? $log->periode_awal->format('d M Y') : '-' }} - 
                                {{ $log->periode_akhir ? $log->periode_akhir->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $log->total_baris }} baris
                                <div class="text-xs text-gray-400 mt-1">
                                    Matched: {{ $log->baris_matched }} | Unmatched: {{ $log->baris_unmatched }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($log->status === 'selesai')
                                    <x-ts:badge color="green">Selesai</x-ts:badge>
                                @elseif($log->status === 'diunggah')
                                    <x-ts:badge color="yellow">Diunggah / Menunggu</x-ts:badge>
                                @else
                                    <x-ts:badge color="gray">{{ ucfirst($log->status) }}</x-ts:badge>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <x-ts:button href="{{ route('kepegawaian.absensi.rekonsiliasi', $log->id) }}" sm color="primary" variant="outline">
                                    Lihat History / Rekonsiliasi
                                </x-ts:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Belum ada riwayat import absensi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </x-ts:card>
</div>
