<div class="featured">
    <div class="flexrow header">
        <h2 dir="ltr" lang="en" class="itemhomeCol flexcol">Featured Titles</h2>
        <h2 dir="rtl" lang="ar" class="itemhomeCol flexcol"> من العناوين المعروضة</h2>
    </div>
    @foreach ($documents as $item)
        <div class="item flexrow">
            @include('partials.search-document-en', [ 'item' => $item['en'] ])
            @include('partials.search-document-thumb', [ 'item' => $item['en'] ])
            @include('partials.search-document-ar', [ 'item' => $item['ar'] ])
        </div>
    @endforeach
    <div class="flexrow itemhome viewmore">
        <div class="itemhomeCol flexcol" lang="en" dir="ltr">
            <a href="{{ route('browse.index') }}">View More</a>
        </div>
        <div class="itemhomeCol flexcol" lang="ar" dir="rtl">
            <a href="{{ route('browse.index') }}">المزيد</a>
        </div>
    </div>
</div>
