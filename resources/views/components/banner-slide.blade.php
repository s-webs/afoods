@if(!empty($link))
    <a href="{{ $link }}">
        <img src="{{ asset($image) }}" alt="{{ $name }}" class="rounded-lg shadow-lg">
    </a>
@else
    <img src="{{ asset($image) }}" alt="{{ $name }}" class="rounded-lg w-full">
@endif
