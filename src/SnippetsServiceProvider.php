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

        // Register Livewire components
        Livewire::component('afdropdown', \ArtFlowStudio\Snippets\Http\Livewire\AFdropdown::class);
        Livewire::component('af-distinct-select', \ArtFlowStudio\Snippets\Http\Livewire\AFDistinctSelect::class);

        // Register Blade directives
        Blade::directive('AFdropdown', function ($expression) {
            return "<?php echo \\Livewire\\Livewire::mount('afdropdown', $expression)->html(); ?>";
        });

        Blade::directive('AFDistinctSelect', function ($expression) {
            $mount = "\\Livewire\\Livewire::mount('af-distinct-select', $expression)";
            return "<?php \n"
                . "  \$__afdistinct = {$mount};\n"
                . "  echo is_object(\$__afdistinct) && method_exists(\$__afdistinct, 'html') ? \$__afdistinct->html() : (string)\$__afdistinct;\n"
                . "?>";
        });

        // Include the helper file(s)
        foreach (glob(__DIR__ . '/helpers/*.php') as $filename) {
            require_once $filename;
        }
    }
}