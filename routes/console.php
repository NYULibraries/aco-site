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
  // 'solr:search {searchString="*:*"} {fieldSelect="q"} {scopeIs="matches"} {sortField="score"} {sortDir="asc"} {start=0} {rows=10}',
  'solr:search
    {searchString="*"}
    {--F|field-select=q}
    {--S|scope=matches}
    {--sort-field=score}
    {--sort-dir=desc}
    {--start=0}
    {--rows=10}',
  //
  // function (string $searchString, string $fieldSelect, string $scopeIs, string $sortField, string $sortDir, int $start, int $rows, SolrService $solrService) {
  function (SolrService $solrService) {
    $results = $solrService->search(
      fieldSelect: $this->option('field-select'),
      scopeIs: $this->option('scope'),
      sortField: $this->option('sort-field'),
      sortDir: $this->option('sort-dir'),
      start: (int) $this->option('start'),
      rows: (int) $this->option('rows'),
      searchString: $this->argument('searchString'),
    );

    dump($results);
  }
)->purpose('Run basic search through the search service');
