<?php

namespace ArtFlowStudio\Snippets;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

class SnippetsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // (optional) add bindings, etc.
    }

    public function boot(): void
    {
        // Load views from package
        $this->loadViewsFrom(__DIR__ . '/views', 'snippets');

        // Register Livewire component
        Livewire::component('afdropdown', \ArtFlowStudio\Snippets\Http\Livewire\AFdropdown::class);

        // Register Blade directive for @AFdropdown
        Blade::directive('AFdropdown', function ($expression) {
            return "<?php echo \\Livewire\\Livewire::mount('afdropdown', $expression)->html(); ?>";
        });

        // Include the helper file(s)
        foreach (glob(__DIR__ . '/helpers/*.php') as $filename) {
            require_once $filename;
        }
    }
}