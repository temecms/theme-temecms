{{-- The foot of every page, given $status and $licence: the status line at the start and the licence at the end, each left out when it's empty. --}}
@if (trim($status) !== '' || trim($licence) !== '')
    <div class="foot-base">
        @if (trim($status) !== '')<span class="foot-status">{{ trim($status) }}</span>@endif
        @if (trim($licence) !== '')<span class="foot-licence">{{ trim($licence) }}</span>@endif
    </div>
@endif
