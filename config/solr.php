<?php

return [
    'host' => env('SOLR_HOST', 'discovery1.dlib.nyu.edu'),
    'port' => env('SOLR_PORT', 8983),
    'path' => env('SOLR_PATH', '/'),
    'core' => env('SOLR_CORE', 'viewer'),
];
