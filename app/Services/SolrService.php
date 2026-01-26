<?php

namespace App\Services;

use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SolrService
{
  protected Client $client;

  public function __construct(private Client $solrClient)
  {
    // this->client = $solrClient;
  }

  /**
   * Modifies the query object according to search params passed
   * use the previous YUI implementation for reference https://github.com/NYULibraries/aco-site/blob/main/source/js/search.js
   */
  public function buildQuery(
    string $fieldSelect = 'q', // what field to search on
string $searchString,  // what query to use
string $scopeIs = 'matches', // how to match results
string $sortField = 'score', // how to order results
string $sortDir = 'desc',
$rowStart,
$rows
  ): Query
  // public function buildQuery(string $searchString, array $options = []): Query
  {
/**
     * Defaults
     */
    // $defaults = [
    //   'scopeIs' => 'matches',
    //   'sortField' => 'score',
    //   'sortDir' => 'desc',
    //   'rowStart' => 10,
    //   'rows' => 0,
    // ];
    // $params = array_merge($defaults, $options);

    /**
     * Possible combinations:
     * searchString - whatever sentence the user has
     * scopeIs - matches, contains all, contains any
     * sortBy - asc, desc
     */
    echo "running buildQuery \n";
    // 1. generate query object
    $query = $this->solrClient->createSelect();

    /**
     * old examples for generated queries?
     */
    // title, matches, jibran
    // fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&
    // fq=(tks_title_long:"jibran" OR tks_ar_title_long:"jibran")&rows=10&start=0&sort=score desc&q=*

    // title, contains all, jibran
    // fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en
    // &fq=((tus_title_long:%22jibran%22%20OR%20ts_title_long:%22jibran%22%20OR%20tusar_title_long:%22jibran%22))&rows=10&start=0&sort=score%20desc&q=*

    // title, contains aany, jibran
    // fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en
    // &fq=((tus_title_long:%22jibran%22%20OR%20ts_title_long:%22jibran%22%20OR%20tusar_title_long:%22jibran%22))&rows=10&start=0&sort=score%20desc&q=*

    // select?
    // wt=json
    // fl=*
    // fq=bundle:dlts_book   << TAKA: this needs to be setup always
    // fq=sm_collection_code:aco   << TAKA: this needs to be in the query always
    // fq=ss_language:en     << TAKA: this needs to be in the query always
    // rows=10
    // start=0
    // sort=score%20desc
    // q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))

    // 2. extract the request details
    // $options = $request->all();
    $options = [];
    // options only really have q => queryString

    /**
     * FIELD LIST - fl
     */
    $query->addParam('fl', '*'); // field list: all fields

    /**
     * FILTER QUERY - fq
     */
    $fq = [];

    // these are all the possible fields to add to the filter query
    // we will use the request options to trim these down to use on our query
    $fields = [
      'title' => [
        'match' => [
          'tks_title_long',
          'tks_ar_title_long',
        ],
        'contains' => [
          'tus_title_long',
          'ts_title_long',
          'tusar_title_long',
        ],
      ],
      'author' => [
        'match' => [
          'tkm_author',
          'tkm_ar_author',
        ],
        'contains' => [
          'tum_author',
          'tm_author',
          'tumar_author',
        ]
      ],
      'pubplace'  => [
        'match' => [
          'tks_publocation',
          'tks_ar_publocation',
        ],
        'contains' => [
          'tus_publocation',
          'ts_publocation',
          'tusar_publocation',
        ],
      ],
      'publisher' => [
        'match' => [
          'tkm_publisher',
          'tkm_ar_publisher',
        ],
        'contains' => [
          'tum_publisher',
          'tm_publisher',
          'tumar_publisher',
        ],
      ],
      'category' => [
        'match' => [
          'tkm_topic',
          'tkm_ar_topic',
        ],
        'contains' => [
          'tum_topic',
          'tm_topic',
          'tumar_topic',
        ],
      ],
      'provider' => [
        'match' => [
          'tkm_provider_label',
        ],
        'contains' => [
          'tum_provider_label',
          'tm_provider_label',
        ],
      ],
      'subject' => [
        'match' => [
          'tkm_subject_label',
        ],
        'contains' => [
          'tum_subject_label',
          'tm_subject_label',
        ],
      ],
    ];

    // foreach ($fields as $key => $map) {
    //   if (empty($options[$key])) continue;
    //   $value = trim($options[$key]);
    //   if ($scopeIs === 'matches') {
    //     $parts = array_map(fn($f) => "$f:\"$value\"", $map['match']);
    //     $fq[] = '(' . implode(' OR ', $parts) . ')';
    //   } else {
    //     $words = preg_split('/\s+/', $value);
    //     $clauses = [];
    //     foreach ($words as $w) {
    //       $sub = array_map(fn($f) => "$f:\"$w\"", $map['contains']);
    //       $clauses[] = '(' . implode(' OR ', $sub) . ')';
    //     }
    //     $fq[] = '(' . implode(($scopeIs === 'containsAny') ? ' OR ' : ' AND ', $clauses) . ')';
    //   }
    // }

    /**
     * fieldSelect determines how the url is created and where the params are placed
     * firldSelect q -> put the query in the q
     * fieldSelect title, author, category, publisher, pubplace, provider, subject
     * QUERY AND FILTERQUERY
     */
    // echo $fieldSelect;
    // echo ">>>>>>>>>>> \n";
    $helper = $query->getHelper();
    $sanitizedSearchString = $helper->escapeTerm($searchString);
    if ($fieldSelect == 'q') {
      if ($scopeIs == 'matches') {
$finalQuery = "((content_und:$sanitizedSearchString OR content_und_ws:$sanitizedSearchString OR content_en:$sanitizedSearchString OR content:$sanitizedSearchString))";
    } else {
$finalQuery = "((content_und:$sanitizedSearchString OR content_und_ws:$sanitizedSearchString OR content_en:$sanitizedSearchString OR content:$sanitizedSearchString))";
      }
    } else {
      echo "<<<< this is the else \n";
      $finalQuery = '*';
      // only adds the query into the fq otions and not the q options

    }
echo "finalQuery before sanitizing: \n";
    echo "$finalQuery \n";
    $query->setQuery($finalQuery);

    /**
     * FILTER QUERY fq
     */
        // these always have to happen as of 2026
    $query->createFilterQuery(md5('bundle:dlts_book'))->setQuery('bundle:dlts_book');
    $query->createFilterQuery(md5('sm_collection_code:aco'))->setQuery('sm_collection_code:aco');
    $query->createFilterQuery(md5('ss_language:en'))->setQuery('ss_language:en');
    // 3.1 set additional filter query
    foreach ($fq as $filter) {
      $query->createFilterQuery(md5($filter))->setQuery($filter);
    }


    /**
     * QUERY q
     * these only need to be when anyfield is selected in field select
     * everything else is *
     * q = query is the only thing coming from the Request Object
     * TODO: sanitize query
     * TODO:
     */
//     $helper = $query->getHelper();
//     $sanitizedSearchString = $helper->escapeTerm($searchString);
//     $finalQuery = "((content_und:$sanitizedSearchString OR content_und_ws:$sanitizedSearchString OR content_en:$sanitizedSearchString OR content:$sanitizedSearchString))";
//     echo "finalQuery before sanitizing: \n";
//     echo "$finalQuery \n";
    //     $query->setQuery($finalQuery);
    // if (!empty($options['q'])) {
    //   $q = trim($options['q']);
    //   if ($scopeIs === 'matches') {
    //     $query->setQuery("(content_und:\"$q\" OR content_und_ws:\"$q\" OR content_en:\"$q\" OR content:\"$q\")");
    //   } else {
    //     $words = preg_split('/\s+/', $q);
    //     $parts = [];
    //     foreach ($words as $w) {
    //       $parts[] = "(content_und:$w OR content_und_ws:$w OR content_en:$w OR content:$w)";
    //     }
    //     $query->setQuery('(' . implode(($scopeIs === 'containsAny') ? ' OR ' : ' AND ', $parts) . ')');
    //   }
    // }

    /**
     * PAGINATION
     * TODO: consider method arguments to pass into pagination
     */
    $start = $options['start'] ?? 0;
    $rows  = $options['rpp'] ?? 10;
    $query->setStart($start); // what item to start from (page)
    $query->setRows($rows);  // how many items to show (page size)

    /**
     * SORT
     * field
     * direction
     */
    $sortDirec = ($sortDir == 'desc') ? $query::SORT_DESC : $query::SORT_ASC;
    $query->addSort($sortField, $sortDirec);

    // createRequest transforms the php queryObject into an actual HTTP request object (headers, URI, GET params)
    // used for debugging THIS IS WHERE WE CHECK THE URL
    $request = $this->solrClient->createRequest($query);
    $uri = $request->getUri();
    echo "--------------------- \n";
    echo "Created Solarium URL: \n";
    echo "--------------------- \n";
    echo $uri . PHP_EOL;
    // dump($uri);
    echo "--------------------- \n";
    echo "example query: \n";
    echo "--------------------- \n";
    echo "select?wt=json&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))" . PHP_EOL;
    echo "--------------------- \n";
    // 3. return single string for query execution
    return $query;
  }

  public function search(Request $request, string $scopeIs = 'matches', string $sortBy = 'score desc'): array
  {

    // title, matches, jibran
    // fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&
    // fq=(tks_title_long:"jibran" OR tks_ar_title_long:"jibran")&rows=10&start=0&sort=score desc&q=*

    // title, contains all, jibran
    // fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en
    // &fq=((tus_title_long:%22jibran%22%20OR%20ts_title_long:%22jibran%22%20OR%20tusar_title_long:%22jibran%22))&rows=10&start=0&sort=score%20desc&q=*

    // title, contains aany, jibran
    // fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en
    // &fq=((tus_title_long:%22jibran%22%20OR%20ts_title_long:%22jibran%22%20OR%20tusar_title_long:%22jibran%22))&rows=10&start=0&sort=score%20desc&q=*

    // select?
    // wt=json
    // fl=*
    // fq=bundle:dlts_book
    // fq=sm_collection_code:aco
    // fq=ss_language:en
    // rows=10
    // start=0
    // sort=score%20desc
    // q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))

    // TAKANOTE: I don't believe we need the whole request object here... we might need some parts of it but not all
    // putting the request in the arguments of this method makes unit testing it harder since it requires
    // a Solarium call to the client
    // 1. extract the request details
    $options = $request->all();   // the options object ONLY seems to contain the q parameter
    echo "------------------ \n";
    // dd dump and die
    // dd($options);
    // dump
    dump($options);
    Log::info('options variable coming from search service', $options);
    echo "------------------ \n";

    // 2. create the filterQuery array
    $fq = [];

    $fields = [
      'title' => [
        'match' => [
          'tks_title_long',
          'tks_ar_title_long',
        ],
        'contains' => [
          'tus_title_long',
          'ts_title_long',
          'tusar_title_long',
        ],
      ],
      'author' => [
        'match' => [
          'tkm_author',
          'tkm_ar_author',
        ],
        'contains' => [
          'tum_author',
          'tm_author',
          'tumar_author',
        ]
      ],
      'pubplace'  => [
        'match' => [
          'tks_publocation',
          'tks_ar_publocation',
        ],
        'contains' => [
          'tus_publocation',
          'ts_publocation',
          'tusar_publocation',
        ],
      ],
      'publisher' => [
        'match' => [
          'tkm_publisher',
          'tkm_ar_publisher',
        ],
        'contains' => [
          'tum_publisher',
          'tm_publisher',
          'tumar_publisher',
        ],
      ],
      'category' => [
        'match' => [
          'tkm_topic',
          'tkm_ar_topic',
        ],
        'contains' => [
          'tum_topic',
          'tm_topic',
          'tumar_topic',
        ],
      ],
      'provider' => [
        'match' => [
          'tkm_provider_label',
        ],
        'contains' => [
          'tum_provider_label',
          'tm_provider_label',
        ],
      ],
      'subject' => [
        'match' => [
          'tkm_subject_label',
        ],
        'contains' => [
          'tum_subject_label',
          'tm_subject_label',
        ],
      ],
    ];

    // seems like there is only q => queryString in the options object
    foreach ($fields as $key => $map) {
      if (empty($options[$key])) continue;
      $value = trim($options[$key]);
      if ($scopeIs === 'matches') {
        $parts = array_map(fn($f) => "$f:\"$value\"", $map['match']);
        $fq[] = '(' . implode(' OR ', $parts) . ')';
      } else {
        $words = preg_split('/\s+/', $value);
        $clauses = [];
        foreach ($words as $w) {
          $sub = array_map(fn($f) => "$f:\"$w\"", $map['contains']);
          $clauses[] = '(' . implode(' OR ', $sub) . ')';
        }
        $fq[] = '(' . implode(($scopeIs === 'containsAny') ? ' OR ' : ' AND ', $clauses) . ')';
      }
    }

    $query = $this->solrClient->createSelect();

    // 3. set the query
    $query->setQuery('*:*');

    // 4. set the filter query
    foreach ($fq as $filter) {
      $query->createFilterQuery(md5($filter))->setQuery($filter);
    }

    if (!empty($options['q'])) {
      $q = trim($options['q']);
      if ($scopeIs === 'matches') {
        $query->setQuery("(content_und:\"$q\" OR content_und_ws:\"$q\" OR content_en:\"$q\" OR content:\"$q\")");
      } else {
        $words = preg_split('/\s+/', $q);
        $parts = [];
        foreach ($words as $w) {
          $parts[] = "(content_und:$w OR content_und_ws:$w OR content_en:$w OR content:$w)";
        }
        $query->setQuery('(' . implode(($scopeIs === 'containsAny') ? ' OR ' : ' AND ', $parts) . ')');
      }
    }

    // 5. pagination for solr
    $start = $options['start'] ?? 0;
    $rows  = $options['rpp'] ?? 10;
    $query->setStart($start);
    $query->setRows($rows);

    // 6. make the request
    $request = $this->solrClient->createRequest($query);

    // 7. extract returned data from request
    $uri = $request->getUri();

    // THIS IS WHERE THE QUERY IS EXECUTED
    $resultset = $this->solrClient->select($query);
    $total = $resultset->getNumFound();

    $docs = iterator_to_array($resultset);

    $documents = [];

    // 8. data transformation
    foreach ($docs as $doc) {

      $publocation = [];

      if (isset($doc->ss_publocation)) {
        $publocation[] = [
          'label' => $doc->ss_publocation,
          'path' => "search/?provider={$doc->ss_publocation}",
        ];
      }

      $ar_publocation = [];

      if (isset($doc->ss_ar_publocation)) {
        $ar_publocation[] = [
          'label' => $doc->ss_ar_publocation,
          'path' => "search/?provider={$doc->ss_ar_publocation}",
        ];
      }

      $providers = [];

      if (isset($doc->sm_provider_label)) {
        foreach ($doc->sm_provider_label as $provider) {
          $providers[] = [
            'label' => $provider,
            'path' => "search/?provider={$provider}",
          ];
        }
      }

      $publishers = [];

      if (isset($doc->sm_publisher)) {
        foreach ($doc->sm_publisher as $publisher) {
          $publishers[] = [
            'label' => $publisher,
            'path' => "search/?publisher={$publisher}",
          ];
        }
      }

      $topics = [];

      if (isset($doc->sm_field_topic)) {
        foreach ($doc->sm_field_topic as $topic) {
          $topics[] = [
            'label' => $topic,
            'path' => "search?category={$topic}&scope=matches",
          ];
        }
      }

      $subjects = [];

      if (isset($doc->zm_subject)) {
        foreach ($doc->zm_subject as $subject) {
          $subject = json_decode($subject);
          $subject->path = "search?subject={$subject->name}";
          $subjects[] = $subject;
        }
      }

      $partners_map = [
        'Arabic collections online' => 'المجموعات العربية على الانترنت',
        'New York University Libraries' => 'مكتبات جامعة نيويورك',
        'Princeton University Libraries' => 'مكتبات جامعة برينستون',
        'Cornell University Libraries' => 'مكتبات جامعة كورنيل',
        'Columbia University Libraries' => 'مكتبات جامعة كولومبيا',
        'American University of Beirut' => 'الجامعة الاميركية في بيروت',
        'American University in Cairo' => 'الجامعة الاميركية بالقاهرة',
        'The American University in Cairo' => 'الجامعة الاميركية بالقاهرة',
        'United Arab Emirates National Archives' => 'الامارات العربية المتحدة - الارشيف الوطني',
      ];

      $partners = [];

      $partners_ar = [];

      if (isset($doc->zm_partner)) {
        foreach ($doc->zm_partner as $partner) {
          $partner = json_decode($partner);
          $partner->path = "search?partner={$partner->name}";
          $partners[] = $partner;
          if (isset($partners_map[$partner->name])) {
            $partners_ar[] = [
              'label' => $partners_map[$partner->name],
              'path' => "search?partner={$partner->name}",
            ];
          }
        }
      }

      $authors = [];

      if (isset($doc->sm_author)) {
        foreach ($doc->sm_author as $author) {
          $authors[] = [
            'label' => $author,
            'path' => "search?subject={$author}",
          ];
        }
      }

      $authors_ar = [];

      if (isset($doc->sm_ar_author)) {
        foreach ($doc->sm_ar_author as $author) {
          $authors_ar[] = [
            'label' => $author,
            'path' => "search?subject={$author}",
          ];
        }
      }

      $pdf_hi = [];

      if (isset($doc->zm_pdf_hi) && isset($doc->zm_pdf_hi[0])) {
        $pdf_hi = json_decode($doc->zm_pdf_hi[0]);
      }

      $pdf_lo = [];

      if (isset($doc->zm_pdf_lo) && isset($doc->zm_pdf_lo[0])) {
        $pdf_lo = json_decode($doc->zm_pdf_lo[0]);
      }

      $pubdate = 'n.d.';

      if (isset($doc->ss_pubdate) && isset($doc->ss_pubdate)) {
        $pubdate = $doc->ss_pubdate;
      }

      $documents[] = [
        'en' => [
          'title' => $doc->ss_title_long,
          'identifier' => $doc->ss_book_identifier,
          'path' => "book/{$doc->ss_book_identifier}/1",
          'noid' => $doc->ss_noid,
          'handle' => $doc->ss_handle,
          'manifest' => $doc->ss_manifest,
          'subjects' => $subjects,
          'pubdate' => $pubdate,
          'pdf' => [
            'hi' => $pdf_hi,
            'lo' => $pdf_lo,
          ],
          'authors' => $authors,
          'partners' => $partners,
          'topics' => $topics,
          'publishers' => $publishers,
          'provider' => $providers,
          'publocation' => $publocation,
        ],
        'ar' => [
          'title' => $doc->ss_ar_title_long,  // ok
          'identifier' => $doc->ss_book_identifier,  // ok
          'path' => "book/{$doc->ss_book_identifier}/1?lang=ar",  // ok
          'noid' => $doc->ss_noid,  // ok
          'handle' => $doc->ss_handle,  // ok
          'manifest' => $doc->ss_manifest,  // ok
          'subjects' => [], // we do not display subjects in ar?
          'pubdate' => $pubdate,  // ok
          'pdf' => [
            'hi' => $pdf_hi,
            'lo' => $pdf_lo,
          ],
          'authors' => $authors_ar,  // ok
          'partners' => $partners_ar,
          'topics' => $topics,
          'publishers' => $publishers,
          'provider' => $providers,
          'publocation' => $ar_publocation,
        ],
      ];
    }

    return [
      'documents' => $documents,
      'total' => $total,
      'rows' => $rows,
      'page' => ($start / $rows) + 1,
    ];
  }

  /**
   * Orchestrates the search
   * new search method, where building the solr query doesn't happen in the same place as excuting it
   * public function search2(Request $request, string $scopeIs = 'matches', string $sortBy = 'score desc'): array
   * use the previous YUI implementation for reference https://github.com/NYULibraries/aco-site/blob/main/source/js/search.js
   * @param string $query
   * @param string $scope 'matches', 'contains all', 'contains any'
   * @param string $sortBy 'asc, 'desc'
   * @param int $rowStart
   * @param int $rows
   */
  public function search2(string $query = '*', string $scopeIs = 'matches', string $sortField, string $sortDir, $rowStart, $rows): array
  // public function search2($query): array
  {
    echo "running serach2 \n";
    echo "-----args-----\n";
    echo "query: $query \n";
    echo "scopeIs: $scopeIs \n";
    echo "sortField: $sortField \n";
    echo "sortDir: $sortDir \n";
    // echo "sortBy: $sortBy \n";
    echo "rowStart: $rowStart \n";
    echo "rows: $rows \n";
    echo "-----args-----\n";

    $BuiltQuery = $this->buildQuery($query, $scopeIs, $sortField, $sortDir, $rowStart, $rows);
    $compareQuery = "select?wt=json&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))";
    // $comparison = compareQueryToUrl($BuiltQuery, $query, $compareQuery)
    // // THIS IS WHERE THE QUERY IS EXECUTED
    // dump($BuiltQuery);
    // $resultset = $this->solrClient->select($query);

    // // 7. extract more data form the request
    // // returns documents and facets
    // $total = $resultset->getNumFound();

    // $docs = iterator_to_array($resultset);

    // $documents = [];

    // // 8. data transformation
    // foreach ($docs as $doc) {

    //   $publocation = [];

    //   if (isset($doc->ss_publocation)) {
    //     $publocation[] = [
    //       'label' => $doc->ss_publocation,
    //       'path' => "search/?provider={$doc->ss_publocation}",
    //     ];
    //   }

    //   $ar_publocation = [];

    //   if (isset($doc->ss_ar_publocation)) {
    //     $ar_publocation[] = [
    //       'label' => $doc->ss_ar_publocation,
    //       'path' => "search/?provider={$doc->ss_ar_publocation}",
    //     ];
    //   }

    //   $providers = [];

    //   if (isset($doc->sm_provider_label)) {
    //     foreach ($doc->sm_provider_label as $provider) {
    //       $providers[] = [
    //         'label' => $provider,
    //         'path' => "search/?provider={$provider}",
    //       ];
    //     }
    //   }

    //   $publishers = [];

    //   if (isset($doc->sm_publisher)) {
    //     foreach ($doc->sm_publisher as $publisher) {
    //       $publishers[] = [
    //         'label' => $publisher,
    //         'path' => "search/?publisher={$publisher}",
    //       ];
    //     }
    //   }

    //   $topics = [];

    //   if (isset($doc->sm_field_topic)) {
    //     foreach ($doc->sm_field_topic as $topic) {
    //       $topics[] = [
    //         'label' => $topic,
    //         'path' => "search?category={$topic}&scope=matches",
    //       ];
    //     }
    //   }

    //   $subjects = [];

    //   if (isset($doc->zm_subject)) {
    //     foreach ($doc->zm_subject as $subject) {
    //       $subject = json_decode($subject);
    //       $subject->path = "search?subject={$subject->name}";
    //       $subjects[] = $subject;
    //     }
    //   }

    //   $partners_map = [
    //     'Arabic collections online' => 'المجموعات العربية على الانترنت',
    //     'New York University Libraries' => 'مكتبات جامعة نيويورك',
    //     'Princeton University Libraries' => 'مكتبات جامعة برينستون',
    //     'Cornell University Libraries' => 'مكتبات جامعة كورنيل',
    //     'Columbia University Libraries' => 'مكتبات جامعة كولومبيا',
    //     'American University of Beirut' => 'الجامعة الاميركية في بيروت',
    //     'American University in Cairo' => 'الجامعة الاميركية بالقاهرة',
    //     'The American University in Cairo' => 'الجامعة الاميركية بالقاهرة',
    //     'United Arab Emirates National Archives' => 'الامارات العربية المتحدة - الارشيف الوطني',
    //   ];

    //   $partners = [];

    //   $partners_ar = [];

    //   if (isset($doc->zm_partner)) {
    //     foreach ($doc->zm_partner as $partner) {
    //       $partner = json_decode($partner);
    //       $partner->path = "search?partner={$partner->name}";
    //       $partners[] = $partner;
    //       if (isset($partners_map[$partner->name])) {
    //         $partners_ar[] = [
    //           'label' => $partners_map[$partner->name],
    //           'path' => "search?partner={$partner->name}",
    //         ];
    //       }
    //     }
    //   }

    //   $authors = [];

    //   if (isset($doc->sm_author)) {
    //     foreach ($doc->sm_author as $author) {
    //       $authors[] = [
    //         'label' => $author,
    //         'path' => "search?subject={$author}",
    //       ];
    //     }
    //   }

    //   $authors_ar = [];

    //   if (isset($doc->sm_ar_author)) {
    //     foreach ($doc->sm_ar_author as $author) {
    //       $authors_ar[] = [
    //         'label' => $author,
    //         'path' => "search?subject={$author}",
    //       ];
    //     }
    //   }

    //   $pdf_hi = [];

    //   if (isset($doc->zm_pdf_hi) && isset($doc->zm_pdf_hi[0])) {
    //     $pdf_hi = json_decode($doc->zm_pdf_hi[0]);
    //   }

    //   $pdf_lo = [];

    //   if (isset($doc->zm_pdf_lo) && isset($doc->zm_pdf_lo[0])) {
    //     $pdf_lo = json_decode($doc->zm_pdf_lo[0]);
    //   }

    //   $pubdate = 'n.d.';

    //   if (isset($doc->ss_pubdate) && isset($doc->ss_pubdate)) {
    //     $pubdate = $doc->ss_pubdate;
    //   }

    //   $documents[] = [
    //     'en' => [
    //       'title' => $doc->ss_title_long,
    //       'identifier' => $doc->ss_book_identifier,
    //       'path' => "book/{$doc->ss_book_identifier}/1",
    //       'noid' => $doc->ss_noid,
    //       'handle' => $doc->ss_handle,
    //       'manifest' => $doc->ss_manifest,
    //       'subjects' => $subjects,
    //       'pubdate' => $pubdate,
    //       'pdf' => [
    //         'hi' => $pdf_hi,
    //         'lo' => $pdf_lo,
    //       ],
    //       'authors' => $authors,
    //       'partners' => $partners,
    //       'topics' => $topics,
    //       'publishers' => $publishers,
    //       'provider' => $providers,
    //       'publocation' => $publocation,
    //     ],
    //     'ar' => [
    //       'title' => $doc->ss_ar_title_long,  // ok
    //       'identifier' => $doc->ss_book_identifier,  // ok
    //       'path' => "book/{$doc->ss_book_identifier}/1?lang=ar",  // ok
    //       'noid' => $doc->ss_noid,  // ok
    //       'handle' => $doc->ss_handle,  // ok
    //       'manifest' => $doc->ss_manifest,  // ok
    //       'subjects' => [], // we do not display subjects in ar?
    //       'pubdate' => $pubdate,  // ok
    //       'pdf' => [
    //         'hi' => $pdf_hi,
    //         'lo' => $pdf_lo,
    //       ],
    //       'authors' => $authors_ar,  // ok
    //       'partners' => $partners_ar,
    //       'topics' => $topics,
    //       'publishers' => $publishers,
    //       'provider' => $providers,
    //       'publocation' => $ar_publocation,
    //     ],
    //   ];
    // }

    // return [
    //   'documents' => $documents,
    //   'total' => $total,
    //   'rows' => $rows,
    //   'page' => ($start / $rows) + 1,
    // ];
    return [
      'documents' => [],
      'total' => "??",
      'rows' => $rows,
      'page' => ($rowStart / $rows) + 1,
    ];

    // return [
    //   'documents' => [],
    //   'scopeIs' => $scopeIs,
    //   'sortBy' => $sortBy,
    //   'query' => $query,
    // ];
  }

  function compareQueryToUrl(Client $client, Query $query, string $oldUrl)
  {
    // 1. Get the RequestBuilder for this specific query type
    $requestBuilder = $client->getAdapter()->getRequestBuilder($query);
    $request = $requestBuilder->build($query);

    // 2. Extract the parameters from the Solarium query object
    $newParams = $request->getParams();

    // 3. Parse the parameters from your old URL
    $urlParts = parse_url($oldUrl);
    $oldParams = [];
    if (isset($urlParts['query'])) {
      parse_str($urlParts['query'], $oldParams);
    }

    // 4. Find the differences
    $added = array_diff_assoc($newParams, $oldParams);
    $removed = array_diff_assoc($oldParams, $newParams);

    return [
      'is_equal' => ($newParams == $oldParams),
      'added_or_changed' => $added,
      'removed_from_old' => $removed,
      'full_new_params'  => $newParams
    ];
  }
}
