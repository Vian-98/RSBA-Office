<!-- TAB: DAFTAR POLI -->
@if($activeTab === 'poli')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Tambah/Edit Poli -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700/50">
            <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4">
                {{ $editingPoliId ? 'Edit Poliklinik' : 'Tambah Poliklinik' }}
            </h3>
            <form wire:submit="savePoli" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Kode Poli</label>
                    <input type="text" wire:model="poliCode" placeholder="POLI-UMUM"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
                    @error('poliCode') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Nama Poli</label>
                    <input type="text" wire:model="poliName" placeholder="Poliklinik Umum"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
                    @error('poliName') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-xl transition">
                        {{ $editingPoliId ? 'Simpan Perubahan' : 'Tambah Poli' }}
                    </button>
                    @if($editingPoliId)
                        <button type="button" wire:click="resetPoliForm"
                            class="px-4 py-2.5 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-xl dark:text-gray-400 dark:border-gray-700">
                            Batal
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Poli -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/50 overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700/50">
                <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Daftar Poliklinik</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kode</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Nama</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Dokter</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($polyclinics as $poli)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition">
                                <td class="px-5 py-3 font-mono font-bold text-violet-600 dark:text-violet-400">{{ $poli['code'] }}</td>
                                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ $poli['name'] }}</td>
                                <td class="px-5 py-3 text-center text-gray-500 font-semibold">{{ count($poli['doctors'] ?? []) }}</td>
                                <td class="px-5 py-3 text-right space-x-2">
                                    <button wire:click="editPoli('{{ $poli['id'] }}')" class="text-xs text-sky-600 hover:text-sky-800 font-semibold dark:text-sky-400">Edit</button>
                                    <button wire:click="deletePoli('{{ $poli['id'] }}')" wire:confirm="Hapus poli ini beserta seluruh dokternya?" class="text-xs text-rose-600 hover:text-rose-800 font-semibold dark:text-rose-400">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-400">Belum ada poliklinik terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
