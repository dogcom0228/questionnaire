# Questionnaire Package - DDD/CQRS/Event Sourcing

A Laravel package for creating and managing questionnaires using **Domain-Driven Design (DDD)**, **CQRS**, and **Event Sourcing** patterns.

## Architecture Overview

This package follows a clean architecture approach with strict layer separation:

```
src/
├── Domain/              # Pure business logic (framework-agnostic)
│   ├── Questionnaire/   # Questionnaire bounded context
│   ├── Response/        # Response bounded context  
│   └── Shared/          # Shared kernel
├── Application/         # Use cases and handlers
│   ├── Command/         # Write operations
│   ├── CommandHandler/  # Command handlers
│   ├── Query/           # Read operations
│   ├── QueryHandler/    # Query handlers
│   ├── Projector/       # Event → Read Model projectors
│   └── Mapper/          # Domain ↔ DTO mapping
├── Infrastructure/      # Framework integration
│   ├── Http/            # Controllers, Requests, Resources
│   ├── Console/         # Artisan commands
│   ├── Persistence/     # Event-sourced repositories
│   └── ReadModel/       # Eloquent read models
└── Contracts/           # Interfaces
```

### Key Patterns

- **DDD**: Value Objects, Aggregates, Entities, Domain Events, Specifications
- **CQRS**: Separate read and write models
- **Event Sourcing**: All state changes recorded as events (using Spatie's laravel-event-sourcing)
- **Hexagonal Architecture**: Domain independent of infrastructure

## Installation

```bash
composer require liangjin0228/questionnaire
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="Liangjin0228\Questionnaire\QuestionnaireServiceProvider"
```

### Run Migrations

```bash
php artisan migrate
```

This creates:
- `stored_events` - Event store
- `snapshots` - Aggregate snapshots
- `questionnaires`, `questions`, `questionnaire_responses`, `questionnaire_answers` - Read models

## Usage

### Creating a Questionnaire (CQRS Command)

```php
use Liangjin0228\Questionnaire\Contracts\Application\CommandBusInterface;
use Liangjin0228\Questionnaire\Application\Command\CreateQuestionnaireCommand;
use Ramsey\Uuid\Uuid;

$commandBus = app(CommandBusInterface::class);

$questionnaireId = (string) Uuid::uuid4();

$commandBus->dispatch(new CreateQuestionnaireCommand(
    questionnaireId: $questionnaireId,
    title: 'Customer Satisfaction Survey',
    slug: 'customer-satisfaction-2024',
    description: 'Annual customer feedback questionnaire',
    startsAt: now(),
    endsAt: now()->addDays(30),
    settings: [
        'requires_auth' => false,
        'allow_multiple_submissions' => false,
    ]
));
```

### Querying Questionnaires (CQRS Query)

```php
use Liangjin0228\Questionnaire\Contracts\Application\QueryBusInterface;
use Liangjin0228\Questionnaire\Application\Query\ListQuestionnairesQuery;

$queryBus = app(QueryBusInterface::class);

$questionnaires = $queryBus->ask(new ListQuestionnairesQuery(
    status: 'published',
    page: 1,
    perPage: 10
));
```

### Publishing a Questionnaire

```php
use Liangjin0228\Questionnaire\Application\Command\PublishQuestionnaireCommand;

$commandBus->dispatch(new PublishQuestionnaireCommand(
    questionnaireId: $questionnaireId
));
```

### Submitting a Response

```php
use Liangjin0228\Questionnaire\Application\Command\SubmitResponseCommand;

$commandBus->dispatch(new SubmitResponseCommand(
    questionnaireId: $questionnaireId,
    answers: [
        1 => 'Very satisfied',      // question_id => answer
        2 => ['Option A', 'Option B'], // Multiple choice
        3 => 5,                        // Rating
    ],
    respondent: [
        'user_id' => auth()->id(),
        'type' => 'authenticated',
    ],
    ipAddress: request()->ip(),
    metadata: [
        'user_agent' => request()->userAgent(),
    ]
));
```

## Event Sourcing

All write operations generate domain events that are stored in the `stored_events` table.

### Available Domain Events

**Questionnaire Events:**
- `QuestionnaireCreated`
- `QuestionnaireUpdated`
- `QuestionnairePublished`
- `QuestionnaireClosed`
- `QuestionAdded`
- `QuestionUpdated`
- `QuestionRemoved`

**Response Events:**
- `ResponseSubmitted`

### Rebuilding Projections

If read models become inconsistent, rebuild them from events:

```bash
php artisan questionnaire:rebuild-projections
```

### Creating Snapshots

For performance, create snapshots of aggregates:

```bash
php artisan questionnaire:create-snapshot {aggregate_uuid}
```

### Replaying Events

Replay specific events for debugging:

```bash
php artisan questionnaire:replay-events {aggregate_uuid}
```

## Testing

The package uses Pest for testing.

### Run All Tests

```bash
vendor/bin/pest
```

### Run Unit Tests (Domain Layer)

```bash
vendor/bin/pest tests/Unit/Domain
```

### Run Feature Tests

```bash
vendor/bin/pest tests/Feature
```

### Architecture Tests

Architecture tests enforce DDD boundaries:

```bash
vendor/bin/pest tests/Arch.php
```

### Code Quality

**PHPStan (Level 9):**
```bash
vendor/bin/phpstan analyse
```

## Domain Concepts

### Value Objects

Immutable objects identified by their value:
- `QuestionnaireId`, `QuestionnaireTitle`, `QuestionnaireSlug`
- `DateRange`, `QuestionnaireSettings`
- `ResponseId`, `AnswerId`, `AnswerValue`
- `IpAddress`, `UserAgent`, `Respondent`

### Aggregates

Consistency boundaries:
- `Questionnaire` - Manages questionnaire lifecycle and questions
- `Response` - Manages response submission and answers

### Entities

Objects with identity:
- `Question` - Individual question within questionnaire
- `Answer` - Individual answer within response

### Specifications

Business rules as reusable objects:
- `QuestionnaireIsActiveSpecification`
- `QuestionnaireCanBePublishedSpecification`
- `ResponseIsCompleteSpecification`

## Configuration

Configuration file: `config/questionnaire.php`

```php
return [
    'table_names' => [
        'questionnaires' => 'questionnaires',
        'questions' => 'questions',
        'responses' => 'questionnaire_responses',
        'answers' => 'questionnaire_answers',
    ],
    
    'event_sourcing' => [
        'enabled' => true,
        'snapshot_frequency' => 100,
    ],
    
    'validation' => [
        'strategy' => DefaultValidationStrategy::class,
    ],
    
    'features' => [
        'log_submissions' => false,
        'send_notifications' => false,
    ],
];
```

## HTTP API Endpoints

### Questionnaire Management

```
POST   /api/questionnaires              - Create questionnaire
GET    /api/questionnaires              - List questionnaires  
GET    /api/questionnaires/{id}         - Get questionnaire
PUT    /api/questionnaires/{id}         - Update questionnaire
DELETE /api/questionnaires/{id}         - Delete questionnaire
POST   /api/questionnaires/{id}/publish - Publish questionnaire
POST   /api/questionnaires/{id}/close   - Close questionnaire
```

### Response Management

```
POST   /api/questionnaires/{id}/responses     - Submit response
GET    /api/questionnaires/{id}/responses     - List responses
GET    /api/questionnaires/{id}/statistics    - Get statistics
```

### Public Access

```
GET    /api/public/questionnaires/{slug}      - Get published questionnaire by slug
POST   /api/public/questionnaires/{slug}      - Submit response to public questionnaire
```

## Requirements

- PHP 8.1+
- Laravel 10.x or 11.x
- MySQL 8.0+ or PostgreSQL 13+

## License

MIT License

## Credits

Built with:
- [Spatie Laravel Event Sourcing](https://github.com/spatie/laravel-event-sourcing)
- [Ramsey UUID](https://github.com/ramsey/uuid)
- [Pest PHP](https://pestphp.com/)
