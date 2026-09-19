<?php

namespace App\Providers;

use App\Support\SiteSettings;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer(['layouts.public', 'public.*', 'partials.public.*'], function ($view): void {
            $settings = request()->attributes->get('site_settings');
            if (! $settings) {
                $settings = new SiteSettings;
                request()->attributes->set('site_settings', $settings);
            }
            $view->with('siteSettings', $settings);
        });
    }
}
