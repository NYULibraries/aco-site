<?php

namespace App\Console\Commands;

use Solarium\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\ItemCountService;
use Throwable;

class RequestSubjectsVolumesCount extends Command
{
    protected $signature = 'app:request-subjects-volumes-count';
    protected $description = 'Request subjects and volumes count from Solr and save to disk';

    private const COLLECTION_CODE = 'aco';
    private const LANGUAGE = 'en';

    /**
     * Create a base Solr query with shared filters.
     */
    private function baseQuery(Client $client)
    {
        $query = $client->createSelect();
        $query->setQuery('*:*');
        $query->setRows(0);

        $query->createFilterQuery('collection')
            ->setQuery('sm_collection_code:' . self::COLLECTION_CODE);

        $query->createFilterQuery('language')
            ->setQuery('ss_language:' . self::LANGUAGE);

        return $query;
    }

    /**
     * Fetch total volume count.
     */
    private function getVolumeCount(Client $client): int
    {
        $query = $this->baseQuery($client);
        $result = $client->execute($query);

        return (int) $result->getNumFound();
    }

    /**
     * Fetch unique subject count via faceting.
     */
    private function getSubjectCount(Client $client): int
    {
        $query = $this->baseQuery($client);

        $facetSet = $query->getFacetSet();
        $facetSet->createFacetField('subjects')
            ->setField('im_field_subject')
            ->setMinCount(1)
            ->setLimit(-1);

        $result = $client->execute($query);
        $facet = $result->getFacetSet()->getFacet('subjects');

        return $facet ? iterator_count($facet) : 0;
    }

    public function handle(Client $solrClient): int
    {
        try {
            $this->info('Fetching volume count…');
            $volumes = $this->getVolumeCount($solrClient);

            $this->info('Fetching subject count…');
            $subjects = $this->getSubjectCount($solrClient);

            if (!ItemCountService::saveItemCounts([
                'volumes'  => $volumes,
                'subjects' => $subjects,
            ])) {
                $this->error('Failed to save item counts.');
                return Command::FAILURE;
            }

            $this->info("Saved → Volumes: {$volumes}, Subjects: {$subjects}");

            return Command::SUCCESS;

        } catch (Throwable $e) {
            Log::error('Solr count command failed', [
                'exception' => $e,
            ]);

            $this->error('Solr error: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
