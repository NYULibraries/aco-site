<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void
    {
        // 1. Get the menu data from the new config file
        $navItems = config('menu.primary');

        // 2. Compose the 'partials.nav' view (or wherever your nav lives)
        //    This makes the $navItems variable available in that view automatically.
        View::composer('partials.nav', function ($view) use ($navItems) {
            $view->with('navItems', $navItems);
        });

        // Get query parameters
        $q = $request->query('q', '');

        $scope = $request->query('scope', 'containsAny');

        $conf = [
          'q' => $q,
          'scope' => $scope,
          'scopes' => [
            [
              'label' => 'Contains Any / يحتوي على اي',
              'value' => 'containsAny',
              'selected' => ($scope === 'containsAny' ? true : false),
            ],
            [
              'label' => 'Contains All / يحتوي على كل',
              'value' => 'containsAll',
              'selected' => ($scope === 'containsAll' ? true : false),
            ],
            [
              'label' => 'Matches / يساوي',
              'value' => 'matches',
              'selected' => ($scope === 'matches' ? true : false),
            ],
          ]
        ];

        View::composer('partials.search_form_adv', function ($view) use ($conf) {
          $view->with('form', $conf);
        });

    }
}
