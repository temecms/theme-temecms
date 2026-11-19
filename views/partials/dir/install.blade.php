{{-- The install steps that open under an item's header: the admin's steps beside the Composer commands, each with Copy. Given $listing and $isTheme. --}}
<div class="install-panel" id="install-panel" data-install-panel>
    <button type="button" class="close-x" data-install-close hidden aria-label="{{ __('Close the install steps') }}">@include('theme::partials.dir.glyph', ['icon' => 'x'])</button>
    <div>
        <h2>{{ __('In your site’s admin') }}</h2>
        <ol>
            <li>{!! __('Open :path.', ['path' => '<span class="path">'.e($isTheme ? __('Site › Themes › Add theme') : __('Plugins › Add plugin')).'</span>']) !!}</li>
            <li>{!! __('Search for “:name” and choose <b>Install</b>.', ['name' => e($listing['title'])]) !!}</li>
            <li>{{ $isTheme ? __('Preview it, then choose Activate.') : __('Read what it asks for and agree. It is enabled at once.') }}</li>
        </ol>
        <p class="muted small">{{ __('The admin records the request; Teme’s installer adds the package within a minute.') }}</p>
    </div>
    @if ($listing['package'] !== '')
        <div>
            <h2>{{ __('With Composer') }}</h2>
            <div class="cmd"><code>composer require {{ $listing['package'] }}</code></div>
            @if (! $isTheme)
                <p class="muted small">{!! __('Then enable it in :path, or run:', ['path' => '<span class="path">'.e(__('Site › Plugins')).'</span>']) !!}</p>
                <div class="cmd"><code>php artisan teme:plugins:enable {{ $listing['package'] }}</code></div>
            @else
                <p class="muted small">{!! __('Then link its files and activate it in :path:', ['path' => '<span class="path">'.e(__('Site › Themes')).'</span>']) !!}</p>
                <div class="cmd"><code>php artisan teme:themes:link</code></div>
            @endif
        </div>
    @endif
</div>
