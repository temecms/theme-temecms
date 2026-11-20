{{-- What a plugin asks a site for, in plain words, from its manifest, with each capability's name small beside it. Given $listing. --}}
<p class="perm-intro">{{ __('What :name asks your site for, from its manifest. Your site shows the same list before you agree.', ['name' => $listing['title']]) }}</p>
@if ($listing['capabilities'] === [])
    <p class="muted">{{ __('It asks for nothing beyond what every plugin may do.') }}</p>
@else
    <ul class="perm-list">
        @foreach ($listing['capabilities'] as $cap)
            <li>@include('theme::partials.dir.glyph', ['icon' => 'check'])<div><b>{{ $cap['words'] ?? ($cap['key'] ?? '') }}</b>@if (($cap['key'] ?? '') !== '') <code>{{ $cap['key'] }}</code>@endif</div></li>
        @endforeach
    </ul>
@endif
<p class="note-line">@include('theme::partials.dir.glyph', ['icon' => 'info'])<span>{{ __('If an update asks for more than this, your site holds it until an administrator agrees, and the directory sends that release to a person before it is listed.') }}</span></p>
