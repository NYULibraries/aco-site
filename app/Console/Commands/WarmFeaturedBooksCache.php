<?php

namespace App\Console\Commands;

use App\Services\FeaturedBooksService;
use Illuminate\Console\Command;

class WarmFeaturedBooksCache extends Command
{
    protected $signature = 'cache:warm-featured-books';

    protected $description = 'Warm Solr caches for featured book lists';

    public function handle(FeaturedBooksService $service): int
    {
        $lists = [
            'default' => config('featured.books'),
        ];

        foreach ($lists as $name => $identifiers) {
            $this->info("Warming featured list: {$name}");

            $service->featured($identifiers, rows: 30);
        }

        $this->info('Featured book caches warmed.');

        return self::SUCCESS;
    }
}
