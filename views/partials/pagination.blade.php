{{-- The links between a listing's pages, given $pagination: newer and older. --}}
@if ($pagination->previous !== null || $pagination->next !== null)
    <nav class="pager listing-pager" aria-label="{{ __('Pages') }}">
        @if ($pagination->previous !== null)<a class="prev" href="{{ $pagination->previous }}"><small>{{ __('Newer') }}</small><b>{{ __('Previous page') }}</b></a>@endif
        @if ($pagination->next !== null)<a class="next" href="{{ $pagination->next }}"><small>{{ __('Older') }}</small><b>{{ __('Next page') }}</b></a>@endif
    </nav>
@endif
