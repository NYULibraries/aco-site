<title>
    {{ env('APP_NAME_EN') }}@if (!empty($pagetitle))
        : {{ $pagetitle }}
    @endif
</title>

@if (config('app.analytics_enabled'))
    <!-- Matomo -->
    <script>
        var _paq = window._paq = window._paq || [];
        /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u = "https://nyulib.matomo.cloud/";
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId', '19']);
            var d = document,
                g = d.createElement('script'),
                s = d.getElementsByTagName('script')[0];
            g.async = true;
            g.src = 'https://cdn.matomo.cloud/nyulib.matomo.cloud/matomo.js';
            s.parentNode.insertBefore(g, s);
        })();
    </script>
    <!-- End Matomo Code -->
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VBYLT4NB1C"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-VBYLT4NB1C');
    </script>
@endif


<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!--[if lte IE 9]>
<script>
    window.location.replace("{{ config('app.url') }}/upgrade.html");
</script>
<![endif]-->

@include('partials.openGraphMetaData')

<style>
    @font-face {
        font-family: 'NYUPerstare';
        src: url('{{ asset('fonts/NYUPerstare-VF.woff2') }}') format('woff2');
        font-style: normal;
        font-synthesis: none;
    }
</style>
<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.5.10/webfont.js"></script>
<script>
    WebFont.load({
        google: {
            families: ['Amiri:400,700', 'Open Sans:400,300,700']
        },
        timeout: 6000
    });
</script>

<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
<link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" />

<script src="{{ asset('js/app.js') }}"></script>
@vite(['resources/sass/style.scss'])
