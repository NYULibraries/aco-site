@extends('layouts.app')

@section('pagetitle', $pagetitle)

@section('body_class', $body_class)

@section('content')
    <main class="main container-fluid" role="main">

        <div class="search_holder_advanced widget" data-name="search_form">
            @include('partials.search_form_adv')
        </div>

        @include('partials.pagetitle')

        <header class="results-header">

            <div class="aboutinfo">
                <div class="aboutinfo-links">
                    <a href="#" dir="ltr" lang="en" class="aboutinfo-link aboutinfo-link-available"
                        data-name="aboutinfo-link" aria-expanded="false"
                        aria-controls="aboutinfocontent-id"><span></span>About this search</a>
                    <a href="#" dir="rtl" lang="ar" class="aboutinfo-link aboutinfo-link-available"
                        data-name="aboutinfo-link" aria-expanded="false" aria-controls="aboutinfocontent-id">بخصوص
                        هذا البحث<span class="arrow-left"></span></a>
                </div>
                <div id="aboutinfocontent-id" class="about-info-content" data-name="aboutinfo-content"
                    style="height:0; overflow:hidden">
                    <div class="inner">
                        @include('partials.searchtips')
                    </div>
                </div>
            </div>

            <div class="select-style sort-select">
                <select id="sort-select-el" aria-label="Sorting Criteria">
                    <option data-sort-dir="desc" value="score">Relevance / فرز حسب</option>
                    <option data-sort-dir="asc" value="tks_title_long">Title in English / العنوان بالانجليزية</option>
                    <option data-sort-dir="asc" value="tks_ar_title_long">Title in Arabic / العنوان بالعربية</option>
                    <option data-sort-dir="asc" value="iass_pubyear">Date of Publication / تاريخ النشر</option>
                    <option data-sort-dir="asc" value="tks_publocation">Place in English / بلد النشر بالانجليزية</option>
                    <option data-sort-dir="asc" value="tks_ar_publocation">Place in Arabic / بلد النشر بالعربية</option>
                    <option data-sort-dir="desc" value="ds_created">Recently added / وضِعت مؤخراً</option>
                </select>
            </div>

            <div class="select-style rpp-select">
                <select id="rpp-select-el" aria-label="Number of Results Per Page">
                    <option value="10" selected>10 per page</option>
                    <option value="20">20 per page</option>
                    <option value="40">40 per page</option>
                </select>
            </div>

            <div class="resultsnum">Showing <span class="start">{{ $startIndex }}</span> - <span
                    class="docslength">{{ $endIndex }}</span> of <span class="numfound">{{ $numfound }}</span> <span
                    class="resultsfor">results for</span> <span class="s-query">{{ $query }}</span></div>

        </header>

        <div class="item-list">
            @foreach ($documents as $item)
                <div class="item flexrow">

                    <div class="flexcol" lang="en">

                        <div class="md_title">
                            <a href="{{ url($item['en']['path']) }}">{{ $item['en']['title'] }}</a>
                        </div>

                        <div class="md_authors">
                            <span class="md_label">Author:</span>
                            @if (empty($item['en']['authors']))
                                n.a.
                            @else
                                @foreach ($item['en']['authors'] as $author)
                                    <span class="one_author">
                                        <a class="md_author" href="{{ url($author['path']) }}">{{ $author['label'] }}</a>
                                    </span>
                                @endforeach
                            @endif
                        </div>

                        <div class="md_category">
                            <span class="md_label">Category:</span>
                            @if (empty($item['en']['topics']))
                                n.a.
                            @else
                                @foreach ($item['en']['topics'] as $topic)
                                    <span class="one_category">
                                        <a class="md_category" href="{{ url($topic['path']) }}">{{ $topic['label'] }}</a>
                                    </span>
                                @endforeach
                            @endif
                        </div>

                        <div class="md_publisher">
                            <span class="md_label">Publisher:</span>
                            @if (!empty($item['en']['publishers']))
                                @foreach ($item['en']['publishers'] as $publisher)
                                    @if (!empty($publisher))
                                        <span><a class="md_publisher"
                                                href="{{ url($publisher['path']) }}">{{ $publisher['label'] }}</a></span>
                                    @else
                                        n.p.
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <div class="md_pubplace">
                            <span class="md_label">Place of Publication:</span>
                            @if (empty($item['en']['publocation']))
                                n.a.
                            @else
                                @foreach ($item['en']['publocation'] as $publocation)
                                    <span class="one_author">
                                        <a class="md_pubplace"
                                            href="{{ url($publocation['path']) }}">{{ $publocation['label'] }}</a>
                                    </span>
                                @endforeach
                            @endif
                        </div>

                        @if (!empty($item['en']['pubdate']))
                            <div class="md_pubdate">
                                <span class="md_label">Date of Publication:</span>
                                {{ $item['en']['pubdate'] }}
                            </div>
                        @endif

                        @if (!empty($item['en']['subjects']))
                            <div class="md_subjects">
                                <span class="md_label">Subject:</span>
                                @foreach ($item['en']['subjects'] as $subject)
                                    <span class="one_subject">
                                        <a class="md_subject" href="{{ url($subject->path) }}">{{ $subject->name }}</a>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if (!empty($item['en']['partners']))
                            <div class="md_partner">
                                <span class="md_label">Provider:</span>
                                @foreach ($item['en']['partners'] as $partner)
                                    <a class="md_provider" href="{{ url($partner->path) }}">{{ $partner->name }}</a>
                                @endforeach
                            </div>
                        @endif

                    </div>

                    <div class="thumbholder flexcol">
                        <div class="thumbs">
                            <div class="read-online">
                                <a class="read-online" href="{{ url($item['en']['path']) }}">
                                    <div lang="ar" class="ar_callout" dir="rtl">شاهِد فوراً</div>
                                    <div dir="ltr" lang="en">Read Online</div>
                                </a>
                            </div>
                            <div class="download-pdfs">
                                <div class="download-pdf-button">

                                    @if (!empty($item['en']['pdf']['lo']))
                                        <a class="pdf-button" href="{{ $item['en']['pdf']['lo']->uri }}">
                                            <div dir="rtl" lang="ar">تحميل دِقّة منخفضة</div>
                                            <div dir="ltr" lang="en">Low-resolution PDF
                                                @if (!empty($item['en']['pdf']['lo']->filesize))
                                                    <span class="fs">({{ $item['en']['pdf']['lo']->filesize }})</span>
                                                @endif
                                            </div>
                                        </a>
                                    @endif

                                    @if (!empty($item['en']['pdf']['hi']))
                                        <a class="pdf-button" href="{{ $item['en']['pdf']['hi']->uri }}">
                                            <div dir="rtl" lang="ar">تحميل دِقّة عالية</div>
                                            <div dir="ltr" lang="en">High-resolution PDF
                                                @if (!empty($item['en']['pdf']['hi']->filesize))
                                                    <span class="fs">({{ $item['en']['pdf']['hi']->filesize }})</span>
                                                @endif
                                            </div>
                                        </a>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flexcol" dir="rtl" lang="ar">

                        <div class="md_title" dir="rtl" lang="ar">
                            <span>
                                <a href="{{ url($item['ar']['path']) }}">{{ $item['ar']['title'] }}</a>
                            </span>
                        </div>

                        <div class="md_authors">
                            <span class="md_label">الكاتب:</span>
                            @if (empty($item['ar']['authors']))
                                n.a.
                            @else
                                @foreach ($item['ar']['authors'] as $author)
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
                            @if (empty($item['ar']['topics']))
                                n.a.
                            @else
                                @foreach ($item['ar']['topics'] as $topic)
                                    <span class="one_category">
                                        <a class="md_category"
                                            href="{{ url($topic['path']) }}">{{ $topic['label'] }}</a>
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
                            @if (empty($item['ar']['publocation']))
                                n.p.
                            @else
                                @foreach ($item['ar']['publocation'] as $publocation)
                                    <span class="one_author">
                                        <a class="md_pubplace"
                                            href="{{ url($publocation['path']) }}">{{ $publocation['label'] }}</a>
                                    </span>
                                @endforeach
                            @endif
                        </div>

                        <div class="md_pubdate">
                            <span class="md_label">تاريخ النشر:</span>
                            {{ $item['ar']['pubdate'] }}
                        </div>

                        @if (!empty($item['ar']['partners']))
                            <div class="md_partner">
                                <span class="md_label">مُزَوِّد:</span>
                                @foreach ($item['ar']['partners'] as $partner)
                                    <a class="md_provider"
                                        href="{{ url($partner['path']) }}">{{ $partner['label'] }}</a>
                                @endforeach
                            </div>
                        @endif

                    </div>

                </div>
            @endforeach
        </div>

        {{ $paginator->links() }}
    </main>
@endsection
