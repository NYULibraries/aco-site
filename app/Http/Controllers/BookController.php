<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Services\FeaturedBooksService;

class BookController extends Controller
{
  public function index(Request $request, FeaturedBooksService $featuredBooksService): View
  {

    $identifier = $request->route('identifier');

    $page = $request->route('page');

    // Gets the book identifier metadata by taking the identifier, params, cache bool
    $result = $featuredBooksService->byIdentifiers([$identifier], 1, true)->toArray();
        $request->mergeIfMissing(['lang' => 'en']);

    $validated = $request->validate([
        'lang' => 'required|in:en,ar',
    ]);

    $lang = $validated['lang'];

    $sequenceCount = $result['documents'][0][$lang]['sequence_count'][0];

    if (empty($result) || empty($result['documents'])) {
      abort(404, 'Book not found');
    }

    if ($page > $sequenceCount || $page < 1 || empty($sequenceCount) || !is_numeric($page)) {
      abort(404, 'Page not found');
    }

    $pageTitle = $result['documents'][0][$lang]['title'] ?? 'Book Viewer';

    $data = [
      'pagetitle' => $pageTitle,
      'body_class' => 'book io-loading',
      'identifier' => $identifier,
      'page' => $page,
      'lang' => $lang,
    ];

    return view('pages.book', $data);
  }
}
