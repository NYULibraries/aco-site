<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\SolrService;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
  $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * artisan command used to call the SolrService
 */
Artisan::command(
  'solr:search
    {searchString="*"}
    {--F|field-select=q}
    {--S|scope=matches}
    {--sort-field=score}
    {--sort-dir=desc}
    {--start=0}
    {--rows=10}',
  function (SolrService $solrService) {
    Log::info("Artisan Command", [
      "fieldSelect" => $this->option('field-select'),
      "scope" => $this->option('scope'),
      "searchString" => $this->argument('searchString'),
      "sortField" => $this->option('sort-field'),
      "sortDir" => $this->option('sort-dir'),
      "start" => (int) $this->option('start'),
      "rows" => (int) $this->option('rows'),
    ]);
    $results = $solrService->search(
      fieldSelect: $this->option('field-select'),
      scope: $this->option('scope'),
      searchString: $this->argument('searchString'),
      sortField: $this->option('sort-field'),
      sortDir: $this->option('sort-dir'),
      start: (int) $this->option('start'),
      rows: (int) $this->option('rows'),
    );

    dump($results);
  }
)->purpose('Run basic search through the search service');
