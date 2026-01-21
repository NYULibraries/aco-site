<?php

use App\Services\SolrService;
use Solarium\Client;

describe('solr search service', function () {

  it('exists', function () {
    expect(class_exists(SolrService::class))->toBeTrue();
    expect(method_exists(SolrService::class, 'search'))->toBeTrue();
  });

  // it('calls the search function', function (SolrService $solrService) {
  it('calls the buildQuery function', function () {
    // import the solr service
    $solrService = app(SolrService::class);

    // enter the test input
    $query = 'arabs';
    $scopeIs = '';
    $sortField = '';
    $sortDir = '';
    $rowStart = 0;
    $rows = 10;

    // base URL to compare
    $compareQuery = "select?wt=json&fl=*&fq=bundle:dlts_book&fq=sm_collection_code:aco&fq=ss_language:en&rows=10&start=0&sort=score%20desc&q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))" . PHP_EOL;
    echo "$compareQuery \n";

    // call the searach method
    $results = $solrService->buildQuery($query, $scopeIs, $sortField, $sortDir, 0, 10);
    dump($results);
    // compare
    // query to query
    $oldQuery = "q=((content_und:arabs%20OR%20content_und_ws:arabs%20OR%20content_en:arabs%20OR%20content:arabs))";
    $newQuery = $results->getQuery();
    expect($oldQuery)->toBe($newQuery);
    // scopeIs to scopeIS
    // expect(true)->toBe(false);
    // sortField to sortField
    // expect(true)->toBe(false);
    // sortDir to sortDir
    // expect(true)->toBe(false);
    // rowStart to rowStart
    // expect(true)->toBe(false);
    // rows to rows
    // expect(true)->toBe(false);
  });
})->only();
