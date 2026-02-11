@extends('layouts.app')

@section('title', $pagetitle)

@section('body_class', $body_class)

@section('content')
    <iframe role="main" title="Book Viewer: ACO" class="widget book" id="book" name="book" data-name="book"
        mozallowfullscreen="" webkitallowfullscreen="" style="height: 701px; opacity: 0;" data-identifier="{{ $identifier }}"
        src="https://sites.dlib.nyu.edu/viewer/books/{{ $identifier }}/{{ $page }}?embed=1&amp;lang=en">
    </iframe>
    {{-- Loader animation --}}
    <div class="bubblingG">
        <span id="bubblingG_1"></span>
        <span id="bubblingG_2"></span>
        <span id="bubblingG_3"></span>
    </div>
@endsection
