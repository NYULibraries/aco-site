<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class DiscoveryCollection
 *
 * This collection resource wraps a set of search results and injects Solr-style
 * pagination metadata. It ensures the frontend receives a consistent structure
 * regardless of the search engine's internal response format.
 *
 * @property \Illuminate\Support\Collection $collection The underlying collection of DiscoveryResource items.
 */
class DiscoveryCollection extends ResourceCollection
{
    /**
     * Execution metadata passed from the Search Service.
     *
     * Typically includes 'numFound', 'start', and 'rows'.
     */
    protected array $meta;

    /**
     * Create a new resource collection instance.
     *
     * @param  mixed  $resource  The collection of search result documents.
     * @param  array  $meta  Mapping of Solr execution metadata.
     */
    public function __construct($resource, array $meta)
    {
        parent::__construct($resource);
        $this->meta = $meta;
    }

    /**
     * Transform the resource collection into an array.
     *
     * Maps the search results through the DiscoveryResource and calculates
     * pagination logic based on Solr's 'start' and 'rows' parameters.
     *
     * @return array{documents: array, total: int, start: int, rows: int, page: int}
     */
    public function toArray(?Request $request = null): array
    {
        // Extract pagination variables from meta with fallbacks
        $start = (int) ($this->meta['start'] ?? 0);
        $total = (int) ($this->meta['numFound'] ?? 0);
        $rows = (int) ($this->meta['rows'] ?? 10);

        return [
            /** @var array List of transformed document resources */
            'documents' => DiscoveryResource::collection($this->collection)
                ->map(fn ($resource) => $resource->toArray($request))
                ->all(),

            /** @var int Total number of documents matching the query in Solr */
            'total' => $total,

            /** @var int The offset (starting point) of the current result set */
            'start' => $start,

            /** @var int Number of results per page */
            'rows' => $rows,

            /** @var int Current page index calculated from Solr offset */
            'page' => ($rows > 0) ? ($start / $rows) + 1 : 1,
        ];
    }
}
