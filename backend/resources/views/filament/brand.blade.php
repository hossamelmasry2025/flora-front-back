@php
    $logo = rescue(fn () => \App\Support\Frontend::media(\App\Models\Setting::get('logo')), null, false);
    $name = rescue(fn () => \App\Models\Setting::get('site_name', 'فلورا'), 'فلورا', false);
@endphp
<div class="flora-brand">
    @if ($logo)
        <img src="{{ $logo }}" alt="{{ $name }}" style="height: 2.25rem; width: auto;">
    @else
        <svg viewBox="0 0 48 48" width="34" height="34" aria-hidden="true">
            <g fill="#b02f59">
                <ellipse cx="24" cy="13" rx="7" ry="10" opacity=".9"/>
                <ellipse cx="24" cy="13" rx="7" ry="10" opacity=".75" transform="rotate(72 24 24)"/>
                <ellipse cx="24" cy="13" rx="7" ry="10" opacity=".6" transform="rotate(144 24 24)"/>
                <ellipse cx="24" cy="13" rx="7" ry="10" opacity=".75" transform="rotate(216 24 24)"/>
                <ellipse cx="24" cy="13" rx="7" ry="10" opacity=".9" transform="rotate(288 24 24)"/>
            </g>
            <circle cx="24" cy="24" r="5" fill="#b8935a"/>
        </svg>
        <span class="flora-brand-name">{{ $name }}</span>
    @endif
</div>
