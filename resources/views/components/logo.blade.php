@if ($rs && $rs->logo)
    <img {{ $attributes }} src="{{ asset('storage/' . $rs->logo) }}" alt="logo-{{ $rs->singkatan ?? 'RSBA' }}" />
@else
    <img {{ $attributes }} src="{{ asset('favicon.ico') }}" alt="logo-RSBA" />
@endif
