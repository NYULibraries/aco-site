<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
</head>

<body class="@yield('body_class')">
    @include('partials.header')
    @yield('content')
    @include('partials.footer')
</body>

</html>
