<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-2xs">
        <div>
            <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                <span class="inline-flex items-center">
                    <x-tabler-moneybag class="mr-1.5 h-3.5 w-3.5 text-slate-400" />
                    Penggajian
                </span>
                <x-tabler-chevron-right class="h-3.5 w-3.5 text-slate-300" />
                <span class="text-indigo-600 font-bold">Tunjangan Golongan</span>
            </div>
            <h1 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight mt-0.5">Master Pengaturan Golongan</h1>
            <p class="text-xs text-slate-500 mt-0.5 leading-normal">Kelola nominal tunjangan dan matrix penentuan golongan karyawan tetap.</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="$set('activeTab', 'tunjangan')" 
                    class="{{ $activeTab === 'tunjangan' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors flex items-center gap-2 focus:outline-none">
                <x-tabler-coin class="h-4.5 w-4.5" />
                Nominal Tunjangan
            </button>
            <button wire:click="$set('activeTab', 'matrix')" 
                    class="{{ $activeTab === 'matrix' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors flex items-center gap-2 focus:outline-none">
                <x-tabler-grid-pattern class="h-4.5 w-4.5" />
                Matrix Golongan (Pendidikan x Masa Kerja)
            </button>
        </nav>
    </div>

    @if($activeTab === 'tunjangan')
        <!-- TAB 1: NOMINAL TUNJANGAN -->
        <div class="grid grid-cols-1 gap-6">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-slate-700 mb-4 flex items-center gap-2">
                    <x-tabler-coin class="h-5 w-5 text-indigo-500" />
                    Tunjangan Golongan (Grade 1 - 15)
                </h2>
                
                <form wire:submit.prevent="saveAllowances" class="space-y-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                        @foreach($golongansList as $gol)
                            <div class="p-3 bg-slate-50/50 border border-slate-100 rounded-xl space-y-1.5 hover:border-slate-200 transition-colors">
                                <span class="text-xs font-semibold text-slate-500 block">Golongan {{ $gol->golongan }}</span>
                                <x-ts:input wire:model.defer="allowances.{{ $gol->golongan }}" type="text" placeholder="0" prefix="Rp" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <x-ts:button type="submit" loading="saveAllowances" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm px-6">
                            Update Tunjangan Golongan
                        </x-ts:button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <!-- TAB 2: MATRIX GOLONGAN -->
        <div class="space-y-6">
            <!-- Main Grid Panel -->
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h2 class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <x-tabler-grid-pattern class="h-5 w-5 text-indigo-500" />
                        Grid Penentuan Golongan (Pendidikan x Masa Kerja)
                    </h2>
                </div>

                <!-- Matrix Table -->
                <div class="w-full overflow-x-auto border border-slate-100 rounded-2xl scrollbar-thin">
                    <table class="w-full border-collapse text-left text-sm text-slate-600">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="p-4 font-semibold text-slate-700 min-w-[180px]">Pendidikan / Masa Kerja</th>
                                @foreach($masaKerjas as $mk)
                                    <th class="p-4 text-center font-semibold text-slate-700 min-w-[100px] border-l border-slate-100/50 relative group">
                                        <div class="flex flex-col items-center justify-center gap-1.5">
                                            <span class="text-xs text-slate-500">Masa Kerja</span>
                                            <span class="text-sm font-bold text-slate-800">{{ $mk }} Th</span>
                                            <!-- Delete button for column -->
                                            <button type="button" 
                                                    wire:confirm="Yakin ingin menghapus kolom masa kerja {{ $mk }} tahun?"
                                                    wire:click="removeColumn({{ $mk }})" 
                                                    class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition-opacity p-0.5 rounded-full hover:bg-rose-50 text-rose-500" 
                                                    title="Hapus kolom ini">
                                                <x-tabler-x class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($kelompoks as $kel)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 font-semibold text-slate-800 flex items-center justify-between gap-3 group/row">
                                        <span>{{ $kel }}</span>
                                        <!-- Delete button for row -->
                                        <button type="button" 
                                                wire:confirm="Yakin ingin menghapus baris kelompok pendidikan '{{ $kel }}'?"
                                                wire:click="removeRow('{{ $kel }}')" 
                                                class="opacity-0 group-hover/row:opacity-100 transition-opacity p-0.5 rounded-full hover:bg-rose-50 text-rose-500" 
                                                title="Hapus baris ini">
                                            <x-tabler-trash class="w-3.5 h-3.5" />
                                        </button>
                                    </td>
                                    @foreach($masaKerjas as $mk)
                                        <td class="p-2 border-l border-slate-100/50">
                                            <div class="flex items-center justify-center">
                                                <input type="number" 
                                                       min="1" 
                                                       max="15" 
                                                       wire:model.defer="matrix.{{ $kel }}.{{ $mk }}" 
                                                       class="w-16 h-9 text-center text-sm font-bold text-slate-800 bg-slate-50/50 border border-slate-200 rounded-xl focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500/25 transition-all" />
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Legend / Notes -->
                <div class="p-4 rounded-2xl bg-amber-50/40 border border-amber-100/50 text-xs text-amber-800 space-y-1.5">
                    <p class="font-bold flex items-center gap-1">
                        <x-tabler-info-circle class="w-4 h-4 text-amber-600" />
                        Aturan Penentuan Bracket (Masa Kerja & Golongan):
                    </p>
                    <ul class="list-disc list-inside pl-1 space-y-0.5 text-amber-700">
                        <li>Nilai sel di dalam tabel menunjukkan tingkat <strong>Golongan/Grade (1 - 15)</strong> untuk karyawan tetap.</li>
                        <li>Sistem menggunakan metode ambang bawah (misal: jika masa kerja karyawan 4 tahun, maka dia masuk ke kolom bracket <strong>3 Tahun</strong> sampai dengan sebelum 6 tahun).</li>
                        <li>Tekan tombol <strong>"Simpan Matrix Golongan"</strong> untuk menyimpan seluruh perubahan angka sel sekaligus.</li>
                    </ul>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <x-ts:button wire:click="saveMatrix" loading="saveMatrix" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm px-6">
                        Simpan Matrix Golongan
                    </x-ts:button>
                </div>
            </div>

            <!-- Side Forms: Add Row & Column -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Column Form -->
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                        <x-tabler-square-plus class="h-4.5 w-4.5 text-indigo-500" />
                        Tambah Kolom Masa Kerja
                    </h3>
                    <p class="text-xs text-slate-400">Tambahkan kolom range masa kerja baru ke dalam matrix (default golongan diisi Grade 15).</p>
                    <form wire:submit.prevent="addColumn" class="flex gap-2">
                        <div class="grow">
                            <x-ts:input wire:model.defer="newMasaKerja" type="number" min="0" max="100" placeholder="Minimal masa kerja (tahun)" suffix="Tahun" />
                        </div>
                        <x-ts:button type="submit" loading="addColumn" class="bg-slate-800 hover:bg-slate-900 text-white shadow-sm font-semibold">
                            Tambah Kolom
                        </x-ts:button>
                    </form>
                </div>

                <!-- Row Form -->
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                        <x-tabler-square-plus class="h-4.5 w-4.5 text-indigo-500" />
                        Tambah Kelompok Pendidikan
                    </h3>
                    <p class="text-xs text-slate-400">Tambahkan kelompok pendidikan (baris baru) ke dalam matrix (contoh: D I / D II).</p>
                    <form wire:submit.prevent="addRow" class="flex gap-2">
                        <div class="grow">
                            <x-ts:input wire:model.defer="newKelompok" type="text" placeholder="Nama kelompok (misal: D II / D III)" />
                        </div>
                        <x-ts:button type="submit" loading="addRow" class="bg-slate-800 hover:bg-slate-900 text-white shadow-sm font-semibold">
                            Tambah Baris
                        </x-ts:button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
