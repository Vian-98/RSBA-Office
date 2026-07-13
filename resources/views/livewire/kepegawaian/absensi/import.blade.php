<div>
    <x-ts:card header="Import Data Absensi">
        <div class="mb-4">
            <x-ts:input type="file" wire:model="file" label="Pilih File Excel" hint="Maksimal 5MB. Format .xls atau .xlsx" />
        </div>

        <div wire:loading wire:target="file" class="mt-4 w-full">
            <x-ts:alert text="Sedang membaca file..." color="info" />
        </div>

        @if($previewData)
            <div class="mt-6 border rounded-lg p-4 bg-gray-50">
                <h3 class="text-lg font-medium mb-4">Preview Deteksi Otomatis</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Periode Awal</p>
                        <p class="font-semibold">{{ \Carbon\Carbon::parse($previewData['periode_awal'])->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Periode Akhir</p>
                        <p class="font-semibold">{{ \Carbon\Carbon::parse($previewData['periode_akhir'])->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Baris</p>
                        <p class="font-semibold">{{ $previewData['total_baris'] }} baris</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Anomali Terdeteksi</p>
                        <p class="font-semibold text-{{ $previewData['anomali'] > 0 ? 'red-600' : 'green-600' }}">
                            {{ $previewData['anomali'] }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-ts:button color="gray" wire:click="$set('previewData', null)">Batal</x-ts:button>
                    <x-ts:button color="primary" wire:click="prosesImport" wire:loading.attr="disabled">
                        Proses ke Staging
                    </x-ts:button>
                </div>
            </div>
        @endif
    </x-ts:card>
</div>
