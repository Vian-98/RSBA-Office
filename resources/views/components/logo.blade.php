@if ($rs?->logo)
    <img {{ $attributes }} src="{{ asset('storage/' . $rs->logo) }}" alt="logo-{{ $rs->singkatan ?? 'RSBA' }}" />
@else
    <img {{ $attributes }} src="{{ asset('logo-fallback.png') }}" alt="logo-RSBA" />
@endif
