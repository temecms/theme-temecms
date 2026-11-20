{{--
    The Plugins or Themes browse page, given $kind (plugins or themes) and the listing's $page: the title line, search,
    the categories with counts (plugins), "Works with Teme", sort, the count, equal cards and page numbers, and, on the first
    view only, three strips. Every listing is in the page; the theme's script filters, sorts and pages them, and without
    it every card shows.
--}}
@include('theme::partials.directory-data')
@php
    $isPlugins = $kind === 'plugins';
    $listings = array_map(fn ($item) => temecms_listing($item), $page->items);
    $count = count($listings);
    $cats = [];
    $labels = [];
    foreach ($listings as $l) {
        foreach ($l['categories'] as $key) {
            $cats[$key] = ($cats[$key] ?? 0) + 1;
            $labels[$key] ??= $l['categoryLabels'][$key];
        }
    }
    // The directory's own categories in its order, empty ones too (plugins); then any other key, by its words.
    $known = $isPlugins ? array_slice(temecms_category_words(), 0, 14, true) : [];
    $others = array_diff_key($cats, $known);
    uksort($others, fn ($a, $b) => strcasecmp($labels[$a], $labels[$b]));
    $cats = array_map(fn ($key) => $cats[$key] ?? 0, array_combine(array_keys($known), array_keys($known))) + $others;
    $labels += $known;
    $versions = temecms_versions_desc(array_merge(...array_map(fn ($l) => $l['works'], $listings ?: [['works' => []]])));
    $byDate = function (string $field) use ($listings): array {
        $rows = array_values(array_filter($listings, fn ($l) => $l[$field] !== null));
        usort($rows, fn ($a, $b) => $b[$field] <=> $a[$field]);

        return array_slice($rows, 0, 3);
    };
    $sorted = $listings;
    usort($sorted, fn ($a, $b) => ($b['updated'] <=> $a['updated']) ?: strcasecmp($a['title'], $b['title']));
    $picked = array_slice(array_values(array_filter($listings, fn ($l) => $l['picked'])), 0, 3);
    $noun = $isPlugins ? __('plugins') : __('themes');
    $submit = (string) ($themeSettings['submit_address'] ?? '');
    $guide = (string) ($themeSettings[$isPlugins ? 'plugin_guide_address' : 'theme_guide_address'] ?? '');
@endphp
<div class="wrap" data-dir="{{ $kind }}" data-noun="{{ $noun }}" data-noun-one="{{ $isPlugins ? __('plugin') : __('theme') }}">
    <div class="page-top">
        <div>
            <h1>{{ $isPlugins ? __('Plugins') : __('Themes') }}</h1>
            <p>{{ $isPlugins ? __('What your Teme site can do, from the Teme team and other authors. Each release is reviewed before it’s listed.') : __('How your Teme site looks. Each release is reviewed before it’s listed.') }}</p>
        </div>
        <div class="end">
            @if ($submit !== '')<a href="{{ $submit }}">{{ $isPlugins ? __('Submit a plugin') : __('Submit a theme') }}</a>@endif
            @if ($guide !== '')<a href="{{ $guide }}">{{ $isPlugins ? __('Writing a plugin') : __('Making a theme') }}</a>@endif
        </div>
    </div>

    <div class="dir-search">
        <div class="field field-search">
            @include('theme::partials.dir.glyph', ['icon' => 'search'])
            <label class="vh" for="dir-q">{{ __('Search the directory') }}</label>
            <input class="input" id="dir-q" type="search" placeholder="{{ $isPlugins ? __('Search plugins by name, author or what they do') : __('Search themes') }}" autocomplete="off" data-dir-q>
            <kbd aria-hidden="true">/</kbd>
        </div>
    </div>

    <div class="dir-layout">
        <aside class="filters" aria-label="{{ __('Filters') }}">
            @if ($isPlugins)
                <div>
                    <h2 id="cat-h">{{ __('Categories') }}</h2>
                    <ul class="cat-list" aria-labelledby="cat-h">
                        <li><button type="button" data-cat="all" aria-pressed="true"><span>{{ __('All plugins') }}</span><span class="n">{{ $count }}</span></button></li>
                        @foreach ($cats as $key => $n)
                            <li><button type="button" data-cat="{{ $key }}" aria-pressed="false"><span>{{ $labels[$key] }}</span><span class="n">{{ $n }}</span></button></li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div>
                <h2><label for="works-sel">{{ __('Works with Teme') }}</label></h2>
                <select class="select" id="works-sel" data-works-sel>
                    <option value="any">{{ __('Any version') }}</option>
                    @foreach ($versions as $v)<option value="{{ $v }}">{{ $loop->first ? __(':v (latest)', ['v' => $v]) : $v }}</option>@endforeach
                </select>
                <p class="filter-note">{{ __('From each package’s own version range, checked at review.') }}</p>
            </div>
        </aside>

        <div>
            <div class="filters-phone">
                @if ($isPlugins)
                    <div><label class="vh" for="cat-sel">{{ __('Category') }}</label><select class="select" id="cat-sel" data-cat-sel>
                        <option value="all">{{ __('All categories') }}</option>
                        @foreach ($cats as $key => $n)<option value="{{ $key }}">{{ $labels[$key] }} ({{ $n }})</option>@endforeach
                    </select></div>
                @endif
                <div><label class="vh" for="works-sel-2">{{ __('Works with Teme') }}</label><select class="select" id="works-sel-2" data-works-sel>
                    <option value="any">{{ __('Any Teme version') }}</option>
                    @foreach ($versions as $v)<option value="{{ $v }}">{{ __('Works with :v', ['v' => $v]) }}</option>@endforeach
                </select></div>
            </div>

            @if ($isPlugins && ($page->pagination->previous ?? null) === null)
                <div class="shelves" data-shelves>
                    @include('theme::partials.dir.shelf', ['key' => 'updated', 'title' => __('Recently updated'), 'rows' => $byDate('updated'), 'see' => 'updated', 'line' => 'updated'])
                    @include('theme::partials.dir.shelf', ['key' => 'new', 'title' => __('New'), 'rows' => $byDate('listed'), 'see' => 'newest', 'line' => 'listed'])
                    @include('theme::partials.dir.shelf', ['key' => 'picked', 'title' => __('Picked by the Teme team'), 'rows' => $picked, 'see' => null, 'line' => 'summary'])
                </div>
            @endif

            @if ($isPlugins)<h2 class="list-h" id="list-h">{{ __('All plugins') }}</h2>@else<h2 class="vh" id="list-h">{{ __('All themes') }}</h2>@endif
            <div class="toolbar">
                <p class="count num" aria-live="polite" data-count>{{ $isPlugins ? trans_choice(':count plugin|:count plugins', $count, ['count' => $count]) : trans_choice(':count theme|:count themes', $count, ['count' => $count]) }}</p>
                <div class="chips" data-chips></div>
                <div class="sort" hidden data-sort-box><label for="sort-sel">{{ __('Sort') }}</label>
                    <select class="select" id="sort-sel" data-sort-sel>
                        <option value="updated">{{ __('Recently updated') }}</option>
                        <option value="newest">{{ __('Newest') }}</option>
                        <option value="name">{{ __('Name') }}</option>
                    </select>
                </div>
            </div>
            <div class="{{ $isPlugins ? 'cards' : 'tcards' }} dir-results" data-results>
                @foreach ($sorted as $listing)
                    @include($isPlugins ? 'theme::partials.dir.card' : 'theme::partials.dir.tcard', ['listing' => $listing])
                @endforeach
            </div>
            <div class="empty" data-empty @if ($count > 0) hidden @endif>
                <p>{{ $isPlugins ? __('No plugin matches.') : __('No theme matches.') }}</p>
                <button type="button" class="btn btn-quiet btn-sm" data-clear hidden>{{ __('Clear the filters') }}</button>
            </div>
            <nav class="pager" aria-label="{{ __('Pages') }}" data-pager hidden></nav>
            @include('theme::partials.pagination', ['pagination' => $page->pagination])
        </div>
    </div>

    <div class="dir-notes">
        <div><h2>{{ $isPlugins ? __('Add your plugin') : __('Add your theme') }}</h2><p>{{ __('Give us the repository address; checks run and a person looks at your first release.') }} @if ($submit !== '')<a href="{{ $submit }}">{{ $isPlugins ? __('Submit a plugin') : __('Submit a theme') }}</a>@endif</p></div>
        <div><h2>{{ $isPlugins ? __('Create a plugin') : __('Create a theme') }}</h2><p>{{ $isPlugins ? __('A plugin is a Composer package with a manifest.') : __('A theme is a Composer package with a manifest.') }} @if ($guide !== ''){{ __('Start with') }} <a href="{{ $guide }}">{{ $isPlugins ? __('Writing your first plugin') : __('Making a theme') }}</a>.@endif</p></div>
        <div><h2>{{ __('How listing works') }}</h2><p>{{ __('Every release is reviewed before it appears here. Premium plugins are listed too; you buy them from their seller.') }}</p></div>
    </div>
</div>
