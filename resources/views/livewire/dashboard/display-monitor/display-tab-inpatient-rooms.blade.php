<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Room List Table -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Daftar Ruangan Custom</h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 text-xs font-semibold text-gray-400 uppercase">
                        <th class="pb-3">Kode</th>
                        <th class="pb-3">Nama Ruangan</th>
                        <th class="pb-3">Lokasi (Gedung/Lantai)</th>
                        <th class="pb-3 text-center">Beds (Total/Terisi/Sisa)</th>
                        <th class="pb-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-600 dark:text-gray-300">
                    @forelse($inpatientRooms as $room)
                        <tr class="border-b border-gray-50 dark:border-gray-700/30 hover:bg-gray-50/50 dark:hover:bg-gray-900/10">
                            <td class="py-3 font-mono font-bold">{{ $room['room_code'] }}</td>
                            <td class="py-3 font-semibold text-gray-800 dark:text-white">{{ $room['name'] }}</td>
                            <td class="py-3">{{ $room['building'] }} - Lantai {{ $room['floor'] }}</td>
                            <td class="py-3 text-center">
                                <span class="font-bold text-gray-800 dark:text-white">{{ $room['bed_total'] }}</span> / 
                                <span class="text-rose-500 font-bold">{{ $room['bed_occupied'] }}</span> / 
                                <span class="text-emerald-500 font-bold">{{ $room['bed_available'] }}</span>
                            </td>
                            <td class="py-3 text-right space-x-2">
                                <button wire:click="editInpatientRoom('{{ $room['id'] }}')" class="text-sky-500 hover:text-sky-600 font-semibold text-xs">Edit</button>
                                <button onclick="confirm('Apakah Anda yakin ingin menghapus ruangan ini?') || event.stopImmediatePropagation()" wire:click="deleteInpatientRoom('{{ $room['id'] }}')" class="text-rose-500 hover:text-rose-600 font-semibold text-xs">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-400 italic">Belum ada ruangan custom terdaftar. Silakan buat di form sebelah kanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6">
        <h3 class="text-md font-bold text-gray-800 dark:text-white mb-4">
            {{ $editingRoomId ? 'Edit Ruangan' : 'Tambah Ruangan Baru' }}
        </h3>
        
        <form wire:submit.prevent="saveInpatientRoom" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1" for="roomCode">Kode Ruangan</label>
                <input wire:model="roomCode" type="text" id="roomCode" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500" placeholder="MISAL: R-VIP-101" required>
                @error('roomCode') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1" for="roomName">Nama Ruangan</label>
                <input wire:model="roomName" type="text" id="roomName" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500" placeholder="MISAL: Ruang Cendrawasih A" required>
                @error('roomName') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1" for="roomBuilding">Gedung</label>
                    <input wire:model="roomBuilding" type="text" id="roomBuilding" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500" placeholder="Gedung Melati" required>
                    @error('roomBuilding') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1" for="roomFloor">Lantai</label>
                    <input wire:model="roomFloor" type="text" id="roomFloor" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500" placeholder="3" required>
                    @error('roomFloor') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1" for="roomBedTotal">Total Kasur</label>
                    <input wire:model="roomBedTotal" type="number" id="roomBedTotal" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500" min="0" required>
                    @error('roomBedTotal') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1" for="roomBedOccupied">Kasur Terisi</label>
                    <input wire:model="roomBedOccupied" type="number" id="roomBedOccupied" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 dark:bg-gray-900 dark:border-gray-700 rounded-lg text-sm text-gray-800 dark:text-white focus:outline-none focus:border-sky-500" min="0" required>
                    @error('roomBedOccupied') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold transition duration-150">
                    {{ $editingRoomId ? 'Perbarui' : 'Buat Ruangan' }}
                </button>
                @if($editingRoomId)
                    <button type="button" wire:click="resetRoomForm" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold transition duration-150">
                        Batal
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
