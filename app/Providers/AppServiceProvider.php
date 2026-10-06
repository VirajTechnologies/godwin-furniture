<?php

namespace App\Providers;

use App\Models\Category;
use App\Support\StoreCart;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StoreCart::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('store.*', function ($view): void {
            $view->with('cartCount', app(StoreCart::class)->count());
            $view->with('storeMenu', $this->storeMenu());
        });
    }

    private function storeMenu()
    {
        return Category::query()
            ->with(['children' => fn ($query) => $query
                ->where('status', Category::STATUS_ACTIVE)
                ->orderBy('menu_group')
                ->orderBy('sort_order')
                ->orderBy('name')])
            ->whereNull('parent_id')
            ->where('status', Category::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
