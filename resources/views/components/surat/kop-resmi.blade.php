@props(['compact' => false])

@php
    $logoUrl = file_exists(public_path('logo-surat-rsba.jpg')) 
        ? asset('logo-surat-rsba.jpg') 
        : (($rs && $rs->logo) ? asset('storage/' . $rs->logo) : asset('logo-fallback.png'));
@endphp

<div style="text-align: center; margin-bottom: 0; padding: 0;">
    <img src="{{ $logoUrl }}" alt="RS BINTANG AMIN" style="width: 5.54cm; height: 2.75cm; object-fit: contain; margin: 0 auto; display: block;">
</div>
