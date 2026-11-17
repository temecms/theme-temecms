{{-- One post in the blog's list, given $item (a ListingItem): its date, title, line and kind, as a full-width row. --}}
<article>
    <time datetime="{{ $item->published->format('Y-m-d') }}">{{ $item->publishedText }}</time>
    <div>
        <h2><a href="{{ $item->url }}">{{ $item->title }}</a></h2>
        @if ($item->standfirst !== null && $item->standfirst !== '')<p>{{ $item->standfirst }}</p>@endif
        @if ($item->kicker !== null)<span class="tag">{{ $item->kicker->name }}</span>@endif
    </div>
</article>
