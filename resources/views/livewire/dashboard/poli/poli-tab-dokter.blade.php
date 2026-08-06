<div class="space-y-6">
    <!-- Pilih Poli -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700/50">
        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Pilih Poliklinik</label>
        <select wire:model.live="selectedPoliId" wire:change="loadDoctors"
            class="w-full max-w-md px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
            <option value="">— Pilih Poli —</option>
            @foreach($polyclinics as $poli)
                <option value="{{ $poli['id'] }}">{{ $poli['code'] }} — {{ $poli['name'] }}</option>
            @endforeach
        </select>
    </div>

    @if($selectedPoliId)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Pilih Dokter dari Master SDM -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700/50">
                <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4">
                    {{ $editingDoctorId ? 'Edit Dokter' : 'Tugaskan Dokter (Master SDM)' }}
                </h3>
                <form wire:submit="saveDoctor" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Pilih Dokter (Master SDM)</label>
                        <select wire:model.live="selectedMasterDoctorId" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="">— Pilih Dokter —</option>
                            @foreach($masterDoctors as $mDoc)
                                <option value="{{ $mDoc['id'] }}">{{ $mDoc['name'] }} ({{ $mDoc['specialty'] }})</option>
                            @endforeach
                        </select>
                        @error('selectedMasterDoctorId') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    @if($doctorName)
                        <div class="p-3 bg-violet-50 dark:bg-violet-950/20 rounded-xl border border-violet-100 dark:border-violet-900/30 text-xs space-y-1">
                            <p class="font-bold text-violet-800 dark:text-violet-300">{{ $doctorName }}</p>
                            <p class="text-gray-500 dark:text-gray-400">Spesialisasi: {{ $doctorSpecialty }}</p>
                            <p class="text-gray-400 dark:text-gray-500 font-mono">Kode Dokter / NIP: {{ $doctorCode }}</p>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Foto Dokter (Opsional Override)</label>
                        <input type="file" wire:model="doctorPhoto" accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-600 hover:file:bg-violet-100 dark:text-gray-400 dark:file:bg-violet-900/30 dark:file:text-violet-400" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Urutan Tampil</label>
                        <input type="number" wire:model="doctorSortOrder" min="0"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="doctorIsActive" id="doctorActive"
                            class="rounded border-gray-300 text-violet-600 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-900">
                        <label for="doctorActive" class="text-sm text-gray-600 dark:text-gray-400 font-medium">Aktif praktik hari ini</label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-xl transition">
                            {{ $editingDoctorId ? 'Simpan Perubahan' : 'Tugaskan Dokter' }}
                        </button>
                        @if($editingDoctorId)
                            <button type="button" wire:click="resetDoctorForm"
                                class="px-4 py-2.5 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-xl dark:text-gray-400 dark:border-gray-700">
                                Batal
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel Dokter -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/50 overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700/50">
                    <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Daftar Dokter Bertugas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase">Foto</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase">Nama Dokter (Master SDM)</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase">Spesialisasi</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-400 uppercase">Status</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($doctors as $doc)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition">
                                    <td class="px-5 py-3">
                                        @if($doc['photo_url'])
                                            <img src="{{ $doc['photo_url'] }}" alt="{{ $doc['name'] }}" class="w-10 h-10 rounded-full object-cover border-2 border-violet-200 dark:border-violet-800">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center text-violet-600 dark:text-violet-400 font-bold text-sm">
                                                {{ strtoupper(substr($doc['name'], 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 font-semibold text-gray-700 dark:text-gray-300">{{ $doc['name'] }}</td>
                                    <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $doc['specialty'] ?? '-' }}</td>
                                    <td class="px-5 py-3 text-center">
                                        <button wire:click="toggleDoctorActive('{{ $doc['id'] }}')"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold cursor-pointer transition
                                            {{ $doc['is_active'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                            {{ $doc['is_active'] ? 'Aktif Praktik' : 'Nonaktif' }}
                                        </button>
                                    </td>
                                    <td class="px-5 py-3 text-right space-x-2">
                                        <button wire:click="editDoctor('{{ $doc['id'] }}')" class="text-xs text-sky-600 hover:text-sky-800 font-semibold dark:text-sky-400">Edit</button>
                                        <button wire:click="deleteDoctor('{{ $doc['id'] }}')" wire:confirm="Hapus dokter ini?" class="text-xs text-rose-600 hover:text-rose-800 font-semibold dark:text-rose-400">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-gray-400">Belum ada dokter bertugas di poli ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
