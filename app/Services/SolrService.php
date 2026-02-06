<?php

namespace App\Services;

use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result as SolariumResult;

class SolrService
{
  private const FL_LIST = '*';
  private const BUNDLE = "bundle:dlts_book";
  private const SM_COLLECTION_CODE = "sm_collection_code:aco";
  private const SS_LANGUAGE = "ss_language:en";
  // these are all the possible fields to add to the filter query
  // we will use the request options to trim these down to use on our query
  private const FIELD_LIST = [
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

  public function __construct(private Client $solrClient) {}

  /**
   * Creates and modifies a Solarium query object according to search params passed
   *
   * previous YUI implementation for reference https://github.com/NYULibraries/aco-site/blob/main/source/js/search.js
   *
   * @param int $start - pagination start point for solr
   * @param int $rows - how many items to display after the start point
   * @param string $fieldSelect - what field the user wants to perform the search on
   * @param string $searchString - the actual search values to use
   * @param string $scope - how to match the results <matches, contains all, contains any>
   * @param string $sortField - what field to sort on
   * @param string $sortDir - what direction to sort <asc, desc>
   * @return Query - Full Solarium Query object
   */
  public function buildQuery(
    int $start,
    int $rows,
    string $fieldSelect,
    string $searchString,
    string $scope,
    string $sortField,
    string $sortDir,
  ): Query {

    $query = $this->solrClient->createSelect();

    // sanitize the user's input
    $helper = $query->getHelper();
    $sanitizedSearchString = $helper->escapeTerm($searchString);

    // FIELD LIST - fl
    $query->addParam('fl', self::FL_LIST);

    // FILTER QUERY - fq
    // these filter queries always have to happen as of 2026
    $query->createFilterQuery('bundle')->setQuery(self::BUNDLE);
    $query->createFilterQuery('sm_collection_code')->setQuery(self::SM_COLLECTION_CODE);
    $query->createFilterQuery('ss_language')->setQuery(self::SS_LANGUAGE);

    // when creating query, we put the users searchInput in two possible places: the `fq` or the `q`
    $fq = [];
    // looping the fieldList to use the wording in the map on our fq
    foreach (self::FIELD_LIST as $key => $map) {
      // skip any actio if the keys don't match
      if ($fieldSelect !== $key) continue;
      if ($scope === 'matches') {
        // combine the FieldList keywords with the user's input
        $parts = array_map(fn($f) => "$f:\"$sanitizedSearchString\"", $map['match']);
        // flatten those parts
        $fq[] = '(' . implode(' OR ', $parts) . ')';
      } else {
        // split the user's query into words
        $words = preg_split('/\s+/', $sanitizedSearchString);
        // catcher for keywords
        $clauses = [];
        // each words of the users input
        foreach ($words as $w) {
          $sub = array_map(fn($f) => "$f:\"$w\"", $map['contains']);
          $clauses[] = '(' . implode(' OR ', $sub) . ')';
        }
        $fq[] = '(' . implode(($scope === 'containsAny') ? ' OR ' : ' AND ', $clauses) . ')';
      }
    }

    // set filter queries
    foreach ($fq as $filter) {
      $query->createFilterQuery($filter)->setQuery($filter);
    }

    // QUERY q
    if ($fieldSelect == 'q') {
      if ($scope === 'matches') {
        $finalQuery = "(content_und:\"$sanitizedSearchString\" OR content_und_ws:\"$sanitizedSearchString\" OR content_en:\"$sanitizedSearchString\" OR content:\"$sanitizedSearchString\")";
        $query->setQuery($finalQuery);
      } else {
        $words = preg_split('/\s+/', $sanitizedSearchString);
        $parts = [];
        foreach ($words as $w) {
          $parts[] = "(content_und:$w OR content_und_ws:$w OR content_en:$w OR content:$w)";
        }
        $finalQuery = ('(' . implode(($scope === 'containsAny') ? ' OR ' : ' AND ', $parts) . ')');
        $query->setQuery($finalQuery);
      }
    } else {
      // setting query to * makes all scores be 1.0 removing
      // matching everything makes all documents the same importance score of 1.0
      $query->setQuery("*");
    }

    // PAGINATION
    $query->setStart($start); // what item to start from (page)
    $query->setRows($rows);  // how many items to show (page size)

    // SORT
    $sortDirec = ($sortDir == 'desc') ? $query::SORT_DESC : $query::SORT_ASC;
    $query->addSort($sortField, $sortDirec);

    return $query;
  }

  /**
   * Transforms Solarium Result into usable data by our application
   * @param $resultset - Solarium result from executing a query
   * @return array - final transformed data
   */
  public function transformData(SolariumResult $resultset): array
  {

    $total = $resultset->getNumFound();
    $docs = $resultset->getDocuments();

    $documents = [];
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

    $rows2 = $resultset->getQuery()->getOption('rows');
    $start2 = $resultset->getQuery()->getOption('start');

    return [
      'documents' => $documents,
      'total' => $total,
      'rows' => $rows2,
      'page' => ($start2 / $rows2) + 1,
    ];
  }

  /**
   * Builds the query, executes Solr search, transforms data
   * use the previous YUI implementation for reference https://github.com/NYULibraries/aco-site/blob/main/source/js/search.js
   * @param string fieldSelect - the field the user wants to search over
   * @param string $searchString - what the user is searching
   * @param string $scope - how well to match the search string <'matches', 'contains all', 'contains any>
   * @param string $sortField - what field to use as sorting point
   * @param string $sortDir - direction of sort <'asc, 'desc'>
   * @param int $start - pagination start
   * @param int $rows - how many items to paginate
   */
  public function search(
    string $fieldSelect = 'q',
    string $searchString = '*:*',
    string $scope = 'matches',
    string $sortField = 'score',
    string $sortDir = 'desc',
    int $start = 0,
    int $rows = 10
  ): array {
    $BuiltQuery = $this->buildQuery(
      fieldSelect: $fieldSelect,
      searchString: $searchString,
      scope: $scope,
      sortField: $sortField,
      sortDir: $sortDir,
      start: $start,
      rows: $rows
    );

    $resultset = $this->solrClient->select($BuiltQuery);

    $finalData = $this->transformData($resultset);
    return $finalData;
  }

  /**
   * Parses the old solr URLs into it's query parameters
   *
   * method used primarily in the test suite to verify that
   * the correct filter queries were generated by Solarium
   *
   * @param string $url - the full original URL in production to be parsed
   * @return array<string, array|string> - associative array of query parameters
   */
  public function parseSolrUrl(string $url): array
  {
    // Parse the URL to get the query string (after the ?)
    $queryString = parse_url($url, PHP_URL_QUERY);

    if (!$queryString) {
      return [];
    }

    $result = [];

    // Explode by '&' to get raw key=value pairs
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
    // using the toEqualCanonicalizing comparison
    if (isset($result['fq']) && !is_array($result['fq'])) {
      $result['fq'] = [$result['fq']];
    }
    return $result;
  }
}
