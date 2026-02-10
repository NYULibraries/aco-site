<div class="flexcol" dir="ltr" lang="en">
    <div class="md_title">
        <a href="{{ url($item['path']) }}">{{ $item['title'] }}</a>
    </div>

    <div class="md_authors">
        <span class="md_label">Author:</span>
        @if (empty($item['authors']))
            n.a.
        @else
            @foreach ($item['authors'] as $author)
                <span class="one_author">
                    <a class="md_author" href="{{ url($author['path']) }}">{{ $author['label'] }}</a>
                </span>
            @endforeach
        @endif
    </div>

    <div class="md_category">
        <span class="md_label">Category:</span>
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

    <div class="md_publisher">
        <span class="md_label">Publisher:</span>
        @if (!empty($item['publishers']))
            @foreach ($item['publishers'] as $publisher)
                @if (!empty($publisher))
                    <span>
                        <a class="md_publisher" href="{{ url($publisher['path']) }}">{{ $publisher['label'] }}</a>
                    </span>
                @else
                    n.p.
                @endif
            @endforeach
        @endif
    </div>

    <div class="md_pubplace">
        <span class="md_label">Place of Publication:</span>
        @if (empty($item['publocation']))
            n.a.
        @else
            @foreach ($item['publocation'] as $publocation)
                <span class="one_author">
                    <a class="md_pubplace" href="{{ url($publocation['path']) }}">{{ $publocation['label'] }}</a>
                </span>
            @endforeach
        @endif
    </div>

    @if (!empty($item['pubdate']))
        <div class="md_pubdate">
            <span class="md_label">Date of Publication:</span>
            {{ $item['pubdate'] }}
        </div>
    @endif

    @if (!empty($item['subjects']))
        <div class="md_subjects">
            <span class="md_label">Subjects:</span>
            @foreach ($item['subjects'] as $subject)
                <span class="one_subject">
                    <a class="md_subject" href="{{ url($subject['path']) }}">{{ $subject['label'] }}</a>
                </span>
            @endforeach
        </div>
    @endif

    @if (!empty($item['partners']))
        <div class="md_partner">
            <span class="md_label">Provider:</span>
            @foreach ($item['partners'] as $partner)
                <a class="md_provider" href="{{ url($partner['path']) }}">{{ $partner['label'] }}</a>
            @endforeach
        </div>
    @endif
</div>
