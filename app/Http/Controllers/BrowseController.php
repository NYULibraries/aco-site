<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class BrowseController extends Controller
{
    public function index(Request $request): View
    {

        $pagetitle = 'Browse';

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
