@extends('layouts.app')

@section('pagetitle', $pagetitle)

@section('body_class', $body_class)

@section('content')
    <main class="main container-fluid" role="main">

        <div class="search_holder_advanced widget" data-name="search_form">
            @include('partials.search_form_adv', [
                'query' => isset($query) ? $query : '',
            ])
        </div>

        <header class="results-header">
            @include('partials.pagetitle')
        </header>

        @if (!empty($documents))
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
                        <option data-sort-dir="asc" value="tks_publocation">Place in English / بلد النشر بالانجليزية
                        </option>
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
                @if (!empty($query) && !empty($total) && !empty($startIndex) && !empty($endIndex))
                    <div class="resultsnum">Showing <span class="start">{{ $startIndex }}</span> - <span
                            class="docslength">{{ $endIndex }}</span> of <span
                            class="numfound">{{ $total }}</span>
                        <span class="resultsfor">results for</span> <span class="s-query">{{ $query }}</span>
                    </div>
                @endif
            </header>
            <div class="item-list">
                @foreach ($documents as $item)
                    <div class="item flexrow">
                        @include('partials.search-document-en', ['item' => $item['en']])
                        @include('partials.search-document-thumb', ['item' => $item['en']])
                        @include('partials.search-document-ar', ['item' => $item['ar']])
                    </div>
                @endforeach
            </div>
            <div class="text-center">
                <div id="paginator" class="pagination">
                    {{ $paginator->links('vendor.pagination.default') }}
                </div>
            </div>
        @else
            <div class="col-l" lang="en" dir="ltr">
                <p>Sorry, no results found.</p>
                <p>Try a different term.</p>
            </div>
            <div class="col-r" lang="ar" dir="rtl">
                <p>عذراً، لا توجد نتائج.</p>
                <p>إبحث عن مصطلح آخر.</p>
            </div>
            <div class="inner">
                @include('partials.searchtips')
            </div>
        @endif
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/util/removeQueryDiacritics.js') }}"></script>
    <script src="{{ asset('js/search.js') }}"></script>
@endpush
