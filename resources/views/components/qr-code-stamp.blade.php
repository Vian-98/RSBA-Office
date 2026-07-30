@props(['hash' => null, 'size' => 4, 'class' => ''])

@php
    $qrService = app(\App\Services\QrGeneratorService::class);
    $url = $hash ? $qrService->getVerificationUrl($hash) : null;
    $qrImage = $url ? $qrService->generateQrPngBase64($url, $size, $size) : null;
@endphp

@if ($qrImage)
    <div class="flex flex-col items-center justify-center text-center {{ $class }}">
        <img src="data:image/png;base64,{{ $qrImage }}" alt="QR Code Legalitas Dokumen RSBA" class="w-24 h-24 object-contain shadow-sm border border-slate-200 p-1 bg-white rounded" />
        <span class="text-[9px] font-mono text-slate-500 mt-1 uppercase tracking-tight">Dokumen Sah Sistem RSBA</span>
    </div>
@else
    <div class="flex flex-col items-center justify-center text-center p-2 border border-dashed border-amber-300 bg-amber-50 rounded text-amber-700 text-[10px] font-medium {{ $class }}">
        <span>DRAF / BELUM DI-ACC FULL</span>
        <span class="text-[8px] text-amber-500">QR Code Legalitas Belum Diterbitkan</span>
    </div>
@endif
