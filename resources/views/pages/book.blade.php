@extends('layouts.app')

@section('title', $pagetitle)

@section('body_class', $body_class)

@section('no_footer', true)

@section('content')
    <iframe role="main" title="Book Viewer: ACO" class="widget book" id="book" name="book" data-name="book"
        mozallowfullscreen="" webkitallowfullscreen="" style="height: 701px; opacity: 0;" data-identifier="{{ $identifier }}"
        data-sourceurl="{{ config('viewer.endpoint') }}"
        data-script="{ &quot;js&quot; : [ &quot;crossframe.min.js&quot;, &quot;book.min.js&quot; ] }"
        src="{{ config('viewer.endpoint') }}/books/{{ $identifier }}/{{ $page }}?embed=1&amp;lang={{ $lang }}">
    </iframe>
    {{-- Loader animation --}}
    <div class="bubblingG">
        <span id="bubblingG_1"></span>
        <span id="bubblingG_2"></span>
        <span id="bubblingG_3"></span>
    </div>
@endsection
