<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BrowseByCategoryController;
use App\Http\Controllers\BrowseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResourcesController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home.index');

Route::get('/about', [AboutController::class, 'index'])->name('about.index');

Route::get('/browse-by-category', [BrowseByCategoryController::class, 'index'])->name('browsebycategory.index');

Route::get('/browse', [BrowseController::class, 'index'])->name('browse.index');

Route::get('/resources', [ResourcesController::class, 'index'])->name('resources.index');

Route::get('/search', [SearchController::class, 'search'])->name('search.index');

Route::get('/searchcollections', [SearchController::class, 'searchcollection'])->name('searchcollections.index');

Route::get('/team', [TeamController::class, 'index'])->name('team.index');

Route::redirect('/book/{identifier}', '/book/{identifier}/1', 301);

Route::get('/book/{identifier}/{page}', [BookController::class, 'index'])->name('book.index');
