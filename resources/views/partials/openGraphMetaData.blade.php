<meta property="og:site_name" content="{{ config('og.site_name') }}">
<meta property="og:title" content="{{ !empty($pagetitle) ? $pagetitle : config('og.site_name') }}">
<meta property="og:description" content="{{ config('og.description') }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ config('app.url') }}">
<meta property="og:image:secure_url" content="{{ asset(config('og.image')) }}">
<meta property="og:image" content="{{ asset(config('og.image')) }}">
<meta property="og:image:width" content="{{ config('og.image_width') }}">
<meta property="og:image:height" content="{{ config('og.image_height') }}">
<meta property="og:image:type" content="{{ config('og.image_type') }}">
