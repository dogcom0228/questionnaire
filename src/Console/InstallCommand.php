<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questionnaire:install
                            {--force : Overwrite existing files}
                            {--config : Publish configuration only}
                            {--migrations : Publish migrations only}
                            {--views : Publish views only}
                            {--frontend : Publish frontend assets only}
                            {--all : Publish all publishable assets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Questionnaire package';

    /**
     * Whether to publish all assets.
     */
    protected bool $publishAll = false;

    public function __construct(
        protected Filesystem $files
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Questionnaire Package...');
        $this->newLine();

        $force = $this->option('force');
        $this->publishAll = $this->option('all') || (!$this->option('config') && !$this->option('migrations') && !$this->option('views') && !$this->option('frontend'));

        // 1. Publish Configuration
        if ($this->shouldPublish('config') && ($force || $this->confirm('Do you want to publish the configuration file?', true))) {
            $this->publishConfig($force);
        }

        // 2. Publish Migrations
        if ($this->shouldPublish('migrations') && ($force || $this->confirm('Do you want to publish the migrations?', true))) {
            $this->publishMigrations($force);
        }

        // 3. Run Migrations
        if ($this->shouldPublish('migrations') && $this->confirm('Do you want to run the migrations now?', true)) {
            $this->call('migrate');
        }

        // 4. Publish Views
        if ($this->shouldPublish('views') && ($force || $this->confirm('Do you want to publish the views?', true))) {
            $this->publishViews($force);
        }

        // 5. Publish Frontend Assets
        if ($this->shouldPublish('frontend') && ($force || $this->confirm('Do you want to publish the frontend assets?', true))) {
            $this->publishFrontend($force);
        }

        $this->newLine();
        $this->info('Questionnaire Package installed successfully!');
        $this->displayNextSteps();

        return Command::SUCCESS;
    }

    /**
     * Check if a specific asset type should be published.
     */
    protected function shouldPublish(string $type): bool
    {
        return $this->publishAll || $this->option($type);
    }

    /**
     * Publish configuration file.
     */
    protected function publishConfig(bool $force): void
    {
        $this->info('Publishing configuration...');

        $this->call('vendor:publish', [
            '--tag' => 'questionnaire-config',
            '--force' => $force,
        ]);
    }

    /**
     * Publish migrations.
     */
    protected function publishMigrations(bool $force): void
    {
        $this->info('Publishing migrations...');

        $this->call('vendor:publish', [
            '--tag' => 'questionnaire-migrations',
            '--force' => $force,
        ]);
    }

    /**
     * Publish views.
     */
    protected function publishViews(bool $force): void
    {
        $this->info('Publishing views...');

        $this->call('vendor:publish', [
            '--tag' => 'questionnaire-views',
            '--force' => $force,
        ]);
    }

    /**
     * Publish frontend assets.
     */
    protected function publishFrontend(bool $force): void
    {
        $this->info('Publishing frontend assets...');

        // Publish Vue components to resources/js/vendor/questionnaire
        $sourcePath = dirname(__DIR__, 2) . '/resources/js/questionnaire';
        $targetPath = resource_path('js/vendor/questionnaire');

        if ($this->files->isDirectory($sourcePath)) {
            if (!$force && $this->files->isDirectory($targetPath)) {
                if (!$this->confirm("Frontend assets already exist at {$targetPath}. Overwrite?")) {
                    $this->warn('Frontend assets not published.');
                    return;
                }
            }

            $this->files->ensureDirectoryExists($targetPath);
            $this->files->copyDirectory($sourcePath, $targetPath);

            $this->info("Frontend assets published to: {$targetPath}");
        }

        // Also publish the Vuetify config stub
        $this->publishVuetifyConfig($force);

        // Publish built assets (for immediate use without building)
        $this->call('vendor:publish', [
            '--tag' => 'questionnaire-assets',
            '--force' => $force,
        ]);
    }

    /**
     * Publish Vuetify configuration stub.
     */
    protected function publishVuetifyConfig(bool $force): void
    {
        $sourceFile = dirname(__DIR__, 2) . '/stubs/vuetify.config.js';
        $targetFile = resource_path('js/vendor/questionnaire/vuetify.config.js');

        if ($this->files->exists($sourceFile)) {
            if (!$force && $this->files->exists($targetFile)) {
                return;
            }

            $this->files->ensureDirectoryExists(dirname($targetFile));
            $this->files->copy($sourceFile, $targetFile);
        }
    }

    /**
     * Display next steps after installation.
     */
    protected function displayNextSteps(): void
    {
        $this->components->info('Questionnaire package installed successfully!');
        $this->newLine();

        $this->components->twoColumnDetail('<fg=green>Next steps</>');
        $this->newLine();

        $this->line('  1. Run migrations:');
        $this->line('     <fg=yellow>php artisan migrate</>');
        $this->newLine();

        $this->line('  2. Configure your vite.config.js to include the questionnaire components:');
        $this->newLine();
        $this->line('     <fg=cyan>// vite.config.js</>');
        $this->line('     resolve: {');
        $this->line("       alias: {");
        $this->line("         '@questionnaire': path.resolve(__dirname, 'resources/js/vendor/questionnaire'),");
        $this->line("       },");
        $this->line('     },');
        $this->newLine();

        $this->line('  3. Configure Inertia page resolution in your app.js:');
        $this->newLine();
        $this->line('     <fg=cyan>// resources/js/app.js</>');
        $this->line('     resolve: name => {');
        $this->line("       // Check questionnaire pages first");
        $this->line("       if (name.startsWith('Questionnaire/')) {");
        $this->line("         const questionnairePage = name.replace('Questionnaire/', '');");
        $this->line("         return import(`@questionnaire/Pages/\${questionnairePage}.vue`);");
        $this->line("       }");
        $this->line("       // Fall back to your app pages");
        $this->line("       return import(`./Pages/\${name}.vue`);");
        $this->line('     },');
        $this->newLine();

        $this->line('  4. (Optional) Customize the configuration in config/questionnaire.php');
        $this->newLine();

        $this->components->bulletList([
            'Documentation: https://github.com/liangjin0228/questionnaire',
            'Admin URL: ' . url(config('questionnaire.routes.prefix', 'questionnaire') . '/admin'),
        ]);
    }
}
