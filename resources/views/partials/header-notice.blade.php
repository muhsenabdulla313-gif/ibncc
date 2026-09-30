@php
    $headerNotice = \App\Models\HeaderNotice::where('is_active', true)->latest()->first();
@endphp

@if($headerNotice)
<span class="header-notice-track">
    <span class="header-notice-marquee">
        <span class="header-notice-text">{{ $headerNotice->text }}</span>
        <span class="header-notice-text" aria-hidden="true">{{ $headerNotice->text }}</span>
    </span>
</span>
@endif