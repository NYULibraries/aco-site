<?php

namespace App\Http\Controllers;

use App\Services\FeaturedBooksService;
use App\Services\ItemCountService;

class HomeController extends Controller
{
    public function index(ItemCountService $itemCounts, FeaturedBooksService $featuredBooksService)
    {

        $itemCounts = $itemCounts->getItemCounts();

        $featured = $featuredBooksService->featured(
            config('featured.books'),
            rows: 9,
        );

        $documents = $featured->toArray();

        $data = [
            'body_class' => 'front',
            'appName' => 'Arabic Collections Online',
            'frontCount' => number_format($itemCounts['volumes']),
            'subjectCount' => number_format($itemCounts['subjects']),
            'documents' => $documents['documents'] ?? [],
        ];

        return view('pages.home', $data);

    }
}
