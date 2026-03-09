<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\DiscoveryCollection;
use App\Services\SolrService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class BrowseController extends Controller
{
    public function index(SearchRequest $request, SolrService $solrService): View
    {
        $page = $request->getPage();
        $scope = $request->getScope();
        $rows = $request->getRows();
        $start = ($page - 1) * $rows;
        $sortDir = $request->getSortDir();
        $sortField = $request->getSortField();

        $results = $solrService->search(
            fieldSelect: 'all',
            searchString: '*:*',
            scope: 'matches',
            sortField: $sortField,
            sortDir: $sortDir,
            start: $start,
            rows: $rows,
        );

        return view('pages.browse', $this->buildViewData($results, $request, $page, $rows, $start));
    }

    private function getBilingualTitle(): array
    {
        return [
            'en' => [
                'label' => 'Browse titles',
                'language' => ['code' => 'en', 'dir' => 'ltr'],
            ],
            'ar' => [
                'label' => 'تصفح العناوين',
                'language' => ['code' => 'ar', 'dir' => 'rtl'],
            ],
        ];
    }

    private function buildViewData(DiscoveryCollection $results, Request $request, int $page, int $rows, int $start): array
    {
        $data = $results->toArray();
        $documents = $data['documents'];
        $total = $data['total'];

        $paginator = new LengthAwarePaginator($documents, $total, $rows, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return [
            'pagetitle' => 'Browse titles',
            'body_class' => 'browse',
            'title' => $this->getBilingualTitle(),
            'documents' => $documents,
            'paginator' => $paginator,
            'total' => $total,
            'limit' => $rows,
            'currentPage' => $page,
            'totalPages' => $total > 0 ? (int) ceil($total / $rows) : 0,
            'startIndex' => $total > 0 ? $start + 1 : 0,
            'endIndex' => min($start + count($documents), $total),
            'currentRows' => $request->getRows(),
            'currentSort' => $request->getSortField(),
        ];
    }
}
