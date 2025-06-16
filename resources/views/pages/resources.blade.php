@extends('layouts.app')

@section('pagetitle', $pagetitle)

@section('body_class', $body_class)

@section('content')
  <main class="main container-fluid" role="main">

    @include('partials.pagetitle')

    @foreach ($content['resources'] as $resources)
      <div class="{{ $resources['language']['class'] }}" lang="{{ $resources['language']['lang'] }}" dir="{{ $resources['language']['dir'] }}">
        <ul class="resources">
          @foreach ($resources['links'] as $item)
            <li>
              <h3>
                <a href="{{ $item['href'] }}" target="_blank">{{ $item['label'] }}</a>
              </h3>
              <div class="link">
                {{ $item['href'] }}
              </div>
            </li>
          @endforeach
        </ul>
      </div>
    @endforeach

  </main>
@endsection
