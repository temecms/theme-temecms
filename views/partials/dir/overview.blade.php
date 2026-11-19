{{-- An item's Overview: its README, converted, as the body, and where it came from. Given $html and $listing. A wide picture with a phone picture after it is set as one composition. Code blocks get the docs' frame, so a wide one scrolls by keyboard too. --}}
<div class="prose readme">{!! temecms_pair_shots(temecms_doc_prose((string) $html, anchors: false)['html']) !!}</div>
@if ($listing['version'] !== '')<p class="readme-src">{{ __('From README.md at the :version release tag.', ['version' => $listing['version']]) }}</p>@endif
