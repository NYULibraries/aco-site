<?php

namespace App\Http\Controllers;
use Solarium\Client;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use App\Providers\SolrServiceProvider;
use App\Services\SolrService;

class SearchController extends Controller
{

  public function searchcollection(Request $request, Client $solrClient): View
  {

        $data = [
          'pagetitle' => 'Search Collections',
          'body_class' => 'search',
          'title' => [
            'en' => [
              'label' => 'Search Collections',
              'language' => [
                'code' => 'en',
                'dir' => 'ltr',
                ],
              ],
            'ar' => [
              'label' => 'إبحث في المجموعات',
              'language' => [
                'code' => 'ar',
                'dir' => 'rtl',
                ],
              ],
            ],
            'content' => [
              'body' => [
                'en' => [
                  'language' => [
                  'class' => 'col col-l',
                  'lang' => 'en',
                  'dir' => 'ltr',
                ],
                'label' => 'Search tips in Arabic transliteration',
                'content' => '',
          ],
          'ar' => [
            'language' => [
              'class' => 'col col-r',
              'lang' => 'ar',
              'dir' => 'rtl',
            ],
            'label' => 'إرشادات للبحث لدى استخدام الترجمة الصوتية بالحروف اللاتينية',
            'content' => '',
          ]
        ],
      ],
    ];

    return view('pages.searchcollections', $data);

  }

  public function search(Request $request, SolrService $solrService): View
  {

    $scope = $request->query('scope', 'containsAny');

    $results = $solrService->search($request, $scope);

    $documents = $results['documents'];

    $total = $results['total'];

    $rows = $results['rows'];

    $page = $results['page'];

    // Calculate Solr start offset
    $start = ($page - 1) * $rows;

    $q = $request->query('q', '');

    $paginator = new LengthAwarePaginator(
        $documents,
        $total,
        $rows,
        $page,
        [
            'path' => $request->url(),
            'query' => $request->query(),
        ]
    );

    $data = [
      'pagetitle' => 'Search Results',
      'body_class' => 'search',
      'title' => [
        'en' => [
          'label' => 'Search Results',
          'language' => [
            'code' => 'en',
            'dir' => 'ltr'
          ]
        ],
        'ar' => [
          'label' => 'نتائج البحث',
          'language' => [
            'code' => 'ar',
            'dir' => 'rtl'
          ]
        ],
      ],
      'content' => [
        'body' => [
          'en' => [
            'language' => [
              'class' => 'col col-l',
              'lang' => 'en',
              'dir' => 'ltr',
            ],
            'label' => 'Search tips in Arabic transliteration',
            'content' => '',
          ],
          'ar' => [
            'language' => [
              'class' => 'col col-r',
              'lang' => 'ar',
              'dir' => 'rtl'
            ],
            'label' => 'إرشادات للبحث لدى استخدام الترجمة الصوتية بالحروف اللاتينية',
            'content' => '',
          ],
        ],
      ],
      'limit' => $rows,
      'docslength' => count($documents),
      'numfound' => $total,
      'total' => $total,
      'query' => '',
      'documents' => $documents,
      'currentPage' => $page,
      'totalPages' => ceil($total / $rows),
      'paginator' => $paginator,
      'startIndex' => $total > 0 ? $start + 1 : 0,
      'endIndex' => min($start + count($documents), $total),
    ];

    return view('pages.search', $data);

  }

}

