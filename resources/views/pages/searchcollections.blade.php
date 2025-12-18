@extends('layouts.app')

@section('pagetitle', $pagetitle)

@section('body_class', $body_class)

@section('content')
  <main class="main container-fluid" role="main">
    <header class="results-header">
      @include('partials.pagetitle')
    </header>
    <div class="search_holder_advanced widget" data-name="search_form">
      @include('partials.search_form_adv')
    </div>
    <div class="inner">
      @include('partials.searchtips')
    </div>
  </main>
@endsection
