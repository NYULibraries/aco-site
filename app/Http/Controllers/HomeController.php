<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {

      $data = [
        'appName' => 'Arabic Collections Online',
        'title' => 'Welcome to My Laravel Site',
        'message' => 'This text comes from the controller.',
        'frontCount' => '17,699',
        'subjectCount' => '10,473',
      ];

      return view('pages.home', $data);

    }
}
