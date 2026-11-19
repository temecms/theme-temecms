{{-- The Themes browse page: the theme type's listing, drawn by the directory's browse partial with picture cards. --}}
@extends('theme::layouts.base')

@section('main')
    <div class="dx">@include('theme::partials.dir.browse', ['kind' => 'themes'])</div>
@endsection
