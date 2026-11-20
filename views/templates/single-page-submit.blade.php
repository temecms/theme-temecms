{{--
    Submit a plugin or theme: the page's title and line, the form (which the temecms-site plugin receives and turns into a
    pull request on the directory's repository), and beside it what happens next and the rules in brief. The plugin answers
    back to this page with ?submitted=… or ?error=…, which the theme's script shows.
--}}
@extends('theme::layouts.base')

@section('main')
    @php
        $dirUrl = (string) ($themeSettings['plugins_address'] ?? '');
        $rulesUrl = (string) ($themeSettings['rules_address'] ?? '');
        $repo = 'https://github.com/temecms/directory';
    @endphp
    <div class="dx"><div class="wrap">
        <nav class="crumbs" aria-label="{{ __('Breadcrumb') }}">@if ($dirUrl !== '')<a href="{{ $dirUrl }}">{{ __('Plugins') }}</a><span aria-hidden="true">/</span>@endif<span aria-current="page">{{ __('Submit') }}</span></nav>
        <div class="page-top submit-top">
            <div>
                <h1>{{ $page->title }}</h1>
                @if ($page->standfirst !== null && $page->standfirst !== '')<p>{{ $page->standfirst }}</p>@endif
            </div>
        </div>

        <div class="submit-layout">
            <div>
                <div class="done-box" role="status" data-submit-done hidden>
                    <b data-done-title></b><span data-done-line></span>
                </div>
                <div class="err-box" role="alert" data-submit-error hidden></div>
                <form class="form" method="post" action="/temecms-site/submit-package" data-submit-form>
                    <div class="fld">
                        <label for="f-repo">{{ __('Repository address') }}</label>
                        <input class="input" id="f-repo" name="repository" type="url" inputmode="url" placeholder="https://github.com/you/your-plugin" required pattern="https://github\.com/[^/]+/[^/]+/?" aria-describedby="f-repo-hint f-repo-err">
                        <p class="hint" id="f-repo-hint">{!! __('The repository must have a release tag, :plugin (or :theme), a README and a licence.', ['plugin' => '<code>teme-plugin.json</code>', 'theme' => '<code>teme-theme.json</code>']) !!}</p>
                        <p class="err" id="f-repo-err" data-err="repository" hidden>{{ __('Give a GitHub address like https://github.com/you/your-plugin.') }}</p>
                    </div>
                    <fieldset class="fld">
                        <legend>{{ __('Kind') }}</legend>
                        <div class="radios"><label><input type="radio" name="kind" value="plugin" checked>{{ __('Plugin') }}</label><label><input type="radio" name="kind" value="theme">{{ __('Theme') }}</label></div>
                    </fieldset>
                    <div class="fld">
                        <label for="f-cat">{{ __('Main category') }}</label>
                        <select class="select" id="f-cat" name="category" aria-describedby="f-cat-hint f-cat-err">@include('theme::partials.dir.categories')</select>
                        <p class="hint" id="f-cat-hint">{{ __('Up to three categories come from your manifest; this one decides where reviewers file it.') }}</p>
                        <p class="err" id="f-cat-err" data-err="category" hidden>{{ __('Choose the category that fits it best.') }}</p>
                    </div>
                    <div class="fld">
                        <label for="f-mail">{{ __('Contact email') }}</label>
                        <input class="input" id="f-mail" name="contact" type="email" autocomplete="email" placeholder="you@example.com" aria-describedby="f-mail-hint f-mail-err">
                        <p class="hint" id="f-mail-hint">{{ __('Only reviewers see it. Your public profile is your GitHub account.') }}</p>
                        <p class="err" id="f-mail-err" data-err="contact" hidden>{{ __('Give an email address reviewers can write to.') }}</p>
                    </div>
                    <div class="hp" aria-hidden="true"><label for="f-website">{{ __('Leave this empty') }}</label><input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                    <label class="check"><input type="checkbox" name="agree" value="1" required id="f-agree" aria-describedby="f-agree-err"><span>{!! __('I have read the :rules and my package follows them.', ['rules' => '<a href="#rules">'.e(__('directory’s rules')).'</a>']) !!}</span></label>
                    <p class="err" id="f-agree-err" data-err="agree" hidden>{{ __('Agree to the rules to submit.') }}</p>
                    <div class="form-foot">
                        <button type="submit" class="btn btn-primary">{{ __('Submit for review') }}</button>
                        <small>@include('theme::partials.dir.glyph', ['icon' => 'branch']){{ __('Opens a pull request on GitHub for you') }}</small>
                    </div>
                </form>
                <p class="muted small prefer-gh">{{ __('Prefer GitHub?') }} <a href="{{ $repo }}">{{ __('Open a pull request to temecms/directory') }}</a> {{ __('that adds one line with your repository address. The form above writes the same line.') }}</p>
            </div>

            <aside>
                <section class="aside-block" aria-labelledby="next-h">
                    <h2 id="next-h">{{ __('What happens next') }}</h2>
                    <ol class="steps">
                        <li><div><b>{{ __('Automatic checks') }}</b><p>{{ __('We read your plugin’s details, README, licence and screenshots, install it on a fresh Teme site, and list the permissions it asks for. The results appear on your pull request.') }}</p></div></li>
                        <li><div><b>{{ __('Review') }}</b><p>{{ __('Your first submission is reviewed before it’s listed. If anything needs changing, we’ll say so on the pull request.') }}</p></div></li>
                        <li><div><b>{{ __('Listed') }}</b><p>{!! __('Once approved, your plugin appears here and in :path on every Teme site.', ['path' => '<span class="path">'.e(__('Plugins › Add plugin')).'</span>']) !!}</p></div></li>
                        <li><div><b>{{ __('New versions') }}</b><p>{{ __('Tag a release on GitHub. It’s checked and listed on its own. A version that asks for more permissions is reviewed again first.') }}</p></div></li>
                    </ol>
                    <div class="status-words" role="list" aria-label="{{ __('Words used for a submission’s state') }}"><span role="listitem" class="tag">{{ __('Submitted') }}</span><span role="listitem" class="tag warn">{{ __('In review') }}</span><span role="listitem" class="tag ok">{{ __('Listed') }}</span><span role="listitem" class="tag">{{ __('Withdrawn') }}</span></div>
                </section>
                <section class="aside-block" id="rules" aria-labelledby="rules-h">
                    <h2 id="rules-h">{{ __('The rules, in brief') }}</h2>
                    <ul class="rules">
                        <li>@include('theme::partials.dir.glyph', ['icon' => 'check'])<span>{!! __('<b>README.md</b> at the release tag: it becomes the listing’s Overview. First line :code; no raw HTML or scripts; images inside the package.', ['code' => '<code># Name</code>']) !!}</span></li>
                        <li>@include('theme::partials.dir.glyph', ['icon' => 'check'])<span>{!! __('<b>The manifest</b> names the plugin and gives a one-line summary (140 characters at most), an icon (256 × 256), up to 8 screenshots, up to 3 categories, the Teme versions it works with, links and the permissions it asks for.') !!}</span></li>
                        <li>@include('theme::partials.dir.glyph', ['icon' => 'check'])<span>{!! __('<b>CHANGELOG.md</b>, one heading per version with its date. It becomes the Changelog.') !!}</span></li>
                        <li>@include('theme::partials.dir.glyph', ['icon' => 'check'])<span>{!! __('<b>A licence</b> from the allowed list. Premium plugins say where they are sold and give reviewers access to each release.') !!}</span></li>
                        <li>@include('theme::partials.dir.glyph', ['icon' => 'check'])<span>{!! __('<b>Ask only for what you use.</b> No hidden calls home, no tracking without the site owner’s consent, no code fetched at runtime.') !!}</span></li>
                    </ul>
                    @if ($rulesUrl !== '')<p class="small rules-more"><a href="{{ $rulesUrl }}">{{ __('Publishing to the directory') }}</a>{{ __(', in the developers’ docs, has the full rules.') }}</p>@endif
                </section>
            </aside>
        </div>
    </div></div>
@endsection
