<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;

class BrowseController extends Controller
{

  public function index(Request $request): View
  {

    $pagetitle = 'Browse titles';

    $body_class = 'browse';

    return view('pages.browse', [
      'pagetitle' => $pagetitle,
      'title' => [
        'en' => [
          'label' => $pagetitle,
          'language' => [
            'code' => 'en',
            'dir' => 'ltr',
          ],
        ],
        'ar' => [
          'label' => 'تصفح العناوين',
          'language' => [
          'code' => 'ar',
          'dir' => 'rtl',
        ],
      ],
    ],
      'body_class' => $body_class,
    ]);

  }

}
