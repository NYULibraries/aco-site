<?php

namespace App\Console\Commands;

use Solarium\Client;
use Illuminate\Console\Command;
use App\Services\ItemCountService;

class RequestSubjectsVolumesCount extends Command
{
    protected $signature = 'app:request-subjects-volumes-count';
    protected $description = 'Request subjects and volumes count from Solr and save to disk';

    public function handle(Client $solrClient)
    {
        $itemCount = [
            'volumes' => 0,
            'subjects' => 0,
        ];

        try {
            $this->info('Fetching Volume counts...');

            // 1. Get Volumes Count
            $vQuery = $solrClient->createSelect();
            $vQuery->setQuery('*:*');
            $vQuery->setRows(0); // We only need getNumFound()
            $vQuery->createFilterQuery('col')->setQuery('sm_collection_code:aco');
            $vQuery->createFilterQuery('lang')->setQuery('ss_language:en');

            $vResult = $solrClient->execute($vQuery);
            $itemCount['volumes'] = (int) $vResult->getNumFound();

            $this->info('Fetching Subject counts...');

            // 2. Get Subjects (Facet) Count
            $sQuery = $solrClient->createSelect();
            $sQuery->setQuery('*:*');
            $sQuery->setRows(0);
            $sQuery->createFilterQuery('col')->setQuery('sm_collection_code:aco');

            $facetSet = $sQuery->getFacetSet();
            $facetSet->createFacetField('subjects')
                ->setField('im_field_subject')
                ->setMinCount(1)
                ->setLimit(-1);

            $sResult = $solrClient->execute($sQuery);
            $facetResult = $sResult->getFacetSet()->getFacet('subjects');
            $itemCount['subjects'] = count($facetResult);

            // 3. Save via Service
            if (ItemCountService::saveItemCounts($itemCount)) {
                $this->info("Successfully saved counts: Volumes: {$itemCount['volumes']}, Subjects: {$itemCount['subjects']}");
            } else {
                $this->error("Failed to write to file.");
            }

        } catch (\Exception $e) {
            $this->error("Solr Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
