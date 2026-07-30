<div class="flex flex-row gap-2 lg:flex-col">
    {{-- selectedBabNama= `{{ $item->babs->nama }}` --}}
    @php
        $stats = $this->babs();
    @endphp

    {{-- Status Element By Chapter --}}
    <div
        class="border-{{ $stats['color_berkas'] }}-300 bg-{{ $stats['color_berkas'] }}-50 hover:border-{{ $stats['color_berkas'] }}-500 hover:bg-{{ $stats['color_berkas'] }}-300 flex flex-1 cursor-pointer flex-col rounded-md border p-2 shadow-md hover:-translate-y-1 hover:shadow-lg">

        <div class="flex flex-1 items-center justify-center">
            <span class="text-{{ $stats['color_berkas'] }}-500 align-text-bottom text-[36px] font-medium uppercase">{{ $stats['elements_with_files_count'] }}
                <span class="text-[20px]">/ {{ $stats['elements_count'] }}</span>
            </span>
        </div>
        <div class="text-{{ $stats['color_berkas'] }}-400 flex flex-col text-left text-[10px] italic">
            <span>Terupload : {{ $stats['elements_with_files_count'] }} element </span>
            <span>Target : {{ $stats['elements_count'] }} element </span>
        </div>
    </div>

    {{-- Validasi Assesor --}}
    <div class="border-{{ $stats['color_nilai'] }}-300 bg-{{ $stats['color_nilai'] }}-50 flex flex-1 flex-col rounded-md border p-2 shadow-md">
        <div class="flex flex-1 items-center justify-center">
            <span class="text-{{ $stats['color_nilai'] }}-500 text-[36px] font-medium uppercase">{{ $stats['elements_nilai'] }}</span>
        </div>
        <div class="text-{{ $stats['color_nilai'] }}-400 flex flex-col text-left text-[10px] italic">
            <span>Nilai : {{ $stats['elements_nilai'] }} </span>
            <span>TDD : {{ $stats['elements_tdd'] }} </span>
            <span>Target : {{ $stats['elements_target_nilai'] }} </span>
        </div>
    </div>
</div>
