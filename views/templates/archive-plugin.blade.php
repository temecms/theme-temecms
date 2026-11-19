{{-- The Plugins browse page: the plugin type's listing, drawn by the directory's browse partial. --}}
@extends('theme::layouts.base')

@section('main')
    <div class="dx">@include('theme::partials.dir.browse', ['kind' => 'plugins'])</div>
@endsection
