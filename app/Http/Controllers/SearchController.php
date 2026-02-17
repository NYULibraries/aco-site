<?php

namespace App\Http\Controllers;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use App\Services\SolrService;
use App\Http\Resources\DiscoveryCollection;
use App\Http\Requests\SearchRequest;

class SearchController extends Controller
{

  public function searchcollection(): View
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

   public function search(SearchRequest $request, SolrService $solrService): View
   {

      $searchData = $request->getSearchData();

      $page = $request->getPage();

      $scope = $request->getScope();

      $rows = $request->getRows();

      $start = ($page - 1) * $rows;

      $sortDir = $request->getSortDir();

      if (empty($searchData)) {
          return view('pages.search', $this->buildView());
      }

      $sortField = $request->getSortField();

      $results = $solrService->search(
          fieldSelect: $searchData['field'],
          searchString: $searchData['value'],
          scope: $scope,
          sortField: $sortField,
          sortDir: $sortDir,
          start: $start,
          rows: $rows,
      );

     return view('pages.search', $this->buildViewData($results, $request, $searchData));

   }

   private function getBilingualTitle(): array
   {
       return [
          'en' => [
              'label' => 'Search Results',
              'language' => ['code' => 'en', 'dir' => 'ltr']
          ],
          'ar' => [
              'label' => 'نتائج البحث',
              'language' => ['code' => 'ar', 'dir' => 'rtl']
          ],
      ];
  }

  private function getBilingualContent(): array
  {
      return [
          'body' => [
              'en' => [
                  'language' => ['class' => 'col col-l', 'lang' => 'en', 'dir' => 'ltr'],
                  'label' => 'Search tips in Arabic transliteration',
                  'content' => '',
              ],
              'ar' => [
                  'language' => ['class' => 'col col-r', 'lang' => 'ar', 'dir' => 'rtl'],
                  'label' => 'إرشادات للبحث لدى استخدام الترجمة الصوتية بالحروف اللاتينية',
                  'content' => '',
              ],
          ],
      ];
  }

  private function buildView(): array
  {

      return [
          'pagetitle' => "Search",
          'body_class' => 'search',
          'title' => $this->getBilingualTitle(),
          'content' => $this->getBilingualContent(),
      ];
  }

  private function buildViewData(DiscoveryCollection $results, Request $request, $searchData): array
  {
      $data = $results->toArray();

      $documents = $data['documents'];

      $total = $data['total'];

      $rows = $data['rows'];

      $page = $data['page'];

      $start = $data['start'];

      $paginator = new LengthAwarePaginator($documents, $total, $rows, $page,
          [
              'path' => $request->url(),
              'query' => $request->query(),
          ]
      );

      return [
          'pagetitle' => "Search Results for {$searchData['value']}",
          'body_class' => 'search',
          'title' => $this->getBilingualTitle(),
          'content' => $this->getBilingualContent(),
          'query' => $searchData['value'],
          'documents' => $documents,
          'paginator' => $paginator,
          'total' => $total,
          'limit' => $rows,
          'currentPage' => $page,
          'totalPages' => $total > 0 ? (int) ceil($total / $rows) : 0,
          'startIndex' => $total > 0 ? $start + 1 : 0,
          'endIndex' => min($start + count($documents), $total),
      ];
  }

}

