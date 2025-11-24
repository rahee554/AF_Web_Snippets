<?php

namespace ArtFlowStudio\Snippets\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishPrintAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'af-print:publish {--force : Force replace existing files}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Publish AF Snippets print layout assets (CSS, JS) to public directory';

    /**
     * Create a new command instance.
     */
    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $packagePath = base_path('vendor/artflow-studio/snippets/src/public/print');
        $publicPath = public_path('vendor/artflow-studio/snippets/print');
        $force = $this->option('force');

        // Ensure directories exist
        $this->files->ensureDirectoryExists($publicPath);

        // Copy CSS
        $cssSource = $packagePath . '/af-print.css';
        $cssDest = $publicPath . '/af-print.css';
        if ($this->files->exists($cssSource)) {
            if ($this->files->exists($cssDest) && !$force) {
                $this->line('⇨ Skipping af-print.css (already exists, use --force to replace)');
            } else {
                $this->files->copy($cssSource, $cssDest);
                $this->info('✓ Published af-print.css');
            }
        } else {
            $this->error('✗ af-print.css source not found at: ' . $cssSource);
        }

        // Copy JS
        $jsSource = $packagePath . '/af-print.js';
        $jsDest = $publicPath . '/af-print.js';
        if ($this->files->exists($jsSource)) {
            if ($this->files->exists($jsDest) && !$force) {
                $this->line('⇨ Skipping af-print.js (already exists, use --force to replace)');
            } else {
                $this->files->copy($jsSource, $jsDest);
                $this->info('✓ Published af-print.js');
            }
        } else {
            $this->error('✗ af-print.js source not found at: ' . $jsSource);
        }

        $this->newLine();
        $this->info('✓ AF Snippets Print Assets published successfully!');
        $this->line('Assets location: <fg=blue>public/vendor/artflow-studio/snippets/print/</>');

        return self::SUCCESS;
    }
}
