<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
</head>

<body class="@yield('body_class')">
    @include('partials.header')
    @yield('content')
    @if(!View::hasSection('no_footer'))
        @include('partials.footer')
    @endif
</body>

</html>
