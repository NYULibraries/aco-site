<?php

return [
    /**
     * Solr Query Defaults
     * These settings define the "baseline" query sent to Solr.
     */
    'defaults' => [
        'filters' => [
            [
                'field' => 'sm_collection_code',
                 'value' => 'aco'
            ],
            [
                'field' => 'ss_language',
                'value' => 'en'
            ],
            [
                'field' => 'bundle',
                'value' => 'dlts_book',
            ],
        ],
        'sort' => [
            [
                'field' => 'ss_book_identifier',
                'dir' => 'desc',
            ],
        ],
        'rows' => 10,
        'start' => 0,
        'fields' => [
            '*',
        ],
    ],
    /**
     * Featured Books List
     * The order of these identifiers determines the display order on the frontend.
     */
    'books' => [
        'princeton_aco000320',
        'nyu_aco000348',
        'nyu_aco000227',
        'cornell_aco000223',
        'cornell_aco000032',
        'columbia_aco003391',
        'aub_aco001663',
        'aub_aco001474',
        'aub_aco000056',
    ],

];
