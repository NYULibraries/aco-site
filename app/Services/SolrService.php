<?php

namespace App\Services;

use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Solarium\Core\Query\QueryInterface;
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
  ): Query {
    /**
     * Possible combinations:
     * searchString - whatever sentence the user has
     * scopeIs - matches, contains all, contains any
     * sortBy - asc, desc
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
    // fq=bundle:dlts_book
    // fq=sm_collection_code:aco
    // fq=ss_language:en
    // rows=10
    // start=0
    // sort=score%20desc
    // q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))


    $query = $this->solrClient->createSelect();

    /**
     * FIELD LIST - fl
     */
    $query->addParam('fl', '*'); // field list: all fields

    /**
     * FILTER QUERY - fq
     */
    // these filter queries always have to happen as of 2026
    $query->createFilterQuery(md5('bundle:dlts_book'))->setQuery('bundle:dlts_book');
    $query->createFilterQuery(md5('sm_collection_code:aco'))->setQuery('sm_collection_code:aco');
    $query->createFilterQuery(md5('ss_language:en'))->setQuery('ss_language:en');

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

    $helper = $query->getHelper();
    $sanitizedSearchString = $helper->escapeTerm($searchString);

    $fq = [];
    foreach ($fields as $key => $map) {
      if ($fieldSelect !== $key) continue; // go to the next loop, skip the rest
      $value = trim($fieldSelect);
      if ($scopeIs === 'matches') {
        $parts = array_map(fn($f) => "$f:\"$sanitizedSearchString\"", $map['match']);
        $fq[] = '(' . implode(' OR ', $parts) . ')';
      } else {
        $words = preg_split('/\s+/', $sanitizedSearchString);
        $clauses = [];
        foreach ($words as $w) {
          $sub = array_map(fn($f) => "$f:\"$w\"", $map['contains']);
          $clauses[] = '(' . implode(' OR ', $sub) . ')';
        }
        $fq[] = '(' . implode(($scopeIs === 'containsAny') ? ' OR ' : ' AND ', $clauses) . ')';
      }
    }

    // set filter queries
    foreach ($fq as $filter) {
      $query->createFilterQuery(md5($filter))->setQuery($filter);
    }

    /**
     * QUERY q
     * these only need to be when anyfield is selected in field select
     * q = query is the only thing coming from the Request Object
     */

    if ($fieldSelect == 'q') {
      $helper = $query->getHelper();
      $sanitizedSearchString = $helper->escapeTerm($searchString);
      if ($scopeIs === 'matches') {
        $finalQuery = "((content_und:$sanitizedSearchString OR content_und_ws:$sanitizedSearchString OR content_en:$sanitizedSearchString OR content:$sanitizedSearchString))";
        $query->setQuery($finalQuery);
      } else {
        $words = preg_split('/\s+/', $sanitizedSearchString);
        $parts = [];
        foreach ($words as $w) {
          $parts[] = "(content_und:$w OR content_und_ws:$w OR content_en:$w OR content:$w)";
        }
        $finalQuery = ('(' . implode(($scopeIs === 'containsAny') ? ' OR ' : ' AND ', $parts) . ')');
        $query->setQuery($finalQuery);
      }
    } else {
      $query->setQuery("*");
    }

    /**
     * PAGINATION
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

    $request = $this->solrClient->createRequest($query);
    $uri = $request->getUri();
    return $query;
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
  public function search(string $fieldSelect, string $query = '*', string $scopeIs = 'matches', string $sortField, string $sortDir, $rowStart, $rows): array
  {
    echo "running search2 \n";
    echo "-----args-----\n";
    echo "fieldSelect: $fieldSelect \n";
    echo "query: $query \n";
    echo "scopeIs: $scopeIs \n";
    echo "sortField: $sortField \n";
    echo "sortDir: $sortDir \n";
    // echo "sortBy: $sortBy \n";
    echo "rowStart: $rowStart \n";
    echo "rows: $rows \n";
    echo "-----args-----\n";

    $BuiltQuery = $this->buildQuery(
      fieldSelect: $fieldSelect,
      searchString: $query,
      scopeIs: $scopeIs,
      sortField: $sortField,
      sortDir: $sortDir,
      rowStart: $rowStart,
      rows: $rows
    );
    // $compareQuery = "select?wt=json&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))";
    // $comparison = compareQueryToUrl($BuiltQuery, $query, $compareQuery)
    // // THIS IS WHERE THE QUERY IS EXECUTED
    dump($BuiltQuery);
    // $resultset = $this->solrClient->select($BuiltQuery);
    // dump($resultset);

    // // 7. extract more data form the request
    // // returns documents and facets
    // $total = $resultset->getNumFound();

    // result sets are already iterable, don't convert solarium call to iterator
    // $docs = iterator_to_array($resultset);

    // catcher
    // $documents = [];

    // // TODO: figure out if this should be it's own method
    // // // 8. data transformation
    // foreach ($resultset as $doc) {

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

    //   // stop iteration on first loop
    //   break;
    // }

    // return [
    //   'documents' => $documents,
    //   'total' => $total,
    //   'rows' => $rows,
    //   'page' => ($rowStart / $rows) + 1,
    // ];
    return [];
  }

  public function parseSolrUrl(string $url): array
  {
    // 1. Parse the URL to get the query string (after the ?)
    $queryString = parse_url($url, PHP_URL_QUERY);

    if (!$queryString) {
      return [];
    }

    $result = [];

    // 2. Explode by '&' to get raw key=value pairs
    foreach (explode('&', $queryString) as $part) {
      $parts = explode('=', $part, 2);

      if (count($parts) === 2) {
        $key = urldecode($parts[0]);
        $value = urldecode($parts[1]);

        // Handle duplicate keys (common for 'fq' in Solr)
        if (isset($result[$key])) {
          if (!is_array($result[$key])) {
            $result[$key] = [$result[$key]];
          }
          $result[$key][] = $value;
        } else {
          $result[$key] = $value;
        }
      }
    }

    // Normalize single 'fq' to array for consistent comparison later
    if (isset($result['fq']) && !is_array($result['fq'])) {
      $result['fq'] = [$result['fq']];
    }

    return $result;
  }

  function getParamsFromSolarium(Client $client, QueryInterface $query): array
  {
    // Convert the Query object into a Request object
    $request = $client->createRequest($query);

    // Get the params (returns an array)
    $params = $request->getParams();

    // Normalize 'fq' to array for consistency
    if (isset($params['fq']) && !is_array($params['fq'])) {
      $params['fq'] = [$params['fq']];
    }

    return $params;
  }
}
