@extends('layouts.app')

@section('pagetitle', $pagetitle)

@section('body_class', $body_class)

@section('content')
  <main class="main container-fluid" role="main">

    @include('partials.pagetitle')

    @foreach ($content['about'] as $langCode => $data)
      <div class="col {{ $data['language']['class'] }}" lang="{{ $langCode }}" dir="{{ $data['language']['dir'] }}">
        <h3>{{ $data['label'] }}</h3>
        {!! $data['body'] !!}
      </div>
    @endforeach

    @foreach ($content['main'] as $index => $data)
      <div class="col {{ $data['language']['class'] }}" lang="{{ $data['language']['code'] }}" dir="{{ $data['language']['dir'] }}">
        {!! $data['html'] !!}
      </div>
    @endforeach

  </main>
@endsection
