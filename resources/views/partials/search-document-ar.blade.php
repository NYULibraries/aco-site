<div class="flexcol" dir="rtl" lang="ar">
    <div class="md_title" dir="rtl" lang="ar">
        <span>
            <a href="{{ url($item['path']) }}">{{ $item['title'] }}</a>
        </span>
    </div>

    <div class="md_authors">
        <span class="md_label">الكاتب:</span>
        @if (empty($item['authors']))
            n.a.
        @else
            @foreach ($item['authors'] as $author)
                <span class="one_author">
                    <a class="md_author" href="{{ url($author['path']) }}">
                        {{ $author['label'] }}
                    </a>
                </span>
            @endforeach
        @endif
    </div>

    <div class="md_category">
        <span class="md_label">فئة الموضوع:</span>
        @if (empty($item['topics']))
            n.a.
        @else
            @foreach ($item['topics'] as $topic)
                <span class="one_category">
                    <a class="md_category" href="{{ url($topic['path']) }}">{{ $topic['label'] }}</a>
                </span>
            @endforeach
        @endif
    </div>

    {{-- <div class="md_publisher">
        <span class="md_label">الناشر:</span>
        @if (empty($item['sm_ar_publisher']))
          n.p.
        @else
          @foreach ($item['sm_ar_publisher'] as $publisher)
            <a class="md_publisher" href="{{ url('/search/?publisher={{ $publisher }}"><span>{{ $publisher }}</span></a>
          @endforeach
        @endif
      </div> --}}


    <div class="md_publocation">
        <span class="md_label">مكان النشر:</span>
        @if (empty($item['publocation']))
            n.p.
        @else
            @foreach ($item['publocation'] as $publocation)
                <span class="one_author">
                    <a class="md_pubplace" href="{{ url($publocation['path']) }}">{{ $publocation['label'] }}</a>
                </span>
            @endforeach
        @endif
    </div>

    <div class="md_pubdate">
        <span class="md_label">تاريخ النشر:</span>
        {{ $item['pubdate'] }}
    </div>

    @if (!empty($item['partners']))
        <div class="md_partner">
            <span class="md_label">مُزَوِّد:</span>
            @foreach ($item['partners'] as $partner)
                <a class="md_provider" href="{{ url($partner['path']) }}">{{ $partner['label'] }}</a>
            @endforeach
        </div>
    @endif

</div>
