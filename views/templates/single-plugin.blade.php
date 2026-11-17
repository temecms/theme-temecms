{{-- A plugin's own page: its icon, name, line and status, then what it asks for, where it appears, what it keeps and sends, and how to get it. --}}
@extends('theme::layouts.base')

@section('main')
    @include('theme::partials.sectioned', [
        'page' => $page,
        'variant' => 'landing',
        'icon' => (string) ($page->field('icon')?->value ?? 'layers'),
        'tags' => array_values(array_filter([(string) ($page->field('status')?->text ?? '')])),
    ])
@endsection
