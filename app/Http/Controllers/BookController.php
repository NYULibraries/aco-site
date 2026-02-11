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

    if (empty($result) || empty($result['documents'])) {
      abort(404, 'Book not found');
    }

    $enTitle = $result['documents'][0]['en']['title'] ?? 'Book Viewer';
    $arTitle = $result['documents'][0]['ar']['title'] ?? 'Book Viewer';

    $data = [
      'pagetitle' => $enTitle,
      'pagetitle_ar' => $arTitle,
      'body_class' => 'book io-loading',
      'identifier' => $identifier,
      'page' => $page,
    ];

    return view('pages.book', $data);
  }
}
