@props(['compact' => false])

@php($customLogoUrl = \App\Models\AppSetting::siteLogoUrl())
@php(
    $box = $compact
        ? 'max-h-14 max-w-[300px] w-auto h-auto'
        : 'max-h-[300px] max-w-[300px] w-auto h-auto'
)

@if ($customLogoUrl)
    <img
        src="{{ $customLogoUrl }}"
        alt="{{ config('app.name') }}"
        {{ $attributes->merge(['class' => 'block shrink-0 object-contain object-left '.$box]) }}
        loading="eager"
        decoding="async"
    >
@else
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 40"
        fill="none"
        role="img"
        focusable="false"
        aria-label="{{ config('app.name') }}"
        {{ $attributes->merge(['class' => 'block shrink-0 '.$box]) }}
    >
        <rect width="40" height="40" x="0" y="0" rx="10" fill="#075985"/>
        <path
            d="M12 26V14h4.2c2.4 0 4 1.5 4 3.6 0 2.1-1.6 3.6-4 3.6H15v4.8H12zm5.9-7.2c0-.9-.6-1.5-1.6-1.5H15v3h1.3c1 0 1.6-.6 1.6-1.5zM22 26l5.2-12h3.2L25.2 26H22z"
            fill="#f8fafc"
        />
    </svg>
@endif
