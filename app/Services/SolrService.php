<?php

namespace App\Services;

use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result as SolariumResult;
use App\Http\Resources\SolrCollectionResource;
use App\Http\Resources\SolrDocumentResource;


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
   * uses Laravel Resources
   * @param $resultset - Solarium result from executing a query
   * @return array - final transformed data
   */
  public function transformData(SolariumResult $resultset): array
  {
    $rawDocs = collect($resultset->getDocuments());
    $resourceDocs = SolrDocumentResource::collection($rawDocs);
    $collection = new SolrCollectionResource($resourceDocs, $resultset);
    // resolving as plain array for blade templates
    return $collection->resolve();
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
}
