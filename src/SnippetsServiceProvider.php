<?php

namespace ArtFlowStudio\Snippets;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class SnippetsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

        
        
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load views from package

        // Include the helper file(s)
        foreach (glob(__DIR__ . '/helpers/*.php') as $filename) {
            require_once $filename;
        }

        
        // Publish assets and views
        // $this->publishes([
        //     __DIR__ . '/../public' => public_path('vendor/snippets'),
        //     __DIR__ . '/views' => resource_path('views/vendor/snippets'),
        // ], 'af_snippets');
    }
}
