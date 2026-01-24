<li class="hover:text-main duration-300">
    <a href="{{ $link }}">
        <i class="{{ $icon }} inline-block translate-y-px"></i>
        <span>{{ \Illuminate\Support\Str::limit($name, 10, '...') }}</span>
    </a>
</li>
