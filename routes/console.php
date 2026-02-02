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
Artisan::command(
  'solr:search {searchString="*:*"} {fieldSelect="q"} {scopeIs="matches"} {sortField="score"} {sortDir="asc"} {start=0} {rows=10}',
  function (string $searchString, string $fieldSelect, string $scopeIs, string $sortField, string $sortDir, int $start, int $rows, SolrService $solrService) {
    $results = $solrService->search(
      fieldSelect: $fieldSelect, // same as scopeIs??
      scopeIs: $scopeIs,
      sortField: $sortField,
      sortDir: $sortDir,
      start: $start,
      rows: $rows,
      searchString: $searchString,
    );

    dump($results);
  }
)->purpose('Run basic search through the search service');
