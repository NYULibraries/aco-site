<?php

namespace App\Console\Commands;

use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\ItemCountService;
use Throwable;

/**
 * RequestSubjectsVolumesCount
 *
 * Fetches:
 *  - Total volume count
 *  - Unique subject count
 *
 * from Solr and persists the result to disk.
 *
 * ---------------------------------------------------------------------
 * SOLR SCHEMA REQUIREMENTS
 * ---------------------------------------------------------------------
 * - sm_collection_code : string (filterable)
 * - ss_language        : string (filterable)
 * - im_field_subject   : multi-valued, facetable
 *
 */
class RequestSubjectsVolumesCount extends Command
{
    protected $signature = 'app:request-subjects-volumes-count';
    protected $description = 'Request subjects and volumes count from Solr and save to disk';

    /** Filter values */
    private const COLLECTION_CODE = 'aco';
    private const LANGUAGE = 'en';

    /** Solr field names */
    private const FIELD_COLLECTION = 'sm_collection_code';
    private const FIELD_LANGUAGE   = 'ss_language';
    private const FIELD_SUBJECT    = 'im_field_subject';

    /** Injected Solr client */
    private Client $solr;

    /**
     * Entry point for the command.
     *
     * @throws Throwable
     */
    public function handle(Client $solrClient): int
    {
        $this->solr = $solrClient;

        try {
            $this->info('Fetching volume count…');
            $volumes = $this->getVolumeCount();

            $this->info('Fetching subject count…');
            $subjects = $this->getSubjectCount();

            $saved = ItemCountService::saveItemCounts([
                'volumes'  => $volumes,
                'subjects' => $subjects,
            ]);

            if (!$saved) {
                $this->error('Failed to persist item counts.');
                return Command::FAILURE;
            }

            $this->info(
                sprintf(
                    'Counts saved successfully → Volumes: %d | Subjects: %d',
                    $volumes,
                    $subjects
                )
            );

            return Command::SUCCESS;

        } catch (Throwable $e) {
            Log::error('Solr count command failed', [
                'exception' => $e,
            ]);

            $this->error('Solr error: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Build a base Solr select query.
     *
     * - rows = 0 because only counts are required
     * - Shared filters are applied here
     */
    private function baseQuery(): Query
    {
        $query = $this->solr->createSelect();
        $query->setQuery('*:*');
        $query->setRows(0); // counts only, no documents

        $query->createFilterQuery('collection')
            ->setQuery(self::FIELD_COLLECTION . ':' . self::COLLECTION_CODE);

        $query->createFilterQuery('language')
            ->setQuery(self::FIELD_LANGUAGE . ':' . self::LANGUAGE);

        return $query;
    }

    /**
     * Fetch total number of volumes.
     *
     * @throws Throwable on Solr failure
     */
    private function getVolumeCount(): int
    {
        $query = $this->baseQuery();
        $result = $this->solr->execute($query);

        return (int) $result->getNumFound();
    }

    /**
     * Fetch unique subject count via faceting.
     *
     * IMPORTANT:
     * - This counts UNIQUE subject values
     * - It does NOT count total subject occurrences
     *
     * @throws Throwable on Solr failure
     */
    private function getSubjectCount(): int
    {
        $query = $this->baseQuery();

        $facetSet = $query->getFacetSet();
        $facetSet->createFacetField('subjects')
            ->setField(self::FIELD_SUBJECT)
            ->setMinCount(1)
            ->setLimit(-1);

        $result = $this->solr->execute($query);
        $facet = $result->getFacetSet()->getFacet('subjects');

        return $facet ? iterator_count($facet) : 0;
    }
}
