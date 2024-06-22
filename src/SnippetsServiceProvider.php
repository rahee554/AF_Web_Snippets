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
        // Register Blade directive for AJAX form submit
        Blade::directive('AF_AjaxForm', function ($expression) {
            return "<?php echo \$__env->make('AF_AjaxForm::ajax_form', $expression, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
        });
        
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load views from package
        $this->loadViewsFrom(__DIR__ . '/views', 'AF_AjaxForm');

        // Publish assets and views
        // $this->publishes([
        //     __DIR__ . '/../public' => public_path('vendor/snippets'),
        //     __DIR__ . '/views' => resource_path('views/vendor/snippets'),
        // ], 'af_snippets');
    }
}
