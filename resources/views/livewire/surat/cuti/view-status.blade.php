<div class="flex flex-col gap-2">
    <span class="text-lg font-semibold text-primary-500">{{ $suratCuti->karyawan?->nama }}</span>
    <div class="text-sm">
        <span>No. Surat : {{ $suratCuti->no_surat }}</span>

        <ul class="mt-3 list-disc space-y-2 ps-4">
            @foreach ($suratCutiApproval as $item)
                <li>
                    {{ $item->karyawan->nama }}
                    <span style="color: {{ $item->status->colorHex() }}; border-color:{{ $item->status->colorHex() }}" class="ms-2 rounded-md border px-1 text-[10px]">
                        {{ $item->status->nama() }}
                    </span>
                    <div class="text-sm italic">
                        <span>{{ $item->keterangan ?? null }}</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
