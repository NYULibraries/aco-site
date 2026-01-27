<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\SolrService;

Artisan::command('inspire', function () {
  $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * artisan command used to trigger the solr service without testing or calling the whole app
 */
// Artisan::command('solr:search {query="*:*"} {scopeIs="matches"} {sortBy="asc"} {rowStart} {rows}', function (SolrService $solrService, string $query, string $scopeIs, string $sortBy, int $rowStart, int $rows) {
Artisan::command('solr:search {query} {fieldSelect} {scopeIs="matches"} {sortField="score"} {sortDir="asc"} {rowStart=0} {rows=10}', function (string $query, string $fieldSelect, string $scopeIs, string $sortField, string $sortDir, int $rowStart, int $rows, SolrService $solrService) {
  $results = $solrService->search(
    fieldSelect: $fieldSelect, // same as scopeIs??
    scopeIs: $scopeIs,
    sortField: $sortField,
    sortDir: $sortDir,
    rowStart: $rowStart,
    rows: $rows,
    query: $query,
  );

  dump($results);
  // $this->table(['Results'], $results);
  // dump($query);
})->purpose('Run basic search through the search service');
