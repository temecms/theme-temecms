{{-- An item's Overview: its README, converted, as the body (without an opening paragraph that repeats the summary), and where it came from. Given $html and $listing. A wide picture with a phone picture after it is set as one composition. Code blocks get the docs' frame, so a wide one scrolls by keyboard too. --}}
@include('theme::partials.pictures')
<div class="prose readme">{!! temecms_pictures(temecms_pair_shots(temecms_doc_prose(temecms_without_summary((string) $html, (string) ($listing['summary'] ?? '')), anchors: false)['html'])) !!}</div>
@if ($listing['version'] !== '')<p class="readme-src">{{ __('From README.md at the :version release tag.', ['version' => $listing['version']]) }}</p>@endif
