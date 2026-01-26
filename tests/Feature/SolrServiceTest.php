<?php

use App\Services\SolrService;
use Solarium\Client;

describe('solr search service', function () {

  it('checks the solr service class exists', function () {
    expect(class_exists(SolrService::class))->toBeTrue();
    expect(method_exists(SolrService::class, 'search'))->toBeTrue();
  });

  // it('calls the search function', function (SolrService $solrService) {
  it('compares query: q matches arabs score asc 0 10', function () {
    // import the solr service
    $solrService = app(SolrService::class);

    // enter the test input
    $fieldSelect = 'q'; // for anyField
    $query = 'arabs';
    $scopeIs = 'matches';
    $sortField = 'score';
    $sortDir = 'desc';
    $rowStart = 0;
    $rows = 10;

    // base URL to compare
    $compareQuery = "select?wt=json&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))" . PHP_EOL;
    // $decodedQuery = urldecode($compareQuery);
    // dump($compareQuery);
    // dump($decodedQuery);
    // echo "$compareQuery \n";

    // call the searach method
    $results = $solrService->buildQuery($fieldSelect, $query, $scopeIs, $sortField, $sortDir, 0, 10);
    // dump($results);

    // compare query to query
    $oldQuery = urldecode("((content_und:arabs OR content_und_ws:arabs OR content_en:arabs OR content:arabs))");
    $newQuery = $results->getQuery();
    expect($oldQuery)->toBe($newQuery);

    // SORTFIELD
    $newSort = $results->getSorts();
    foreach ($newSort as $key => $value) {
      expect($key)->toBe($sortField);
      expect($value)->toBe($sortDir);
      break; // should only be one iteration
    }

    // ROW START AND ROWS
    $options = $results->getOptions();
    dump($options['start']);
    expect($options['start'])->toBe($rowStart);
    expect($options['rows'])->toBe($rows);

    // FIELD LIST fl
    $params = $results->getParams();
    expect($params['fl'])->toBe("*"); // field list is always *, nothing more or less
  });

  it('compares query: q containsAny arabs score desc 0 10', function () {
    // import the solr service
    $solrService = app(SolrService::class);

    // enter the test input
    $fieldSelect = 'q'; // for anyField
    $query = 'arabs';
    $scopeIs = 'containsAny';
    $sortField = 'score';
    $sortDir = 'desc';
    $rowStart = 0;
    $rows = 10;

    // base URL to compare
    // $compareQuery = "select?wt=json&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))" . PHP_EOL;
    $compareQuery = "select?wt=json&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))" . PHP_EOL;
    // $decodedQuery = urldecode($compareQuery);
    // dump($compareQuery);
    // dump($decodedQuery);
    // echo "$compareQuery \n";

    // call the searach method
    $results = $solrService->buildQuery($fieldSelect, $query, $scopeIs, $sortField, $sortDir, 0, 10);
    // dump($results);

    // compare query to query
    $oldQuery = urldecode("((content_und:arabs OR content_und_ws:arabs OR content_en:arabs OR content:arabs))");
    $newQuery = $results->getQuery();
    expect($oldQuery)->toBe($newQuery);

    // SORTFIELD
    $newSort = $results->getSorts();
    foreach ($newSort as $key => $value) {
      expect($key)->toBe($sortField);
      expect($value)->toBe($sortDir);
      break; // should only be one iteration
    }

    // ROW START AND ROWS
    $options = $results->getOptions();
    dump($options['start']);
    expect($options['start'])->toBe($rowStart);
    expect($options['rows'])->toBe($rows);

    // FIELD LIST fl
    $params = $results->getParams();
    expect($params['fl'])->toBe("*"); // field list is always *, nothing more or less
  });

  it('compares query: q containsAll arabs score asc 0 10', function () {
    // import the solr service
    $solrService = app(SolrService::class);

    // enter the test input
    $fieldSelect = 'q'; // for anyField
    $query = 'arabs';
    $scopeIs = 'containsAll';
    $sortField = 'score';
    $sortDir = 'desc';
    $rowStart = 0;
    $rows = 10;

    // base URL to compare
    $compareQuery = "select?wt=json&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))" . PHP_EOL;
    // $decodedQuery = urldecode($compareQuery);
    // dump($compareQuery);
    // dump($decodedQuery);
    // echo "$compareQuery \n";

    // call the searach method
    $results = $solrService->buildQuery($fieldSelect, $query, $scopeIs, $sortField, $sortDir, 0, 10);
    // dump($results);

    // compare query to query
    $oldQuery = urldecode("((content_und:arabs OR content_und_ws:arabs OR content_en:arabs OR content:arabs))");
    $newQuery = $results->getQuery();
    expect($oldQuery)->toBe($newQuery);

    // SORTFIELD
    $newSort = $results->getSorts();
    foreach ($newSort as $key => $value) {
      expect($key)->toBe($sortField);
      expect($value)->toBe($sortDir);
      break; // should only be one iteration
    }

    // ROW START AND ROWS
    $options = $results->getOptions();
    dump($options['start']);
    expect($options['start'])->toBe($rowStart);
    expect($options['rows'])->toBe($rows);

    // FIELD LIST fl
    $params = $results->getParams();
    expect($params['fl'])->toBe("*"); // field list is always *, nothing more or less
  });

  // it('compares query: title containsAll arabs score desc 0 10', function () {
  //   // import the solr service
  //   $solrService = app(SolrService::class);

  //   // enter the test input
  //   $fieldSelect = 'title'; // for anyField
  //   $query = 'arabs';
  //   $scopeIs = 'containsAll';
  //   $sortField = 'score';
  //   $sortDir = 'desc';
  //   $rowStart = 0;
  //   $rows = 10;

  //   // base URL to compare
  //   $compareQuery = "select?wt=json&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))" . PHP_EOL;
  //   // $decodedQuery = urldecode($compareQuery);
  //   // dump($compareQuery);
  //   // dump($decodedQuery);
  //   // echo "$compareQuery \n";

  //   // call the searach method
  //   $results = $solrService->buildQuery($fieldSelect, $query, $scopeIs, $sortField, $sortDir, 0, 10);
  //   // dump($results);

  //   // compare query to query
  //   $oldQuery = urldecode("((content_und:arabs OR content_und_ws:arabs OR content_en:arabs OR content:arabs))");
  //   $newQuery = $results->getQuery();
  //   expect($oldQuery)->toBe($newQuery);

  //   // SORTFIELD
  //   $newSort = $results->getSorts();
  //   foreach ($newSort as $key => $value) {
  //     expect($key)->toBe($sortField);
  //     expect($value)->toBe($sortDir);
  //     break; // should only be one iteration
  //   }

  //   // ROW START AND ROWS
  //   $options = $results->getOptions();
  //   dump($options['start']);
  //   expect($options['start'])->toBe($rowStart);
  //   expect($options['rows'])->toBe($rows);

  //   // FIELD LIST fl
  //   $params = $results->getParams();
  //   expect($params['fl'])->toBe("*"); // field list is always *, nothing more or less
  // });
})->only();
