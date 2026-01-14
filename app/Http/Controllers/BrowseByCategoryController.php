<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Solarium\Client;

// use App\Services\ItemCountService;

class BrowseByCategoryController extends Controller
{
    public function index(Client $solrClient): View
    {

        // $itemCounts = ItemCountService::getItemCounts();
        // $totalVolumes = number_format($itemCounts['volumes']);
        $totalVolumes = number_format(17699);

        $fields = ['en' => ['id' => 'sm_topic', 'links' => []], 'ar' => ['id' => 'sm_ar_topic', 'links' => []]];

        foreach ($fields as $lang => $field) {

            // 1. Create the select query object
            $query = $solrClient->createSelect();

            // 2. Set the main query (q=sm_collection_code:aco)
            // This targets all documents belonging to the 'aco' collection.
            $query->setQuery('sm_collection_code:aco');

            // 3. Set the rows to 0 (rows=0)
            // We only want facet counts, not search results.
            $query->setRows(0);

            // 4. Add a filter query for the topics (fq=sm_topic:*)
            // This ensures the faceting only runs over documents that actually have a topic field.
            $query->createFilterQuery('topic_filter')->setQuery("{$field['id']}:*");

            // 5. Enable faceting and define the facet field (facet=true & facet.field=sm_topic)
            $facetSet = $query->getFacetSet();

            $facetSet->createFacetField('topics')->setField($field['id']);

            try {

                $result = $solrClient->select($query);

                $facetResult = $result->getFacetSet()->getFacet('topics');

                foreach ($facetResult as $value => $count) {
                    $suffix = $lang === 'en' ? 'books' :  'كتب';
                    $fields[$lang]['links'][] = ['label' => $value, 'count' => number_format($count) . " {$suffix}", 'href' => url("/search?category={$value}&scope=matches")];
                }

            } catch (\Exception $e) {
                // Log the error and handle gracefully
                // error_log("Solr error: " . $e->getMessage());
            }

        }

        $data = [
            'pagetitle' => 'Browse by Category',
            'body_class' => 'browse-by-category',
            'title' => [
                'en' => [
                    'label' => 'Browse by Category',
                    'language' => [
                        'code' => 'en',
                        'dir' => 'ltr',
                    ],
                ],
                'ar' => [
                    'label' => 'تصفح حسب فئة الموضوع',
                    'language' => [
                        'code' => 'ar',
                        'dir' => 'rtl',
                    ],
                ],
            ],
            'content' => [
                'resources' => [
                    'en' => [
                        'language' => [
                            'class' => 'col col-l',
                            'lang' => 'en',
                            'dir' => 'ltr',
                        ],
                        'label' => 'ACO categories follow the Library of Congress Classification system',
                        'links' => array_merge([['label' => 'All', 'count' => $totalVolumes . ' ', 'href' => url('/browse')]], $fields['en']['links']),
                    ],
                    'ar' => [
                        'language' => [
                            'class' => 'col col-r',
                            'lang' => 'ar',
                            'dir' => 'rtl',
                        ],
                        'label' => 'المجموعات العربية على الانترت تتبع نظام تصنيف مكتبة الكونغرس',
                        'links' => array_merge([['label' => 'الجميع', 'count' => $totalVolumes . ' ', 'href' => url('/browse')]], $fields['ar']['links']),
                    ],
                ],
            ],
        ];

        return view('pages.browsebycategory', $data);

    }
}
