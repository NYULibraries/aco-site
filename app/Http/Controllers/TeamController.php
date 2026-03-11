<?php

namespace App\Http\Controllers;

class TeamController extends Controller
{
    public function index()
    {
        $data = [
            'pagetitle' => 'About the project team',
            'body_class' => 'page team about',
            'title' => [
                'en' => [
                    'label' => 'About',
                    'language' => [
                        'code' => 'en',
                        'dir' => 'ltr',
                    ],
                ],
                'ar' => [
                    'label' => 'عن هذا المشروع',
                    'language' => [
                        'code' => 'ar',
                        'dir' => 'rtl',
                    ],
                ],
            ],
        ];

        return view('pages.team', $data);
    }
}
