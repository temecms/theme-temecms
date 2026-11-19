{{-- One strip above the list on the browse page's first view: a heading, up to three listings, and "See all" that sorts the list the same way ($see; none for picks). $line says what each row's small line shows: updated, listed or summary. --}}
@if ($rows !== [])
    <section class="shelf" aria-labelledby="shelf-{{ $key }}">
        <div class="shelf-h"><h2 id="shelf-{{ $key }}">{{ $title }}</h2>@if ($see !== null)<button type="button" class="linkish" data-see="{{ $see }}" hidden>{{ __('See all') }}</button>@endif</div>
        <ul>
            @foreach ($rows as $row)
                <li><a href="{{ $row['url'] }}">@include('theme::partials.dir.icon', ['listing' => $row, 'size' => 'sm'])<div><b>{{ $row['title'] }}</b><small>
                    @if ($line === 'updated')
                        @if ($row['version'] !== '')v{{ $row['version'] }} · @endif
                        @include('theme::partials.dir.date', ['date' => $row['updated'], 'relative' => true])
                    @elseif ($line === 'listed')
                        {{ __('Listed') }} @include('theme::partials.dir.date', ['date' => $row['listed'], 'relative' => true])@if ($row['author'] !== '') · {{ $row['author'] }}@endif
                    @else
                        {{ $row['summary'] }}
                    @endif
                </small></div></a></li>
            @endforeach
        </ul>
    </section>
@endif
