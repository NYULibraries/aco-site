<?php

namespace App\Services;

use App\Http\Resources\DiscoveryCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Cache\Repository;
use RuntimeException;
use Solarium\Client;
use Throwable;

/**
 * FeaturedBooksService
 *
 * This service manages the retrieval of curated "Featured" book lists
 * from the Solr search index.
 *
 * Responsibilities:
 * - Build Solr queries (filters, sorting, pagination)
 * - Apply identifier-based pinning (fq with OR logic)
 * - Normalize Solr failures
 * - Cache deterministic featured queries
 *
 * Key Features:
 * - Deterministic Ordering: Manually re-sorts Solr results to match the
 * provided identifier array order.
 * - Multi-Layer Caching: Uses Cache Tags (where available) and a
 * Global Versioning system to allow instant invalidation of all cached lists.
 * - Fault Tolerance: Normalizes Solarium exceptions into standard RuntimeExceptions.
 */
class FeaturedBooksService
{

    private const FEATURED_CACHE_TAG = 'featured-books';

    private const FEATURED_CACHE_KEY_PREFIX = 'featured_books';

    /**
     * Cache TTL for featured lists (in seconds).
     */
    private const FEATURED_CACHE_TTL = 3600; // 1 hour

    /**
     * Default Solr fields to return.
     */
    private array $defaultFields;

    /**
     * Default Solr sorting.
     */
    private array $defaultSort;

    /**
     * Default Solr filter queries applied to all searches.
     */
    private array $defaultFilters;

    /**
     * Pagination defaults.
     */
    private int $defaultStart;

    private int $defaultRows;

    private int $cacheVersion;

    public function __construct(
        private readonly Client $solrClient
    ) {
        // Load query logic defaults
        $this->defaultFilters = config('featured.defaults.filters', []);
        $this->defaultStart =  config('featured.defaults.start', 0);
        $this->defaultRows =  config('featured.defaults.rows', 10);
        $this->cacheVersion = (int) Cache::get('featured_books_version', 1);
        $this->defaultFields = config('featured.defaults.fields', [ '*' ]);
        $this->defaultSort = config('featured.defaults.sort', []);
    }

    /**
     * Public entry point for featured books.
     */
    public function featured(array $identifiers, ?int $rows = null): DiscoveryCollection
    {
        return $this->byIdentifiers($identifiers, $rows, true);
    }

    /**
     * Fetch documents by explicit book identifiers.
     *
     * This builds an fq clause like:
     * (ss_book_identifier:A OR ss_book_identifier:B ...)
     *
     * @param array $identifiers
     * @param int   $rows
     * @param bool  $cache
     */
    public function byIdentifiers(
        array $identifiers,
        ?int $rows = null,
        bool $cache = false
    ): DiscoveryCollection {

        if (empty($identifiers)) {
            return new DiscoveryCollection(collect([]), ['numFound' => 0, 'start' => 0, 'rows' => $rows]);
        }

        // Fallback to the class property if null was passed
        $rows = $rows ?? $this->defaultRows;

        $cacheKey = $this->buildFeaturedCacheKey($identifiers, $rows);

        if ($cache) {
            return $this->cache()->remember(
                $cacheKey,
                self::FEATURED_CACHE_TTL,
                fn () => $this->executeSearch(
                    searchString: '*',
                    options: [
                        'rows' => $rows,
                        'identifier_filter' => $identifiers,
                    ]
                )
            );
        }

        return $this->executeSearch(
            searchString: '*',
            options: [
                'rows' => $rows,
                'identifier_filter' => $identifiers,
            ]
        );
    }

    /**
     * Core Solr search execution.
     */
    private function executeSearch(
        string $searchString,
        array $options = []
    ): DiscoveryCollection {
        $rows  = (int) ($options['rows'] ?? $this->defaultRows);
        $start = (int) ($options['start'] ?? $this->defaultStart);

        // We use the config-defined array of sorts
        $sorts = $this->defaultSort;

        $query = $this->solrClient->createSelect();

        /**
         * 1. Fields
         * Solarium's setFields is cleaner than addParam('fl')
         */
        $query->setFields($this->defaultFields);

        /**
         * 2. Sorting
         * This applies the nested array logic from your config
         */
        foreach ($sorts as $sort) {
            $query->addSort($sort['field'], $sort['dir']);
        }

        /**
         * 3. Default filters
         */
        foreach ($this->defaultFilters as $filter) {
            $query->createFilterQuery($filter['field'])
                ->setQuery(sprintf('%s:%s', $filter['field'], $filter['value']));
        }

        /**
         * 4. Identifier filter (featured pinning)
         */
        if (! empty($options['identifier_filter'])) {
            $fq = collect($options['identifier_filter'])
                ->map(fn ($id) => sprintf('ss_book_identifier:%s', $id))
                ->implode(' OR ');

            $query->createFilterQuery('featured_identifiers')
                ->setQuery("({$fq})");
        }

        /**
         * 5. Search string handling
         */
        if ($searchString === '*' || trim($searchString) === '') {
            $query->setQuery('*:*');
        } else {
            $query->setQuery($query->getHelper()->escapePhrase($searchString));
        }

        $query->setStart($start);
        $query->setRows($rows);

        try {
            $results = $this->solrClient->execute($query);
            $response = $results->getData()['response'] ?? [];
            $docs = $response['docs'] ?? [];

            /**
             * 6. Re-ranking Logic
             * HINT: If we have an identifier_filter, it means we have a "Curated" list.
             * We prioritize the order of the $identifiers array OVER the Solr sort.
             */
            if (! empty($options['identifier_filter'])) {
                $docs = $this->reorderByIdentifiers(
                    $docs,
                    $options['identifier_filter']
                );
            }

            return new DiscoveryCollection(collect($docs), [
                'start' => $response['start'] ?? $start,
                'rows' => $rows,
                'numFound' => $response['numFound'] ?? 0,
            ]);

        } catch (Throwable $e) {
            Log::error('Solr query failed in FeaturedBooksService', [
                'query' => $query->getQuery(),
                'exception' => $e->getMessage(),
            ]);

            throw new RuntimeException('Search service unavailable.', 0, $e);
        }
    }

    /**
     * Build a deterministic cache key for featured lists.
     */
    private function buildFeaturedCacheKey(array $identifiers, int $rows): string
    {
        // Fix: Sort a COPY so we don't ruin the original manual order
        $sortedIds = $identifiers;
        sort($sortedIds);

        $key = implode('|', $sortedIds) . ":rows={$rows}";

        return self::FEATURED_CACHE_KEY_PREFIX . ":v{$this->cacheVersion}:" . md5($key);
    }

    /**
     * Reorders Solr results to match the order of the input identifiers array.
     */
    private function reorderByIdentifiers(array $docs, array $identifiers): array
    {
        $indexedDocs = [];
        foreach ($docs as $doc) {
            $indexedDocs[$doc['ss_book_identifier']] = $doc;
        }

        $ordered = [];
        foreach ($identifiers as $id) {
            if (isset($indexedDocs[$id])) {
                $ordered[] = $indexedDocs[$id];
            }
        }

        return $ordered;
    }

   /**
    * clearFeaturedCache()
    *
    * Invalidates all existing featured book caches globally.
    * It increments a persistent version number, which changes the cache key
    * prefix for all subsequent 'featured' requests.
    */
    public function clearFeaturedCache(): void
    {
        // 1. Use increment() to handle race conditions (returns the new value)
        // 2. Update the local property so the current request uses the new version immediately
        $this->cacheVersion = (int) Cache::increment('featured_books_version');

        // Note: If the key doesn't exist, increment() might fail or start from 1
        // depending on the driver. To be safe:
        if ($this->cacheVersion <= 1) {
            Cache::forever('featured_books_version', 1);
            $this->cacheVersion = 1;
        }
    }

    /**
     * Get the cache repository, with tags if supported.
     */
    private function cache(): Repository
    {
        return Cache::supportsTags()
            ? Cache::tags([self::FEATURED_CACHE_TAG])
            : Cache::store();
    }

}
