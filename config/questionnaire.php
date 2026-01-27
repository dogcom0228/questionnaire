<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Questionnaire System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Questionnaire package.
    | You can customize the behavior, routing, features, and UI here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features of the questionnaire package.
    |
    */
    'features' => [
        // Enable the admin module (questionnaire management)
        'admin' => true,

        // Enable the public fill module
        'public_fill' => true,

        // Enable the results/statistics module
        'results' => true,

        // Enable API routes
        'api' => true,

        // Enable the built-in frontend UI (Inertia + Vue)
        'frontend' => true,

        // Enable authorization (policies)
        'authorization' => true,

        // Enable CSV export functionality
        'export_csv' => true,

        // Log response submissions
        'log_submissions' => false,

        // Send email notifications on submission
        'email_notifications' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Routing Configuration
    |--------------------------------------------------------------------------
    */
    'routes' => [
        // Enable or disable the default routes
        'enabled' => true,

        // Route prefix for admin routes
        'prefix' => 'questionnaire',

        // Route prefix for public routes
        'public_prefix' => 'survey',

        // API route prefix
        'api_prefix' => 'api/questionnaire',

        // Middleware for admin routes
        'middleware' => ['web', 'auth'],

        // Middleware for public routes (includes rate limiting)
        'public_middleware' => ['web', 'throttle:10,1'],

        // Middleware for API routes
        'api_middleware' => ['api', 'throttle:60,1'],

        // Use slug instead of ID in URLs
        'use_slug' => false,

        // Route name prefix
        'name_prefix' => 'questionnaire.',

        // Domain constraint (null for no constraint)
        'domain' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | You can swap the implementations of these models by changing the class here.
    | Your custom models should extend the package's base models.
    |
    */
    'models' => [
        'questionnaire' => \Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire::class,
        'question' => \Liangjin0228\Questionnaire\Domain\Question\Models\Question::class,
        'response' => \Liangjin0228\Questionnaire\Domain\Response\Models\Response::class,
        'answer' => \Liangjin0228\Questionnaire\Domain\Response\Models\Answer::class,
        'user' => null, // null will use config('auth.providers.users.model')
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Customize the database table names used by the package.
    |
    */
    'table_names' => [
        'questionnaires' => 'questionnaires',
        'questions' => 'questions',
        'responses' => 'questionnaire_responses',
        'answers' => 'questionnaire_answers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Controller Configuration
    |--------------------------------------------------------------------------
    |
    | Override the default controllers with your own implementations.
    |
    */
    'controllers' => [
        'questionnaire' => \Liangjin0228\Questionnaire\Infrastructure\Http\Controllers\QuestionnaireController::class,
        'api' => \Liangjin0228\Questionnaire\Infrastructure\Http\Controllers\QuestionnaireController::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Bindings
    |--------------------------------------------------------------------------
    |
    | Override the default service implementations. These are bound in the
    | service provider and can be overridden by binding your own classes
    | in your application's service provider.
    |
    */
    'bindings' => [
        'questionnaire_repository' => \Liangjin0228\Questionnaire\Infrastructure\Persistence\Repositories\EloquentQuestionnaireRepository::class,
        'response_repository' => \Liangjin0228\Questionnaire\Infrastructure\Persistence\Repositories\EloquentResponseRepository::class,
        'validation_strategy' => \Liangjin0228\Questionnaire\Services\DefaultValidationStrategy::class,
        'question_type_registry' => \Liangjin0228\Questionnaire\Managers\QuestionTypeManager::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Question Types
    |--------------------------------------------------------------------------
    |
    | Register question types to be available in the questionnaire builder.
    | You can add your own custom question types by adding them to this array.
    |
    */
    'question_types' => [
        \Liangjin0228\Questionnaire\QuestionTypes\TextQuestionType::class,
        \Liangjin0228\Questionnaire\QuestionTypes\TextareaQuestionType::class,
        \Liangjin0228\Questionnaire\QuestionTypes\RadioQuestionType::class,
        \Liangjin0228\Questionnaire\QuestionTypes\CheckboxQuestionType::class,
        \Liangjin0228\Questionnaire\QuestionTypes\SelectQuestionType::class,
        \Liangjin0228\Questionnaire\QuestionTypes\NumberQuestionType::class,
        \Liangjin0228\Questionnaire\QuestionTypes\DateQuestionType::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Duplicate Submission Guards
    |--------------------------------------------------------------------------
    |
    | Register duplicate submission guard strategies.
    |
    */
    'duplicate_guards' => [
        'allow_multiple' => \Liangjin0228\Questionnaire\Guards\AllowMultipleGuard::class,
        'one_per_user' => \Liangjin0228\Questionnaire\Guards\OnePerUserGuard::class,
        'one_per_session' => \Liangjin0228\Questionnaire\Guards\OnePerSessionGuard::class,
        'one_per_ip' => \Liangjin0228\Questionnaire\Guards\OnePerIpGuard::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Policy Configuration
    |--------------------------------------------------------------------------
    |
    | Override the default policies with your own implementations.
    |
    */
    'policies' => [
        'questionnaire' => \Liangjin0228\Questionnaire\Policies\QuestionnairePolicy::class,
        'response' => \Liangjin0228\Questionnaire\Policies\ResponsePolicy::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Classes
    |--------------------------------------------------------------------------
    |
    | You can replace the default action classes with your own implementations.
    | Valid keys: create_questionnaire, update_questionnaire, publish_questionnaire,
    | close_questionnaire, submit_response, add_question, delete_question.
    |
    */
    'actions' => [
        // 'create_questionnaire' => \Liangjin0228\Questionnaire\Services\CreateQuestionnaireAction::class,
        // 'add_question' => \Liangjin0228\Questionnaire\Services\AddQuestionAction::class,
        // 'delete_question' => \Liangjin0228\Questionnaire\Services\DeleteQuestionAction::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the frontend UI settings.
    |
    */
    'ui' => [
        // Blade root view for Inertia
        'root_view' => 'questionnaire::app',

        // Vue component prefix (for page resolution)
        'component_prefix' => 'Questionnaire/',

        // Theme configuration
        'theme' => [
            'primary' => '#1976D2',
            'secondary' => '#424242',
            'accent' => '#82B1FF',
            'error' => '#FF5252',
            'info' => '#2196F3',
            'success' => '#4CAF50',
            'warning' => '#FFC107',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'log_channel' => env('QUESTIONNAIRE_LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    */
    'export' => [
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'include_headers' => true,
        ],
    ],
];
