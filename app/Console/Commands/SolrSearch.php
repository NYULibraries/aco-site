<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SolrService;
use Illuminate\Support\Facades\Log;

class SolrSearch extends Command
{
  /**
   * artisan command used to call the SolrService
   *
   * @var string
   */
  protected $signature = 'solr:search
                          {searchString="*" : string to search for}
                          {--F|field-select=q : field used to search over}
                          {--S|scope=matches : how well to match the string}
                          {--sort-field=score : field to use for sort}
                          {--sort-dir=desc : direction of sort}
                          {--start=0 : pagination start}
                          {--rows=10 : how many items to show}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Run basic search through the search service';

  /**
   * Execute the console command.
   */
  public function handle(SolrService $solrService)
  {
    Log::info("Artisan Command", [
      "fieldSelect" => $this->option('field-select'),
      "scope" => $this->option('scope'),
      "searchString" => $this->argument('searchString'),
      "sortField" => $this->option('sort-field'),
      "sortDir" => $this->option('sort-dir'),
      "start" => (int) $this->option('start'),
      "rows" => (int) $this->option('rows'),
    ]);
    try {
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
      return Command::SUCCESS;
    } catch (\Exception $e) {
      $this->error("Search failed: " . $e->getMessage());
      Log::error("Solr Search Failed", ['error' => $e->getMessage()]);

      return Command::FAILURE;
    }
  }
}
