@extends('layouts.app')

@section('pagetitle', $pagetitle)

@section('body_class', $body_class)

@section('content')
    <main class="main container-fluid" role="main">

        @include('partials.pagetitle')
        <div class="inner">
            @foreach ($content['resources'] as $resources)
                <div class="{{ $resources['language']['class'] }}" lang="{{ $resources['language']['lang'] }}"
                    dir="{{ $resources['language']['dir'] }}">
                    <p>{{ $resources['label'] }}</p>
                    <ul>
                        @foreach ($resources['links'] as $item)
                            <li>
                                <a href="{{ url($item['href']) }}">{{ $item['label'] }}</a> ({{ $item['count'] }})</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </main>
@endsection
