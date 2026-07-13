<div class="flex flex-col gap-4">
    <div class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                Jadwal Kerja: {{ $jadwalKerja->ruangan->nama ?? '-' }}
            </h2>
            <p class="text-sm text-gray-500">
                Periode: {{ date('F', mktime(0, 0, 0, $jadwalKerja->bulan, 1)) }} {{ $jadwalKerja->tahun }}
                <span class="mx-2">•</span>
                Status: 
                <x-ts:badge :color="$jadwalKerja->status->color()" text="{{ $jadwalKerja->status->nama() }}" />
            </p>
        </div>
        <div class="flex gap-2">
            <x-ts:button outline href="{{ route('kepegawaian.jadwal-kerja.index') }}" icon="tabler.arrow-left">Kembali</x-ts:button>
            <x-ts:button outline color="secondary" x-on:click="$dispatch('open-modal', {id:'modal-riwayat'}); $dispatch('load-riwayat', {jadwalKerjaId: {{ $jadwalKerja->id }}})" icon="tabler.history">Riwayat</x-ts:button>
            
            @if(!$isReadOnly)
                <x-ts:button outline color="primary" wire:click="save" loading="save" icon="tabler.device-floppy">Simpan Draf</x-ts:button>
                @if($jadwalKerja->status === \App\Enums\StatusJadwalKerja::DRAFT)
                    <x-ts:button color="success" wire:click="publish" icon="tabler.send">Publikasikan</x-ts:button>
                @endif
            @endif
        </div>
    </div>

    {{-- Modal Riwayat --}}
    <x-filament::modal id="modal-riwayat" width="4xl" :autofocus="false">
        <x-slot name="heading">
            Riwayat Perubahan Jadwal
        </x-slot>
        <livewire:Kepegawaian.JadwalKerja.Riwayat />
    </x-filament::modal>

    {{-- Wrapper dengan batasan tinggi maks agar scrollbar horizontal selalu terlihat di bawah area viewport --}}
    <div class="rounded-lg bg-white p-4 shadow-sm w-full overflow-auto max-h-[70vh] border border-gray-200">
        <form wire:submit.prevent="save">
            <table class="min-w-max w-full border-collapse border border-gray-200 text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        {{-- Header Karyawan: Sticky di atas (z-30) dan di kiri (left-0) --}}
                        <th class="border border-gray-200 p-2 text-left sticky top-0 left-0 z-30 bg-gray-100 min-w-[200px] w-[200px] max-w-[200px]">Karyawan</th>
                        
                        {{-- Header Kategori: Sticky di atas (z-30) dan di kiri (left-[200px]) --}}
                        <th class="border-y border-gray-200 border-l border-r-2 border-r-slate-300 p-2 text-center sticky top-0 left-[200px] z-30 bg-gray-100 min-w-[100px] w-[100px] max-w-[100px]">Kategori</th>
                        
                        @foreach($dates as $date)
                            {{-- Header Tanggal: Sticky di atas saja (z-20) --}}
                            <th class="border border-gray-200 p-2 text-center sticky top-0 z-20 bg-gray-100 {{ $date->isWeekend() ? 'text-red-500 bg-red-50' : '' }} min-w-[85px]">
                                <div>{{ $date->format('D') }}</div>
                                <div>{{ $date->format('d') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($karyawans as $karyawan)
                        <tr class="hover:bg-gray-50">
                            {{-- Kolom Karyawan: Sticky di kiri (left-0, z-10) --}}
                            <td class="border border-gray-200 p-2 sticky left-0 z-10 bg-white font-medium text-gray-800 min-w-[200px] w-[200px] max-w-[200px] truncate" title="{{ $karyawan['nama'] }}">
                                {{ $karyawan['nama'] }}
                            </td>
                            
                            {{-- Kolom Kategori: Sticky di kiri (left-[200px], z-10) dengan pembatas garis tebal --}}
                            <td class="border-y border-gray-200 border-l border-r-2 border-r-slate-300 p-2 text-center text-xs text-gray-500 bg-white sticky left-[200px] z-10 min-w-[100px] w-[100px] max-w-[100px] truncate">
                                {{ $karyawan['kategori'] }}
                            </td>
                            
                            @for($d = 1; $d <= count($dates); $d++)
                                @php
                                    $detail = $karyawan['details'][$d] ?? null;
                                @endphp
                                <td class="border border-gray-200 p-1 min-w-[85px] align-top {{ $dates[$d-1]->isWeekend() ? 'bg-red-50/30' : '' }}">
                                    @if(!$detail)
                                        <div class="w-full text-center p-1 rounded text-xs font-semibold text-gray-400 bg-gray-50 border border-dashed border-gray-300">
                                            N/A
                                        </div>
                                    @elseif($isReadOnly)
                                        <div class="w-full text-center p-1 rounded text-xs font-semibold"
                                             style="background-color: {{ $detail->shift->warna ?? '#f3f4f6' }}">
                                            {{ $detail->shift->kode ?? 'LIBUR' }}
                                        </div>
                                    @else
                                        <select wire:model.defer="state.{{ $detail->id }}" class="w-full text-xs rounded border-gray-300 focus:border-primary-500 focus:ring-primary-500 p-1">
                                            <option value="">LIBUR</option>
                                            @foreach($shiftOptions as $shift)
                                                <option value="{{ $shift['id'] }}" title="{{ $shift['jam_masuk'] }} - {{ $shift['jam_keluar'] }}">{{ $shift['nama'] }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
    </div>
</div>
