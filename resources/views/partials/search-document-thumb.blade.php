<div class="thumbholder flexcol">
    <div class="thumbs">
        <div class="read-online">
            <a class="read-online" href="{{ url($item['path']) }}">
                <div lang="ar" class="ar_callout" dir="rtl">شاهِد فوراً</div>
                <div dir="ltr" lang="en">Read Online</div>
            </a>
        </div>
        <div class="download-pdfs">
            <div class="download-pdf-button">
                @if (!empty($item['pdf']['lo']))
                    <a class="pdf-button" href="{{ $item['pdf']['lo']['uri'] }}">
                        <div dir="rtl" lang="ar">تحميل دِقّة منخفضة</div>
                        <div dir="ltr" lang="en">Low-resolution PDF
                            @if (!empty($item['pdf']['lo']['filesize']))
                                <span class="fs">({{ $item['pdf']['lo']['filesize'] }})</span>
                            @endif
                        </div>
                    </a>
                @endif
                @if (!empty($item['pdf']['hi']))
                    <a class="pdf-button" href="{{ $item['pdf']['hi']['uri'] }}">
                        <div dir="rtl" lang="ar">تحميل دِقّة عالية</div>
                        <div dir="ltr" lang="en">High-resolution PDF
                            @if (!empty($item['pdf']['hi']['filesize']))
                                <span class="fs">({{ $item['pdf']['hi']['filesize'] }})</span>
                            @endif
                        </div>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
