<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ItemCountService;

class HomeController extends Controller
{
    public function index()
    {
      $itemCounts = ItemCountService::getItemCounts();

      $data = [
        'body_class' => 'front',
        'appName' => 'Arabic Collections Online',
        'title' => 'Welcome to My Laravel Site',
        'message' => 'This text comes from the controller.',
        'frontCount' => number_format($itemCounts['volumes']),
        'subjectCount' => number_format($itemCounts['subjects']),
      ];

      return view('pages.home', $data);

    }
}
