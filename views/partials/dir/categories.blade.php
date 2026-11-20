{{-- The directory's categories, key to words, for the Submit form's "Main category" (the index carries keys only). --}}
@include('theme::partials.directory-data')
<option value="">{{ __('Choose one') }}</option>
@foreach (array_slice(temecms_category_words(), 0, 14, true) as $key => $words)<option value="{{ $key }}">{{ $words }}</option>@endforeach
