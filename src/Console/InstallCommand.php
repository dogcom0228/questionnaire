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
                            {--assets : Publish pre-built assets}
                            {--all : Publish all publishable assets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Questionnaire package (Zero Config)';

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
        $this->publishAll = $this->option('all') || (! $this->option('config') && ! $this->option('migrations') && ! $this->option('views') && ! $this->option('assets'));

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

        // 5. Publish Assets
        if ($this->shouldPublish('assets') && ($force || $this->confirm('Do you want to publish the pre-built assets?', true))) {
            $this->publishAssets($force);
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
     * Publish assets.
     */
    protected function publishAssets(bool $force): void
    {
        $this->info('Publishing assets...');

        $this->call('vendor:publish', [
            '--tag' => 'questionnaire-assets',
            '--force' => $force,
        ]);
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

        $this->line('  2. (Optional) Customize the configuration in config/questionnaire.php');
        $this->newLine();

        $this->line('  That\'s it! No frontend configuration required.');
        $this->newLine();

        $this->components->bulletList([
            'Documentation: https://github.com/liangjin0228/questionnaire',
            'Admin URL: '.url(config('questionnaire.routes.prefix', 'questionnaire').'/admin'),
        ]);
    }
}
