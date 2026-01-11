<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Liangjin0228\Questionnaire\Console\InstallCommand;
use Liangjin0228\Questionnaire\Console\ListQuestionTypesCommand;
use Liangjin0228\Questionnaire\Contracts\Actions\CloseQuestionnaireActionInterface;
use Liangjin0228\Questionnaire\Contracts\Actions\CreateQuestionnaireActionInterface;
use Liangjin0228\Questionnaire\Contracts\Actions\PublishQuestionnaireActionInterface;
use Liangjin0228\Questionnaire\Contracts\Actions\SubmitResponseActionInterface;
use Liangjin0228\Questionnaire\Contracts\Actions\UpdateQuestionnaireActionInterface;
use Liangjin0228\Questionnaire\Contracts\DuplicateSubmissionGuardInterface;
use Liangjin0228\Questionnaire\Contracts\ExporterInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\Contracts\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Contracts\ValidationStrategyInterface;
use Liangjin0228\Questionnaire\Export\CsvExporter;
use Liangjin0228\Questionnaire\Guards\DuplicateSubmissionGuardFactory;
use Liangjin0228\Questionnaire\Managers\QuestionTypeManager;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Models\Response;
use Liangjin0228\Questionnaire\Repositories\EloquentQuestionnaireRepository;
use Liangjin0228\Questionnaire\Repositories\EloquentResponseRepository;
use Liangjin0228\Questionnaire\Services\DefaultValidationStrategy;

class QuestionnaireServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<string, string>
     */
    public array $bindings = [];

    /**
     * All of the container singletons that should be registered.
     *
     * @var array<string, string>
     */
    public array $singletons = [];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/questionnaire.php',
            'questionnaire'
        );

        $this->registerBindings();
        $this->registerQuestionTypes();
        $this->registerGuards();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerRoutes();
        $this->registerViews();
        $this->registerBladeDirectives();
        $this->registerMigrations();
        $this->registerPolicies();
        $this->registerCommands();
        $this->registerEventListeners();
    }

    /**
     * Register the package bindings.
     */
    protected function registerBindings(): void
    {
        // Repository bindings
        $this->app->bind(
            QuestionnaireRepositoryInterface::class,
            fn () => $this->app->make(
                config('questionnaire.bindings.questionnaire_repository', EloquentQuestionnaireRepository::class)
            )
        );

        $this->app->bind(
            ResponseRepositoryInterface::class,
            fn () => $this->app->make(
                config('questionnaire.bindings.response_repository', EloquentResponseRepository::class)
            )
        );

        // Validation strategy
        $this->app->bind(
            ValidationStrategyInterface::class,
            fn () => $this->app->make(
                config('questionnaire.bindings.validation_strategy', DefaultValidationStrategy::class)
            )
        );

        // Action bindings
        $this->registerActionBindings();

        // Question type registry (singleton)
        $this->app->singleton(
            QuestionTypeRegistryInterface::class,
            fn () => $this->app->make(
                config('questionnaire.bindings.question_type_registry', QuestionTypeManager::class)
            )
        );

        // Guard factory (singleton)
        $this->app->singleton(DuplicateSubmissionGuardFactory::class);

        // Dynamic guard binding (resolves based on questionnaire settings)
        $this->app->bind(DuplicateSubmissionGuardInterface::class, function ($app, $params) {
            $factory = $app->make(DuplicateSubmissionGuardFactory::class);

            // If a questionnaire is provided in params, resolve the appropriate guard
            if (isset($params['questionnaire']) && $params['questionnaire'] instanceof Questionnaire) {
                return $factory->resolve($params['questionnaire']);
            }

            // Default to allow multiple
            return $app->make(\Liangjin0228\Questionnaire\Guards\AllowMultipleGuard::class);
        });

        // Exporter
        if (config('questionnaire.features.export_csv', true)) {
            $this->app->bind(ExporterInterface::class, CsvExporter::class);
        }

        // Asset Manager
        $this->app->singleton(AssetManager::class);
    }

    /**
     * Register question types from config.
     */
    protected function registerQuestionTypes(): void
    {
        $this->app->afterResolving(QuestionTypeRegistryInterface::class, function ($registry) {
            $types = config('questionnaire.question_types', []);

            foreach ($types as $typeClass) {
                $registry->register($typeClass);
            }
        });
    }

    /**
     * Register duplicate submission guards from config.
     */
    protected function registerGuards(): void
    {
        $this->app->afterResolving(DuplicateSubmissionGuardFactory::class, function ($factory) {
            $guards = config('questionnaire.duplicate_guards', []);

            foreach ($guards as $identifier => $guardClass) {
                $factory->register($identifier, $guardClass);
            }
        });
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        if (! config('questionnaire.routes.enabled', true)) {
            return;
        }

        $this->registerWebRoutes();
        $this->registerApiRoutes();
    }

    /**
     * Register web routes.
     */
    protected function registerWebRoutes(): void
    {
        if (! config('questionnaire.features.frontend', true)) {
            return;
        }

        $routeConfig = [
            'prefix' => config('questionnaire.routes.prefix', 'questionnaire'),
            'as' => config('questionnaire.routes.name_prefix', 'questionnaire.'),
        ];

        if ($domain = config('questionnaire.routes.domain')) {
            $routeConfig['domain'] = $domain;
        }

        Route::group($routeConfig, function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Register API routes.
     */
    protected function registerApiRoutes(): void
    {
        if (! config('questionnaire.features.api', true)) {
            return;
        }

        $routeConfig = [
            'prefix' => config('questionnaire.routes.api_prefix', 'api/questionnaire'),
            'as' => config('questionnaire.routes.name_prefix', 'questionnaire.'),
            'middleware' => config('questionnaire.routes.api_middleware', ['api']),
        ];

        if ($domain = config('questionnaire.routes.domain')) {
            $routeConfig['domain'] = $domain;
        }

        Route::group($routeConfig, function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }

    /**
     * Register the package views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'questionnaire');
    }

    /**
     * Register Blade directives for the package.
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('questionnaireScripts', function () {
            return '<?php echo app(\\Liangjin0228\\Questionnaire\\AssetManager::class)->scripts(); ?>';
        });
    }

    /**
     * Register the package migrations.
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register the package policies.
     */
    protected function registerPolicies(): void
    {
        if (! config('questionnaire.features.authorization', true)) {
            return;
        }

        $policies = config('questionnaire.policies', []);

        if (isset($policies['questionnaire'])) {
            Gate::policy(
                config('questionnaire.models.questionnaire', Questionnaire::class),
                $policies['questionnaire']
            );
        }

        if (isset($policies['response'])) {
            Gate::policy(
                config('questionnaire.models.response', Response::class),
                $policies['response']
            );
        }
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Config
        $this->publishes([
            __DIR__.'/../config/questionnaire.php' => config_path('questionnaire.php'),
        ], 'questionnaire-config');

        // Migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'questionnaire-migrations');

        // Views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/questionnaire'),
        ], 'questionnaire-views');

        // Frontend assets (built)
        $this->publishes([
            __DIR__.'/../public/build' => public_path('vendor/questionnaire'),
        ], 'questionnaire-assets');

        // Frontend source (Vue components)
        $this->publishes([
            __DIR__.'/../resources/js/questionnaire' => resource_path('js/vendor/questionnaire'),
        ], 'questionnaire-frontend');

        // Stubs
        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs/questionnaire'),
        ], 'questionnaire-stubs');
    }

    /**
     * Register the package's commands.
     */
    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            ListQuestionTypesCommand::class,
        ]);
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners(): void
    {
        $events = $this->app['events'];

        // Register default listeners
        $events->listen(
            \Liangjin0228\Questionnaire\Events\ResponseSubmitted::class,
            \Liangjin0228\Questionnaire\Listeners\LogResponseSubmission::class
        );

        $events->listen(
            \Liangjin0228\Questionnaire\Events\ResponseSubmitted::class,
            \Liangjin0228\Questionnaire\Listeners\SendResponseNotification::class
        );
    }

    /**
     * Register action bindings.
     *
     * Actions are bound to their interfaces, allowing for easy replacement
     * through configuration while maintaining type safety.
     */
    protected function registerActionBindings(): void
    {
        $actionBindings = [
            'create_questionnaire' => [
                'interface' => CreateQuestionnaireActionInterface::class,
                'default' => Services\CreateQuestionnaireAction::class,
            ],
            'update_questionnaire' => [
                'interface' => UpdateQuestionnaireActionInterface::class,
                'default' => Services\UpdateQuestionnaireAction::class,
            ],
            'publish_questionnaire' => [
                'interface' => PublishQuestionnaireActionInterface::class,
                'default' => Services\PublishQuestionnaireAction::class,
            ],
            'close_questionnaire' => [
                'interface' => CloseQuestionnaireActionInterface::class,
                'default' => Services\CloseQuestionnaireAction::class,
            ],
            'submit_response' => [
                'interface' => SubmitResponseActionInterface::class,
                'default' => Services\SubmitResponseAction::class,
            ],
        ];

        foreach ($actionBindings as $key => $binding) {
            $this->app->bind(
                $binding['interface'],
                function ($app) use ($key, $binding) {
                    $concrete = config("questionnaire.actions.{$key}", $binding['default']);

                    if ($concrete === $binding['default']) {
                        return $app->build($binding['default']);
                    }

                    return $app->make($concrete);
                }
            );

            // Also bind concrete class for backward compatibility
            $this->app->bind(
                $binding['default'],
                fn ($app) => $app->make($binding['interface'])
            );
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            QuestionnaireRepositoryInterface::class,
            ResponseRepositoryInterface::class,
            QuestionTypeRegistryInterface::class,
            ValidationStrategyInterface::class,
            DuplicateSubmissionGuardInterface::class,
            DuplicateSubmissionGuardFactory::class,
            ExporterInterface::class,
            AssetManager::class,
            CreateQuestionnaireActionInterface::class,
            UpdateQuestionnaireActionInterface::class,
            PublishQuestionnaireActionInterface::class,
            CloseQuestionnaireActionInterface::class,
            SubmitResponseActionInterface::class,
        ];
    }
}
