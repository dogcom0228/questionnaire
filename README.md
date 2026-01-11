# Laravel Questionnaire Package

A full-featured, highly customizable Laravel questionnaire/survey package with a Vue 3 + Vuetify 3 + Inertia.js frontend.

## Highlights

- Auto-discovery with minimal setup
- Extensible question types (7 built-in, easy to add more)
- Duplicate submission guards (per user, session, IP, etc.)
- Policy-based authorization for every operation
- Event-driven lifecycle hooks
- CSV export with UTF-8 BOM support
- Optional RESTful API endpoints

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- Node.js 18+
- Vue 3.4+
- Vuetify 3.5+

## Quick Start

1. Install the package: `composer require liangjin0228/questionnaire`
2. Publish config/migrations/views/frontend as needed (see below).
3. Run `php artisan migrate`.
4. Install frontend deps and add the Vite config.
5. Visit `/questionnaire/admin`.

## Installation

### 1) Install via Composer

```bash
composer require liangjin0228/questionnaire
```

### 2) Publish assets (pick what you need)

```bash
# Config
php artisan vendor:publish --tag=questionnaire-config

# Migrations
php artisan vendor:publish --tag=questionnaire-migrations

# Views (Inertia pages for customization)
php artisan vendor:publish --tag=questionnaire-views

# Frontend source (Vue components)
php artisan vendor:publish --tag=questionnaire-frontend

# Everything
php artisan vendor:publish --provider="Liangjin0228\Questionnaire\QuestionnaireServiceProvider"
```

### 3) Run migrations

```bash
php artisan migrate
```

### 4) Frontend dependencies

```bash
npm install @inertiajs/vue3 vue vuetify @mdi/font vuedraggable
npm install -D vite-plugin-vuetify
```

### 5) Vite configuration

Add Vuetify and the alias to your `vite.config.js`:

```js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'

export default defineConfig({
    plugins: [vue(), vuetify({ autoImport: true })],
    resolve: {
        alias: {
            '@questionnaire':
                'vendor/liangjin0228/questionnaire/resources/js/questionnaire',
        },
    },
})
```

## Usage

```bash
# Guided installation steps
php artisan questionnaire:install

# List available question types
php artisan questionnaire:question-types
```

## Configuration

Publish the config to edit it at [config/questionnaire.php](config/questionnaire.php).

### Feature toggles

```php
'features' => [
    'admin' => true,
    'public_fill' => true,
    'results' => true,
    'api' => false,
    'frontend' => true,
    'authorization' => true,
    'export_csv' => true,
],
```

### Route prefixes and middleware

```php
'routes' => [
    'prefix' => 'questionnaire',
    'admin_prefix' => 'admin',
    'public_prefix' => 'survey',
    'middleware' => ['web'],
    'admin_middleware' => ['web', 'auth'],
],
```

### Override controllers

```php
'controllers' => [
    'web' => \App\Http\Controllers\CustomQuestionnaireController::class,
    'api' => \App\Http\Controllers\Api\CustomQuestionnaireApiController::class,
],
```

### Swap services/repositories

```php
'services' => [
    'questionnaire_repository' => \App\Repositories\CustomQuestionnaireRepository::class,
    'response_repository' => \App\Repositories\CustomResponseRepository::class,
    'validation_strategy' => \App\Validation\CustomValidationStrategy::class,
],
```

### Register custom question types

```php
'question_types' => [
    // built-ins ...
    'rating' => \App\QuestionTypes\RatingQuestionType::class,
],
```

## Custom Question Type (example)

```php
<?php

namespace App\QuestionTypes;

use Liangjin0228\Questionnaire\QuestionTypes\AbstractQuestionType;

class RatingQuestionType extends AbstractQuestionType
{
    public function getIdentifier(): string { return 'rating'; }
    public function getName(): string { return 'Rating'; }
    public function getDescription(): string { return 'A star rating question'; }
    public function getIcon(): string { return 'mdi-star'; }

    public function getValidationRules(array $question): array
    {
        $rules = $question['required'] ?? false ? ['required'] : ['nullable'];
        $rules[] = 'integer';
        $rules[] = 'min:1';
        $rules[] = 'max:' . ($question['settings']['max'] ?? 5);
        return $rules;
    }

    public function getDefaultSettings(): array
    {
        return ['min' => 1, 'max' => 5];
    }
}
```

## Events

| Event                  | When it fires                    |
| ---------------------- | -------------------------------- |
| QuestionnaireCreated   | After a questionnaire is created |
| QuestionnaireUpdated   | After a questionnaire is updated |
| QuestionnairePublished | After publication                |
| QuestionnaireClosed    | After closing                    |
| QuestionnaireDeleted   | After deletion                   |
| ResponseSubmitted      | After a response is submitted    |
| ResponseDeleted        | After a response is deleted      |

Register listeners in your EventServiceProvider:

```php
protected $listen = [
    \Liangjin0228\Questionnaire\Events\ResponseSubmitted::class => [
        \App\Listeners\SendResponseNotification::class,
        \App\Listeners\CalculateScore::class,
    ],
];
```

## Duplicate Submission Guards

Built-ins:

- allow_multiple
- one_per_user
- one_per_session
- one_per_ip

Custom example:

```php
<?php

namespace App\Guards;

use Liangjin0228\Questionnaire\Contracts\DuplicateSubmissionGuardInterface;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class OnePerDeviceGuard implements DuplicateSubmissionGuardInterface
{
    public function check(Questionnaire $questionnaire): bool
    {
        $deviceId = request()->fingerprint();
        return !$questionnaire->responses()
            ->where('metadata->device_id', $deviceId)
            ->exists();
    }

    public function getMessage(): string
    {
        return 'You have already submitted from this device.';
    }
}
```

Register it under `duplicate_guards` in [config/questionnaire.php](config/questionnaire.php).

## Authorization

- Override policies via the `policies` config entry.
- Disable authorization by setting `'authorization' => false` under `features`.

## API

Enable by setting `'api' => true` under `features`.

| Method | Endpoint                          | Description           |
| ------ | --------------------------------- | --------------------- |
| GET    | /api/questionnaire                | List questionnaires   |
| POST   | /api/questionnaire                | Create questionnaire  |
| GET    | /api/questionnaire/{id}           | Get questionnaire     |
| PUT    | /api/questionnaire/{id}           | Update questionnaire  |
| DELETE | /api/questionnaire/{id}           | Delete questionnaire  |
| POST   | /api/questionnaire/{id}/publish   | Publish questionnaire |
| POST   | /api/questionnaire/{id}/responses | Submit response       |
| GET    | /api/questionnaire/{id}/responses | Fetch responses       |

## Frontend Customization

1. Publish frontend assets: `php artisan vendor:publish --tag=questionnaire-frontend`.
2. Edit the Vue components as needed.
3. Update your Vite alias to point to your customized components.

## Testing

```bash
php artisan test --filter=Questionnaire
./vendor/bin/phpunit packages/questionnaire
```

## Directory Layout

```
src/
├── Console/              # Artisan commands
├── Contracts/            # Interfaces
├── Events/               # Domain events
├── Exceptions/           # Custom exceptions
├── Export/               # CSV exporter
├── Guards/               # Duplicate submission guards
├── Http/
│   ├── Controllers/      # Controllers
│   └── Requests/         # Form requests
├── Listeners/            # Event listeners
├── Models/               # Eloquent models
├── Policies/             # Authorization policies
├── QuestionTypes/        # Question type classes
├── Repositories/         # Data access
├── Services/             # Business logic
└── QuestionnaireServiceProvider.php
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT License. See [LICENSE](LICENSE).

## Contributing

PRs are welcome. Please follow PSR-12, include tests, and update docs.

## Support

Open a GitHub Issue if you have questions or run into problems.
