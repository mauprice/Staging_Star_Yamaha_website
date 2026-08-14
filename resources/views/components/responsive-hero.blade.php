@props([
    'mobile' => null,
    'tablet' => null,
    'desktop' => null,
    'alt' => '',
    'priority' => false,
])

{{--
    Renders the three crops the Yamaha API already composes per banner
    (mobile/tablet/desktop) via <picture>, instead of forcing the desktop
    crop through object-cover at every viewport width.
--}}
@if($desktop)
<picture>
    @if($mobile)<source media="(max-width: 767px)" srcset="{{ $mobile }}">@endif
    @if($tablet)<source media="(max-width: 1023px)" srcset="{{ $tablet }}">@endif
    <img src="{{ $desktop }}" alt="{{ $alt }}"
         @if($priority) loading="eager" fetchpriority="high" @else loading="lazy" @endif
         {{ $attributes->merge(['class' => 'absolute inset-0 w-full h-full object-cover']) }}>
</picture>
@endif
