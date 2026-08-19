@props([
    'title' => 'RS. Bintang Amin',
    'role' => 'Direktur',
    'name' => 'dr. Rachmawati, MPH',
    'nip' => null,
    'qrCode' => null,
    'date' => null,
    'city' => null,
])

<div style="display: flex; justify-content: flex-end; margin-top: 15px;">
    <div style="text-align: center; min-width: 220px; font-size: 11pt;">
        @if($city && $date)
            <div style="margin-bottom: 2px;">{{ $city }}, {{ $date }}</div>
        @elseif($date)
            <div style="margin-bottom: 2px;">{{ $date }}</div>
        @endif

        @if($title)
            <div style="font-weight: bold;">{{ $title }}</div>
        @endif

        <div style="margin-bottom: 4px;">{{ $role }}</div>

        @if ($qrCode)
            <div style="margin: 6px 0;">
                <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Verifikasi Bank Surat" style="height:75px; width:75px; display:inline-block;">
                <span style="font-size:7pt; color:#555; display:block; margin-top:1px;">Scan verifikasi keabsahan</span>
            </div>
        @else
            <div style="margin-bottom: 75px;"></div>
        @endif

        <div style="font-weight: bold; text-decoration: underline;">{{ $name }}</div>

        @if ($nip)
            <div style="font-family: monospace; font-size: 10pt; margin-top: 2px;">{{ $nip }}</div>
        @endif
    </div>
</div>
