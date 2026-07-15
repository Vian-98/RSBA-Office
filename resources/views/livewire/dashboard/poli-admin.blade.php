<div class="space-y-6">
    <!-- Tab Navigation -->
    <div class="flex border-b border-gray-200 dark:border-gray-700">
        <button 
            wire:click="setTab('poli')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'poli' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Daftar Poli
        </button>
        <button 
            wire:click="setTab('doctors')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'doctors' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Dokter
        </button>
        <button 
            wire:click="setTab('queue')" 
            class="py-3 px-6 text-sm font-semibold border-b-2 transition duration-150 {{ $activeTab === 'queue' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
        >
            Antrian Hari Ini
        </button>
    </div>

    <!-- Alert Banner -->
    @if($successMessage)
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-2 text-sm dark:bg-emerald-950/20 dark:border-emerald-800/30 dark:text-emerald-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>{{ $successMessage }}</span>
        </div>
    @endif

    @if($errorMessage)
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-2 text-sm dark:bg-rose-950/20 dark:border-rose-800/30 dark:text-rose-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <span>{{ $errorMessage }}</span>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════════
         TAB: DAFTAR POLI
    ═══════════════════════════════════════════════════════════════════════════ --}}
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
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400">
                                            {{ $poli['doctors_count'] ?? count($poli['doctors'] ?? []) }} dokter
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right space-x-2">
                                        <button wire:click="editPoli('{{ $poli['id'] }}')" class="text-xs text-sky-600 hover:text-sky-800 font-semibold dark:text-sky-400">Edit</button>
                                        <button wire:click="deletePoli('{{ $poli['id'] }}')" wire:confirm="Hapus poliklinik ini beserta semua dokter dan antriannya?" class="text-xs text-rose-600 hover:text-rose-800 font-semibold dark:text-rose-400">Hapus</button>
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

    {{-- ═══════════════════════════════════════════════════════════════════════════
         TAB: DOKTER
    ═══════════════════════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'doctors')
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
                    <!-- Form Tambah/Edit Dokter -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700/50">
                        <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4">
                            {{ $editingDoctorId ? 'Edit Dokter' : 'Tambah Dokter' }}
                        </h3>
                        <form wire:submit="saveDoctor" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Nama Dokter</label>
                                <input type="text" wire:model="doctorName" placeholder="dr. Ahmad, Sp.A"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
                                @error('doctorName') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Foto Dokter</label>
                                <input type="file" wire:model="doctorPhoto" accept="image/*"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-600 hover:file:bg-violet-100 dark:text-gray-400 dark:file:bg-violet-900/30 dark:file:text-violet-400" />
                                @error('doctorPhoto') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Spesialisasi</label>
                                <input type="text" wire:model="doctorSpecialty" placeholder="Spesialis Anak"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Urutan Tampil</label>
                                <input type="number" wire:model="doctorSortOrder" min="0"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="checkbox" wire:model="doctorIsActive" id="doctorActive"
                                    class="rounded border-gray-300 text-violet-600 focus:ring-violet-500 dark:border-gray-600 dark:bg-gray-900">
                                <label for="doctorActive" class="text-sm text-gray-600 dark:text-gray-400">Aktif praktik hari ini</label>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit"
                                    class="flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-xl transition">
                                    {{ $editingDoctorId ? 'Simpan Perubahan' : 'Tambah Dokter' }}
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
                            <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Daftar Dokter</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase">Foto</th>
                                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase">Nama</th>
                                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase">Spesialisasi</th>
                                        <th class="text-center px-5 py-3 text-xs font-semibold text-gray-400 uppercase">Urutan</th>
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
                                            <td class="px-5 py-3 text-center text-gray-500">{{ $doc['sort_order'] }}</td>
                                            <td class="px-5 py-3 text-center">
                                                <button wire:click="toggleDoctorActive('{{ $doc['id'] }}')"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold cursor-pointer transition
                                                    {{ $doc['is_active'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                                    {{ $doc['is_active'] ? 'Aktif' : 'Nonaktif' }}
                                                </button>
                                            </td>
                                            <td class="px-5 py-3 text-right space-x-2">
                                                <button wire:click="editDoctor('{{ $doc['id'] }}')" class="text-xs text-sky-600 hover:text-sky-800 font-semibold dark:text-sky-400">Edit</button>
                                                <button wire:click="deleteDoctor('{{ $doc['id'] }}')" wire:confirm="Hapus dokter ini?" class="text-xs text-rose-600 hover:text-rose-800 font-semibold dark:text-rose-400">Hapus</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-5 py-8 text-center text-gray-400">Belum ada dokter di poli ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════════
         TAB: ANTRIAN HARI INI
    ═══════════════════════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'queue')
        <div class="space-y-6">
            <!-- Filter: Poli & Dokter -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700/50">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Pilih Poliklinik</label>
                        <select wire:model.live="queuePoliId" wire:change="loadQueue"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="">— Pilih Poli —</option>
                            @foreach($polyclinics as $poli)
                                <option value="{{ $poli['id'] }}">{{ $poli['code'] }} — {{ $poli['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Filter Dokter</label>
                        <select wire:model.live="queueDoctorId" wire:change="loadQueue"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="">Semua Dokter</option>
                            @php
                                $selectedPoli = collect($polyclinics)->firstWhere('id', $queuePoliId);
                                $availableDoctors = $selectedPoli['doctors'] ?? [];
                            @endphp
                            @foreach($availableDoctors as $doc)
                                <option value="{{ $doc['id'] }}">{{ $doc['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if($queuePoliId)
                <!-- Form Tambah Pasien -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700/50">
                    <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-4">Tambah Pasien ke Antrian</h3>
                    <form wire:submit="addPatient" class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Dokter</label>
                            <select wire:model="queueDoctorId" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                <option value="">— Pilih Dokter —</option>
                                @foreach($availableDoctors as $doc)
                                    <option value="{{ $doc['id'] }}">{{ $doc['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Nama Pasien</label>
                            <input type="text" wire:model="patientName" placeholder="Nama lengkap pasien" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-violet-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
                        </div>
                        <button type="submit"
                            class="bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 px-6 rounded-xl transition">
                            + Tambah
                        </button>
                    </form>
                </div>

                <!-- Tabel Antrian -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/50 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700/50 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Antrian — {{ now()->translatedFormat('l, d F Y') }}</h3>
                        <span class="text-xs text-gray-400">{{ count($queueItems) }} pasien</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase w-16">No.</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Nama Pasien</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Dokter</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Status</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Dipanggil</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                @forelse($queueItems as $item)
                                    <tr class="transition {{ $item['status'] === 'dilayani' ? 'bg-amber-50 dark:bg-amber-900/10' : ($item['status'] === 'terlewat' ? 'opacity-60' : 'hover:bg-gray-50 dark:hover:bg-gray-700/20') }}">
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm
                                                {{ $item['status'] === 'dilayani' ? 'bg-amber-200 text-amber-800 dark:bg-amber-800/30 dark:text-amber-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                                {{ $item['queue_number'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-semibold {{ $item['status'] === 'terlewat' ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $item['patient_name'] }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $item['doctor_name'] ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @switch($item['status'])
                                                @case('menunggu')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Menunggu</span>
                                                    @break
                                                @case('dilayani')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Dilayani</span>
                                                    @break
                                                @case('selesai')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Selesai</span>
                                                    @break
                                                @case('terlewat')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">Terlewat</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td class="px-4 py-3 text-center text-xs text-gray-400">
                                            {{ $item['called_at'] ? \Carbon\Carbon::parse($item['called_at'])->format('H:i') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($item['status'] === 'menunggu')
                                                    <button wire:click="callPatient('{{ $item['id'] }}')" class="px-2.5 py-1 text-xs font-semibold text-amber-600 hover:bg-amber-50 rounded-lg transition dark:text-amber-400 dark:hover:bg-amber-900/20" title="Panggil">
                                                        Panggil
                                                    </button>
                                                    <button wire:click="skipPatient('{{ $item['id'] }}')" class="px-2.5 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition dark:text-rose-400 dark:hover:bg-rose-900/20" title="Lewati">
                                                        Lewati
                                                    </button>
                                                @elseif($item['status'] === 'dilayani')
                                                    <button wire:click="completePatient('{{ $item['id'] }}')" class="px-2.5 py-1 text-xs font-semibold text-emerald-600 hover:bg-emerald-50 rounded-lg transition dark:text-emerald-400 dark:hover:bg-emerald-900/20" title="Selesai">
                                                        Selesai
                                                    </button>
                                                @elseif($item['status'] === 'terlewat')
                                                    <button wire:click="requeuePatient('{{ $item['id'] }}')" class="px-2.5 py-1 text-xs font-semibold text-violet-600 hover:bg-violet-50 rounded-lg transition dark:text-violet-400 dark:hover:bg-violet-900/20" title="Panggil Ulang">
                                                        Panggil Ulang
                                                    </button>
                                                @endif
                                                @if($item['status'] !== 'selesai')
                                                    <button wire:click="deleteQueueItem('{{ $item['id'] }}')" wire:confirm="Hapus antrian ini?" class="px-2.5 py-1 text-xs font-semibold text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition dark:hover:text-rose-400 dark:hover:bg-rose-900/20" title="Hapus">
                                                        ✕
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-8 text-center text-gray-400">Belum ada antrian hari ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
