@props(['headers', 'rows', 'striped' => false, 'paginator' => false, 'no' => false, 'headerless' => false])

<div class="scrollbar-hidden w-full overflow-x-auto">
    <table class="border-collapses w-full min-w-full table-auto">

        @if (!$headerless)
            <thead class="text-left text-sm capitalize text-gray-600">
                <tr>
                    @if ($no)
                        <th class="w-[50px] px-4 py-2">No.</th>
                    @endif
                    @foreach ($headers as $index => $header)
                        <th class="{{ $loop->last ? 'text-right' : '' }} px-4 py-2">{{ $header['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody>
            @forelse ($rows as $index => $row)
                @php
                    $rowData = match (true) {
                        is_array($row) => $row,
                        is_object($row) => method_exists($row, 'toArray') ? $row->toArray() : (array) $row,
                        default => (array) $row,
                    };
                @endphp
                <tr @class([
                    'text-sm text-gray-800 border-y border-gray-200 hover:bg-indigo-100/50',
                    'even:bg-gray-200/25' => $striped,
                ]) :key="{{ $index }}">

                    @if ($no)
                        <td class="w-[50px] px-4 py-2">{{ $loop->iteration }}</td>
                    @endif

                    @foreach ($headers as $header)
                        @php
                            $value = $rowData[$header['index']] ?? '-';
                            $format = $header['format'] ?? null;

                            if ($format === 'number') {
                                $decimals = $header['decimals'] ?? 0;
                                $value = is_numeric($value) ? number_format($value, $decimals, ',', '.') : $value;
                            } elseif ($format === 'float') {
                                $value = is_numeric($value) ? number_format($value, 2, ',', '.') : $value;
                            }
                        @endphp
                        <td class="{{ $loop->last ? 'text-right' : '' }} px-4 py-2">
                            {{ $value }}
                        </td>
                    @endforeach

                </tr>
            @empty
                <tr class="border-b border-gray-200 text-left text-sm text-gray-600 even:bg-gray-200/25">
                    <td class="px-4 py-2 text-center italic" colspan="{{ count($headers) + ($no ? 1 : 0) }}">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($paginator)
        <div class="px-4 py-3">
            {{ $paginator->links() }}
        </div>
    @endif
</div>
