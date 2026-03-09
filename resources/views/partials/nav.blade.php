<ul class="nav navbar-nav">
    {{-- Loop through the array of items provided by the View Composer --}}
    @foreach ($navItems as $item)
        {{--
            Use the url() helper function. If the 'url' key starts with '/',
            Laravel automatically appends the application URL if necessary.
        --}}
        <li>
            <a href="{{ url($item['url']) }}" class="{{ $item['class'] }}">
                {{ $item['title'] }}
            </a>
        </li>
    @endforeach
</ul>
