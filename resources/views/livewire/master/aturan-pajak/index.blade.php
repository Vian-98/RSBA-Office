<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center bg-white p-4 rounded-2xl border border-slate-100 shadow-2xs">
        <div>
            <nav class="flex text-xs text-slate-400 font-semibold mb-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <span class="inline-flex items-center">
                            <x-tabler-moneybag class="mr-1.5 h-3.5 w-3.5" />
                            Penggajian
                        </span>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <x-tabler-chevron-right class="h-3 w-3 text-slate-400 mx-1" />
                            <span class="text-slate-650">Aturan Pajak PPh 21</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-lg font-bold text-slate-800">
                Konfigurasi Aturan Pajak PPh 21
            </h1>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button wire:click="changeTab('ptkp')" class="shrink-0 border-b-2 py-3 px-1 text-sm font-semibold transition-all duration-200 {{ $activeTab === 'ptkp' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                <span class="flex items-center gap-2">
                    <x-tabler-id class="h-4.5 w-4.5" />
                    Batas PTKP
                </span>
            </button>
            <button wire:click="changeTab('ter')" class="shrink-0 border-b-2 py-3 px-1 text-sm font-semibold transition-all duration-200 {{ $activeTab === 'ter' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                <span class="flex items-center gap-2">
                    <x-tabler-table class="h-4.5 w-4.5" />
                    Tarif TER (Tarif Efektif Rata-rata)
                </span>
            </button>
            <button wire:click="changeTab('pasal17')" class="shrink-0 border-b-2 py-3 px-1 text-sm font-semibold transition-all duration-200 {{ $activeTab === 'pasal17' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                <span class="flex items-center gap-2">
                    <x-tabler-scale-outline class="h-4.5 w-4.5" />
                    Tarif Progresif (Pasal 17)
                </span>
            </button>
        </nav>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 gap-6">
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            
            <!-- Tab 1: Batas PTKP -->
            @if($activeTab === 'ptkp')
                <div class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-semibold text-slate-700 flex items-center gap-2 mb-1">
                            <x-tabler-shield-dollar class="h-5 w-5 text-indigo-500" />
                            Batas Penghasilan Tidak Kena Pajak (PTKP)
                        </h2>
                        <p class="text-xs text-slate-400">Konfigurasi nominal batas PTKP setahun berdasarkan status wajib pajak karyawan.</p>
                    </div>

                    <form wire:submit.prevent="savePtkp" class="space-y-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach($ptkpForm as $id => $data)
                                <div class="p-4 bg-slate-50/50 border border-slate-100 rounded-2xl space-y-2 hover:border-slate-200 hover:bg-slate-50 transition-all duration-200">
                                    <div class="space-y-0.5">
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Status PTKP</span>
                                        <span class="text-sm font-bold text-slate-700 block">{{ $data['status'] }}</span>
                                    </div>
                                    <div>
                                        <x-ts:input wire:model.defer="ptkpForm.{{ $id }}.nominal_setahun" type="text" prefix="Rp" class="font-semibold text-slate-700" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <x-ts:button type="submit" loading="savePtkp" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm w-full sm:w-auto justify-center px-6">
                                Simpan Batas PTKP
                            </x-ts:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Tab 2: Kategori TER -->
            @if($activeTab === 'ter')
                <div class="space-y-6">
                    <div class="border-b border-slate-100 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base font-semibold text-slate-700 flex items-center gap-2 mb-1">
                                <x-tabler-calculator class="h-5 w-5 text-indigo-500" />
                                Tarif Efektif Rata-rata (TER)
                            </h2>
                            <p class="text-xs text-slate-400">Atur rentang penghasilan bruto bulanan beserta tarif persen pajak bulanan.</p>
                        </div>
                        
                        <!-- TER Kategori Selection -->
                        <div class="inline-flex rounded-xl bg-slate-100 p-0.5 self-start md:self-auto shrink-0">
                            <button wire:click="changeTerCategory('A')" class="rounded-lg px-4 py-1.5 text-xs font-bold transition-all duration-200 {{ $activeTerCategory === 'A' ? 'bg-white text-indigo-600 shadow-2xs' : 'text-slate-500 hover:text-slate-700' }}">
                                Kategori A
                            </button>
                            <button wire:click="changeTerCategory('B')" class="rounded-lg px-4 py-1.5 text-xs font-bold transition-all duration-200 {{ $activeTerCategory === 'B' ? 'bg-white text-indigo-600 shadow-2xs' : 'text-slate-500 hover:text-slate-700' }}">
                                Kategori B
                            </button>
                            <button wire:click="changeTerCategory('C')" class="rounded-lg px-4 py-1.5 text-xs font-bold transition-all duration-200 {{ $activeTerCategory === 'C' ? 'bg-white text-indigo-600 shadow-2xs' : 'text-slate-500 hover:text-slate-700' }}">
                                Kategori C
                            </button>
                        </div>
                    </div>

                    <!-- Category Helper Info -->
                    <div class="p-4 bg-indigo-50/50 border border-indigo-100/50 rounded-2xl text-xs text-indigo-900 leading-relaxed">
                        @if($activeTerCategory === 'A')
                            <span class="font-bold">Kategori A berlaku untuk status PTKP:</span> TK-0 (Tidak Kawin 0 Tanggungan), TK-1, dan K-0 (Kawin 0 Tanggungan).
                        @elseif($activeTerCategory === 'B')
                            <span class="font-bold">Kategori B berlaku untuk status PTKP:</span> TK-2, TK-3, K-1, dan K-2.
                        @else
                            <span class="font-bold">Kategori C berlaku untuk status PTKP:</span> K-3 (Kawin dengan 3 Tanggungan).
                        @endif
                    </div>

                    <form wire:submit.prevent="saveTer" class="space-y-6">
                        <div class="overflow-x-auto rounded-2xl border border-slate-100">
                            <table class="w-full border-collapse text-left text-sm text-slate-500">
                                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-3">Rentang Bruto Bawah</th>
                                        <th class="px-6 py-3">Rentang Bruto Atas</th>
                                        <th class="px-6 py-3 w-48">Tarif Persen</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($terForm as $id => $data)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-3 font-medium text-slate-700">Rp {{ number_format($data['bruto_bawah'], 0, ',', '.') }}</td>
                                            <td class="px-6 py-3 font-medium text-slate-700">
                                                @if($data['bruto_atas'] >= 999999999)
                                                    Tak Terbatas
                                                @else
                                                    Rp {{ number_format($data['bruto_atas'], 0, ',', '.') }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-3">
                                                <x-ts:input wire:model.defer="terForm.{{ $id }}.tarif_persen" type="number" step="0.01" min="0" max="100" suffix="%" class="text-right font-semibold" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <x-ts:button type="submit" loading="saveTer" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm w-full sm:w-auto justify-center px-6">
                                Simpan Tarif TER Kategori {{ $activeTerCategory }}
                            </x-ts:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Tab 3: Tarif Progresif Pasal 17 -->
            @if($activeTab === 'pasal17')
                <div class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base font-semibold text-slate-700 flex items-center gap-2 mb-1">
                            <x-tabler-chart-arrows class="h-5 w-5 text-indigo-500" />
                            Tarif Progresif PPh 21 Pasal 17
                        </h2>
                        <p class="text-xs text-slate-400">Atur rentang Penghasilan Kena Pajak (PKP) tahunan beserta persentase tarif progresifnya (dipakai pada slip bulan Desember / Resign).</p>
                    </div>

                    <form wire:submit.prevent="savePasal17" class="space-y-6">
                        <div class="overflow-x-auto rounded-2xl border border-slate-100">
                            <table class="w-full border-collapse text-left text-sm text-slate-500">
                                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-3">Batas Bawah PKP</th>
                                        <th class="px-6 py-3">Batas Atas PKP</th>
                                        <th class="px-6 py-3 w-48">Tarif Persen</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($pasal17Form as $id => $data)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-3">
                                                <x-ts:input wire:model.defer="pasal17Form.{{ $id }}.pkp_bawah" type="text" prefix="Rp" class="font-semibold text-slate-700" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />
                                            </td>
                                            <td class="px-6 py-3">
                                                <x-ts:input wire:model.defer="pasal17Form.{{ $id }}.pkp_atas" type="text" prefix="Rp" placeholder="Tak Terbatas" class="font-semibold text-slate-700" x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" x-effect="let inp = $el.querySelector('input') || $el; inp.value = (inp.value || '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" />
                                            </td>
                                            <td class="px-6 py-3">
                                                <x-ts:input wire:model.defer="pasal17Form.{{ $id }}.tarif_persen" type="number" step="0.1" min="0" max="100" suffix="%" class="text-right font-semibold" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <x-ts:button type="submit" loading="savePasal17" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-sm w-full sm:w-auto justify-center px-6">
                                Simpan Tarif Progresif
                            </x-ts:button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</div>
