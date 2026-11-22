{{-- Restaurant theme: the Orange theme's header, plus the Demo banner.
     The banner shows on every customer page while content/menu.json says "demo": true
     (php artisan restaurant:import copies that flag into this theme's settings).
     It tells visitors this restaurant is a class demo: orders are not real. Keep it. --}}
@if($theme->demo ?? false)
<div class="demo-banner" role="note">
    <strong>Demo</strong> — orders are not real
</div>
<style>
    .demo-banner { background: #FFF4CC; color: #4A3700; border-bottom: 1px solid #E8D58A;
        font-size: 0.875rem; line-height: 1.4; text-align: center; padding: 6px 12px; }
</style>
@endif
<nav class="navbar navbar-light navbar-top navbar-expand-md py-sm-2 py-md-0">
    <div class="container">
        <a class="navbar-brand" href="{{ page_url('home') }}">
            @if($theme->logo_image)
                <img
                    class="img-logo"
                    alt="{{ setting('site_name') }}"
                    src="{{ media_url($theme->logo_image) }}"
                />
            @elseif($theme->logo_text)
                <span class="text-logo">{{ $theme->logo_text }}</span>
            @else
                <img
                    class="img-logo"
                    alt="{{ $site_name }}"
                    src="{{ $site_logo !== 'no_photo.png'
                        ? media_thumb($site_logo)
                        : asset('vendor/igniter-orange/images/favicon.ico') }}"
                />
            @endif
        </a>
        <button
            class="navbar-toggler border-0"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarMainHeader"
            aria-controls="navbarMainHeader"
            aria-expanded="false"
            aria-label="Toggle navigation"
        ><span class="navbar-toggler-icon"></span></button>

        <div class="justify-content-end collapse navbar-collapse" id="navbarMainHeader">
            <x-igniter-orange::nav code="main-menu"/>
        </div>
    </div>
</nav>
