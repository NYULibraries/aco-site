@extends('layouts.app')

@section('title', '404 - Not found')

@section('body_class', '404')

{{-- @section('no_footer', false) --}}

@section('content')
    <main class="main container-fluid">
        <div class="row">
            <div class="col-12">
                <h2 class="page-title">404 - The page requested does not exist.</h2>
                @if ($exception->getMessage())
                    <p>{{ $exception->getMessage() }}</p>
                @endif
                <p><a href="{{ route('home.index') }}" class="btn btn-primary">Return home</a></p>
            </div>
        </div>
    </main>
@endsection
