<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\BrowseByCategoryController;
use App\Http\Controllers\BrowseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResourcesController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::get('/about', [AboutController::class, 'index']);

Route::get('/browse-by-category', [BrowseByCategoryController::class, 'index']);

Route::get('/browse', [BrowseController::class, 'index']);

Route::get('/resources', [ResourcesController::class, 'index']);

Route::get('/search', [SearchController::class, 'search']);

Route::get('/searchcollections', [SearchController::class, 'searchcollection']);
