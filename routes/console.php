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
Artisan::command('solr:search {query="*:*"} {scopeIs="matches"} {sortBy="asc"}', function (SolrService $solrService, string $query, string $scopeIs, string $sortBy) {
  $results = $solrService->search2($scopeIs, $sortBy, $query);

  dump($results);
  // $this->table(['Results'], $results);
  // dump($query);
})->purpose('Run basic search through the search service');
