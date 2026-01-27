# 測試策略與規範

> **文件**: 09-testing-strategy.md  
> **上一篇**: [08-migration-guide.md](./08-migration-guide.md)

---

## 1. 測試策略概述

### 1.1 測試金字塔

```
                    ┌─────────────┐
                    │     E2E     │  ← 少量
                    │   Tests     │
                    ├─────────────┤
                    │ Integration │  ← 適量
                    │   Tests     │
                    ├─────────────┤
                    │    Unit     │  ← 大量
                    │   Tests     │
                    └─────────────┘
```

### 1.2 目標覆蓋率

| 層級 | 目標覆蓋率 | 測試類型 |
|------|-----------|----------|
| Domain | >95% | Unit |
| Application | >90% | Unit + Integration |
| Infrastructure | >80% | Integration |
| 整體 | >90% | All |

### 1.3 測試工具

- **Pest PHP** - 主要測試框架
- **Mockery** - Mock 框架
- **Pest Architecture Plugin** - 架構測試
- **Orchestra Testbench** - Laravel Package 測試

---

## 2. 測試目錄結構

```
tests/
├── Pest.php                           # Pest 配置
├── TestCase.php                       # 基礎測試類
│
├── Unit/                              # 單元測試
│   ├── Domain/
│   │   ├── Shared/
│   │   │   ├── AggregateRootTest.php
│   │   │   ├── ValueObjectTest.php
│   │   │   └── SpecificationTest.php
│   │   ├── Questionnaire/
│   │   │   ├── Aggregate/
│   │   │   │   └── QuestionnaireTest.php
│   │   │   ├── ValueObject/
│   │   │   │   ├── QuestionnaireIdTest.php
│   │   │   │   ├── QuestionnaireTitleTest.php
│   │   │   │   └── DateRangeTest.php
│   │   │   ├── Specification/
│   │   │   │   └── QuestionnaireIsActiveSpecTest.php
│   │   │   └── QuestionType/
│   │   │       └── TextQuestionTypeTest.php
│   │   └── Response/
│   │       ├── Aggregate/
│   │       │   └── ResponseTest.php
│   │       └── Guard/
│   │           └── OnePerUserGuardTest.php
│   │
│   └── Application/
│       ├── Command/
│       │   └── CreateQuestionnaireHandlerTest.php
│       ├── Query/
│       │   └── ListQuestionnairesHandlerTest.php
│       └── Mapper/
│           └── QuestionnaireMapperTest.php
│
├── Feature/                           # 功能測試
│   ├── Api/
│   │   ├── QuestionnaireApiTest.php
│   │   ├── ResponseApiTest.php
│   │   └── PublicApiTest.php
│   ├── EventSourcing/
│   │   ├── QuestionnaireEventSourcingTest.php
│   │   └── ProjectorTest.php
│   └── Command/
│       └── RebuildProjectionsCommandTest.php
│
├── Arch/                              # 架構測試
│   ├── DomainLayerTest.php
│   ├── ApplicationLayerTest.php
│   └── InfrastructureLayerTest.php
│
└── Fixtures/                          # 測試固件
    ├── QuestionnaireFixture.php
    └── ResponseFixture.php
```

---

## 3. Pest 配置

### 3.1 tests/Pest.php

```php
<?php

declare(strict_types=1);

use Liangjin0228\Questionnaire\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(TestCase::class)->in('Unit', 'Feature');
uses(RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeUuid', function () {
    return $this->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

expect()->extend('toBeValueObject', function () {
    return $this->toBeInstanceOf(\Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject::class);
});

expect()->extend('toBeAggregateRoot', function () {
    return $this->toBeInstanceOf(\Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateRoot::class);
});

expect()->extend('toBeDomainEvent', function () {
    return $this->toBeInstanceOf(\Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent::class);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function createQuestionnaire(array $overrides = []): \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire
{
    $defaults = [
        'title' => 'Test Questionnaire',
        'description' => null,
    ];

    $data = array_merge($defaults, $overrides);

    return \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::create(
        id: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId::generate(),
        title: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle::fromString($data['title']),
        description: $data['description']
    );
}

function createQuestion(array $overrides = []): \Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question
{
    $defaults = [
        'type' => \Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\QuestionType::TEXT,
        'content' => 'Test Question',
        'order' => 0,
        'isRequired' => false,
    ];

    $data = array_merge($defaults, $overrides);

    return \Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question::create(
        id: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId::generate(),
        type: $data['type'],
        content: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionContent::fromString($data['content']),
        order: $data['order'],
        isRequired: $data['isRequired']
    );
}
```

### 3.2 tests/TestCase.php

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Tests;

use Liangjin0228\Questionnaire\QuestionnaireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\EventSourcing\EventSourcingServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // 設置測試環境
    }

    protected function getPackageProviders($app): array
    {
        return [
            EventSourcingServiceProvider::class,
            QuestionnaireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // 使用 SQLite 記憶體資料庫
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Event Sourcing 配置
        $app['config']->set('event-sourcing.stored_event_model', \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Event Sourcing 遷移
        $this->artisan('migrate', ['--database' => 'testing']);
    }
}
```

---

## 4. 單元測試範例

### 4.1 Value Object 測試

```php
<?php
// tests/Unit/Domain/Questionnaire/ValueObject/QuestionnaireTitleTest.php

declare(strict_types=1);

use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionnaireException;

describe('QuestionnaireTitle', function () {
    it('can be created from a valid string', function () {
        $title = QuestionnaireTitle::fromString('Test Questionnaire');

        expect($title)
            ->toBeValueObject()
            ->and((string) $title)->toBe('Test Questionnaire');
    });

    it('trims whitespace', function () {
        $title = QuestionnaireTitle::fromString('  Test Questionnaire  ');

        expect((string) $title)->toBe('Test Questionnaire');
    });

    it('throws exception for empty title', function () {
        QuestionnaireTitle::fromString('');
    })->throws(InvalidQuestionnaireException::class, 'title cannot be empty');

    it('throws exception for whitespace-only title', function () {
        QuestionnaireTitle::fromString('   ');
    })->throws(InvalidQuestionnaireException::class);

    it('throws exception for title exceeding max length', function () {
        $longTitle = str_repeat('a', 256);
        QuestionnaireTitle::fromString($longTitle);
    })->throws(InvalidQuestionnaireException::class, 'cannot exceed');

    it('can compare equality', function () {
        $title1 = QuestionnaireTitle::fromString('Test');
        $title2 = QuestionnaireTitle::fromString('Test');
        $title3 = QuestionnaireTitle::fromString('Different');

        expect($title1->equals($title2))->toBeTrue()
            ->and($title1->equals($title3))->toBeFalse();
    });
});
```

### 4.2 Aggregate 測試

```php
<?php
// tests/Unit/Domain/Questionnaire/Aggregate/QuestionnaireTest.php

declare(strict_types=1);

use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireCreated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnairePublished;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionAdded;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionnaireException;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;

describe('Questionnaire Aggregate', function () {
    describe('creation', function () {
        it('can be created with valid data', function () {
            $questionnaire = createQuestionnaire();

            expect($questionnaire)
                ->toBeAggregateRoot()
                ->and($questionnaire->getStatus())->toBe(QuestionnaireStatus::DRAFT)
                ->and($questionnaire->getUncommittedEventsCount())->toBe(1);
        });

        it('records QuestionnaireCreated event', function () {
            $questionnaire = createQuestionnaire(['title' => 'My Survey']);
            $events = $questionnaire->releaseEvents();

            expect($events)->toHaveCount(1)
                ->and($events[0])->toBeInstanceOf(QuestionnaireCreated::class)
                ->and((string) $events[0]->title)->toBe('My Survey');
        });
    });

    describe('adding questions', function () {
        it('can add a question', function () {
            $questionnaire = createQuestionnaire();
            $question = createQuestion();

            $questionnaire->addQuestion($question);

            expect($questionnaire->getQuestions())->toHaveCount(1)
                ->and($questionnaire->getUncommittedEventsCount())->toBe(2);
        });

        it('records QuestionAdded event', function () {
            $questionnaire = createQuestionnaire();
            $questionnaire->releaseEvents(); // Clear creation event

            $question = createQuestion(['content' => 'What is your name?']);
            $questionnaire->addQuestion($question);

            $events = $questionnaire->releaseEvents();

            expect($events)->toHaveCount(1)
                ->and($events[0])->toBeInstanceOf(QuestionAdded::class);
        });

        it('throws exception for duplicate question', function () {
            $questionnaire = createQuestionnaire();
            $question = createQuestion();

            $questionnaire->addQuestion($question);
            $questionnaire->addQuestion($question);
        })->throws(InvalidQuestionnaireException::class, 'already exists');
    });

    describe('publishing', function () {
        it('can publish questionnaire with questions', function () {
            $questionnaire = createQuestionnaire();
            $questionnaire->addQuestion(createQuestion());

            $questionnaire->publish();

            expect($questionnaire->getStatus())->toBe(QuestionnaireStatus::PUBLISHED);
        });

        it('throws exception when publishing without questions', function () {
            $questionnaire = createQuestionnaire();
            $questionnaire->publish();
        })->throws(InvalidQuestionnaireException::class, 'without questions');

        it('throws exception when publishing non-draft questionnaire', function () {
            $questionnaire = createQuestionnaire();
            $questionnaire->addQuestion(createQuestion());
            $questionnaire->publish();

            $questionnaire->publish(); // Try to publish again
        })->throws(InvalidQuestionnaireException::class);

        it('cannot modify questions after publishing', function () {
            $questionnaire = createQuestionnaire();
            $questionnaire->addQuestion(createQuestion());
            $questionnaire->publish();

            $questionnaire->addQuestion(createQuestion());
        })->throws(InvalidQuestionnaireException::class, 'after publish');
    });

    describe('event reconstitution', function () {
        it('can be reconstituted from events', function () {
            // Create and get events
            $original = createQuestionnaire(['title' => 'Survey']);
            $original->addQuestion(createQuestion(['content' => 'Q1']));
            $events = $original->releaseEvents();

            // Reconstitute
            $reconstituted = Questionnaire::reconstituteFromHistory($events);

            expect((string) $reconstituted->getTitle())->toBe('Survey')
                ->and($reconstituted->getQuestions())->toHaveCount(1)
                ->and($reconstituted->getAggregateVersion())->toBe(2);
        });
    });
});
```

### 4.3 Specification 測試

```php
<?php
// tests/Unit/Domain/Questionnaire/Specification/QuestionnaireIsActiveSpecTest.php

declare(strict_types=1);

use Liangjin0228\Questionnaire\Domain\Questionnaire\Specification\QuestionnaireIsActiveSpec;

describe('QuestionnaireIsActiveSpec', function () {
    it('returns true for published questionnaire with no date range', function () {
        $questionnaire = createQuestionnaire();
        $questionnaire->addQuestion(createQuestion());
        $questionnaire->publish();

        $spec = new QuestionnaireIsActiveSpec();

        expect($spec->isSatisfiedBy($questionnaire))->toBeTrue();
    });

    it('returns false for draft questionnaire', function () {
        $questionnaire = createQuestionnaire();

        $spec = new QuestionnaireIsActiveSpec();

        expect($spec->isSatisfiedBy($questionnaire))->toBeFalse();
    });

    it('returns false for closed questionnaire', function () {
        $questionnaire = createQuestionnaire();
        $questionnaire->addQuestion(createQuestion());
        $questionnaire->publish();
        $questionnaire->close();

        $spec = new QuestionnaireIsActiveSpec();

        expect($spec->isSatisfiedBy($questionnaire))->toBeFalse();
    });

    it('provides unsatisfied reason', function () {
        $questionnaire = createQuestionnaire();

        $spec = new QuestionnaireIsActiveSpec();

        expect($spec->getUnsatisfiedReason($questionnaire))
            ->toBe('Questionnaire is not currently accepting responses');
    });
});
```

### 4.4 Command Handler 測試

```php
<?php
// tests/Unit/Application/Command/CreateQuestionnaireHandlerTest.php

declare(strict_types=1);

use Liangjin0228\Questionnaire\Application\Command\Questionnaire\CreateQuestionnaire\CreateQuestionnaireCommand;
use Liangjin0228\Questionnaire\Application\Command\Questionnaire\CreateQuestionnaire\CreateQuestionnaireHandler;
use Liangjin0228\Questionnaire\Application\DTO\Input\QuestionnaireInput;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\EventBusInterface;
use Liangjin0228\Questionnaire\Contracts\Domain\Repository\QuestionnaireRepositoryInterface;

describe('CreateQuestionnaireHandler', function () {
    it('creates a questionnaire and returns id', function () {
        // Arrange
        $repository = Mockery::mock(QuestionnaireRepositoryInterface::class);
        $repository->shouldReceive('save')->once();

        $eventBus = Mockery::mock(EventBusInterface::class);
        $eventBus->shouldReceive('dispatch')->once();

        $handler = new CreateQuestionnaireHandler($repository, $eventBus);

        $command = new CreateQuestionnaireCommand(
            input: new QuestionnaireInput(
                title: 'Test Survey',
                description: 'A test survey'
            ),
            userId: 'user-123'
        );

        // Act
        $id = $handler->handle($command);

        // Assert
        expect($id)->toBeUuid();
    });

    it('saves the questionnaire to repository', function () {
        $repository = Mockery::mock(QuestionnaireRepositoryInterface::class);
        $repository->shouldReceive('save')
            ->once()
            ->withArgs(function ($questionnaire) {
                return (string) $questionnaire->getTitle() === 'Test Survey';
            });

        $eventBus = Mockery::mock(EventBusInterface::class);
        $eventBus->shouldReceive('dispatch');

        $handler = new CreateQuestionnaireHandler($repository, $eventBus);

        $command = new CreateQuestionnaireCommand(
            input: new QuestionnaireInput(title: 'Test Survey')
        );

        $handler->handle($command);
    });

    it('publishes domain events', function () {
        $repository = Mockery::mock(QuestionnaireRepositoryInterface::class);
        $repository->shouldReceive('save');

        $eventBus = Mockery::mock(EventBusInterface::class);
        $eventBus->shouldReceive('dispatch')
            ->once()
            ->withArgs(function ($event) {
                return $event instanceof \Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireCreated;
            });

        $handler = new CreateQuestionnaireHandler($repository, $eventBus);

        $command = new CreateQuestionnaireCommand(
            input: new QuestionnaireInput(title: 'Test Survey')
        );

        $handler->handle($command);
    });
});
```

---

## 5. 功能測試範例

### 5.1 API 測試

```php
<?php
// tests/Feature/Api/QuestionnaireApiTest.php

declare(strict_types=1);

use Illuminate\Foundation\Testing\WithFaker;

uses(WithFaker::class);

describe('Questionnaire API', function () {
    describe('POST /api/questionnaire', function () {
        it('creates a questionnaire', function () {
            $user = createUser();

            $response = $this->actingAs($user)
                ->postJson('/api/questionnaire', [
                    'title' => 'Customer Satisfaction Survey',
                    'description' => 'Please share your feedback',
                    'questions' => [
                        [
                            'type' => 'text',
                            'content' => 'What is your name?',
                            'is_required' => true,
                        ],
                    ],
                ]);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => ['id'],
                    'message',
                ]);

            expect($response->json('data.id'))->toBeUuid();
        });

        it('validates required fields', function () {
            $user = createUser();

            $response = $this->actingAs($user)
                ->postJson('/api/questionnaire', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['title']);
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/questionnaire', [
                'title' => 'Test',
            ]);

            $response->assertStatus(401);
        });
    });

    describe('GET /api/questionnaire', function () {
        it('lists user questionnaires', function () {
            $user = createUser();
            createQuestionnaireModel(['user_id' => $user->id]);
            createQuestionnaireModel(['user_id' => $user->id]);
            createQuestionnaireModel(); // Other user's questionnaire

            $response = $this->actingAs($user)
                ->getJson('/api/questionnaire');

            $response->assertStatus(200)
                ->assertJsonCount(2, 'data');
        });

        it('supports pagination', function () {
            $user = createUser();
            for ($i = 0; $i < 20; $i++) {
                createQuestionnaireModel(['user_id' => $user->id]);
            }

            $response = $this->actingAs($user)
                ->getJson('/api/questionnaire?page=2&per_page=10');

            $response->assertStatus(200)
                ->assertJsonCount(10, 'data')
                ->assertJsonPath('meta.page', 2);
        });

        it('supports status filter', function () {
            $user = createUser();
            createQuestionnaireModel(['user_id' => $user->id, 'status' => 'draft']);
            createQuestionnaireModel(['user_id' => $user->id, 'status' => 'published']);

            $response = $this->actingAs($user)
                ->getJson('/api/questionnaire?status=published');

            $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
        });
    });

    describe('POST /api/questionnaire/{id}/publish', function () {
        it('publishes a questionnaire', function () {
            $user = createUser();
            $questionnaire = createQuestionnaireModel([
                'user_id' => $user->id,
                'status' => 'draft',
            ]);
            createQuestionModel(['questionnaire_id' => $questionnaire->id]);

            $response = $this->actingAs($user)
                ->postJson("/api/questionnaire/{$questionnaire->id}/publish");

            $response->assertStatus(200)
                ->assertJsonPath('message', 'Questionnaire published successfully.');

            $this->assertDatabaseHas('questionnaires', [
                'id' => $questionnaire->id,
                'status' => 'published',
            ]);
        });

        it('fails if questionnaire has no questions', function () {
            $user = createUser();
            $questionnaire = createQuestionnaireModel([
                'user_id' => $user->id,
                'status' => 'draft',
            ]);

            $response = $this->actingAs($user)
                ->postJson("/api/questionnaire/{$questionnaire->id}/publish");

            $response->assertStatus(422);
        });
    });
});
```

### 5.2 Event Sourcing 測試

```php
<?php
// tests/Feature/EventSourcing/QuestionnaireEventSourcingTest.php

declare(strict_types=1);

use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireCreated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnairePublished;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

describe('Questionnaire Event Sourcing', function () {
    it('stores events when saving aggregate', function () {
        $repository = app(\Liangjin0228\Questionnaire\Contracts\Domain\Repository\QuestionnaireRepositoryInterface::class);

        $questionnaire = createQuestionnaire(['title' => 'Event Sourcing Test']);
        $repository->save($questionnaire);

        $events = EloquentStoredEvent::query()
            ->where('aggregate_uuid', (string) $questionnaire->getId())
            ->get();

        expect($events)->toHaveCount(1)
            ->and($events->first()->event_class)->toBe(QuestionnaireCreated::class);
    });

    it('reconstitutes aggregate from events', function () {
        $repository = app(\Liangjin0228\Questionnaire\Contracts\Domain\Repository\QuestionnaireRepositoryInterface::class);

        // Create and save
        $original = createQuestionnaire(['title' => 'Original']);
        $original->addQuestion(createQuestion());
        $repository->save($original);

        // Fetch fresh
        $fetched = $repository->get($original->getId());

        expect((string) $fetched->getTitle())->toBe('Original')
            ->and($fetched->getQuestions())->toHaveCount(1);
    });

    it('maintains event order', function () {
        $repository = app(\Liangjin0228\Questionnaire\Contracts\Domain\Repository\QuestionnaireRepositoryInterface::class);

        $questionnaire = createQuestionnaire();
        $questionnaire->addQuestion(createQuestion());
        $questionnaire->addQuestion(createQuestion());
        $questionnaire->publish();
        $repository->save($questionnaire);

        $events = EloquentStoredEvent::query()
            ->where('aggregate_uuid', (string) $questionnaire->getId())
            ->orderBy('aggregate_version')
            ->get();

        expect($events)->toHaveCount(4)
            ->and($events->last()->event_class)->toBe(QuestionnairePublished::class);
    });
});
```

---

## 6. 架構測試

### 6.1 Domain 層架構測試

```php
<?php
// tests/Arch/DomainLayerTest.php

declare(strict_types=1);

arch('domain layer should not depend on laravel')
    ->expect('Liangjin0228\Questionnaire\Domain')
    ->not->toUse([
        'Illuminate',
        'Laravel',
    ]);

arch('domain layer can only use allowed dependencies')
    ->expect('Liangjin0228\Questionnaire\Domain')
    ->toOnlyUse([
        'Liangjin0228\Questionnaire\Domain',
        'Liangjin0228\Questionnaire\Contracts\Domain',
        'Ramsey\Uuid',
        'DateTimeImmutable',
        'DateTimeInterface',
        'JsonSerializable',
        'Stringable',
        'InvalidArgumentException',
        'RuntimeException',
        'DomainException',
    ]);

arch('aggregates should be final')
    ->expect('Liangjin0228\Questionnaire\Domain\*\Aggregate')
    ->toBeFinal();

arch('aggregates should extend AggregateRoot')
    ->expect('Liangjin0228\Questionnaire\Domain\*\Aggregate')
    ->toExtend(\Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateRoot::class);

arch('value objects should be final')
    ->expect('Liangjin0228\Questionnaire\Domain\*\ValueObject')
    ->toBeFinal();

arch('value objects should extend ValueObject base class')
    ->expect('Liangjin0228\Questionnaire\Domain\*\ValueObject')
    ->toExtend(\Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject::class);

arch('domain events should be final')
    ->expect('Liangjin0228\Questionnaire\Domain\*\Event')
    ->toBeFinal();

arch('domain events should extend DomainEvent')
    ->expect('Liangjin0228\Questionnaire\Domain\*\Event')
    ->toExtend(\Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent::class);

arch('enums should be in Enum namespace')
    ->expect('Liangjin0228\Questionnaire\Domain\*\Enum')
    ->toBeEnums();
```

### 6.2 Application 層架構測試

```php
<?php
// tests/Arch/ApplicationLayerTest.php

declare(strict_types=1);

arch('application layer should not depend on infrastructure')
    ->expect('Liangjin0228\Questionnaire\Application')
    ->not->toUse([
        'Liangjin0228\Questionnaire\Infrastructure',
    ]);

arch('commands should be readonly')
    ->expect('Liangjin0228\Questionnaire\Application\Command\*\*Command')
    ->toBeReadonly();

arch('commands should implement CommandInterface')
    ->expect('Liangjin0228\Questionnaire\Application\Command\*\*Command')
    ->toImplement(\Liangjin0228\Questionnaire\Contracts\Application\CommandInterface::class);

arch('command handlers should be final and readonly')
    ->expect('Liangjin0228\Questionnaire\Application\Command\*\*Handler')
    ->toBeFinal()
    ->toBeReadonly();

arch('queries should be readonly')
    ->expect('Liangjin0228\Questionnaire\Application\Query\*\*Query')
    ->toBeReadonly();

arch('query handlers should be final and readonly')
    ->expect('Liangjin0228\Questionnaire\Application\Query\*\*Handler')
    ->toBeFinal()
    ->toBeReadonly();

arch('DTOs should use spatie/laravel-data')
    ->expect('Liangjin0228\Questionnaire\Application\DTO')
    ->toExtend(\Spatie\LaravelData\Data::class);
```

### 6.3 Infrastructure 層架構測試

```php
<?php
// tests/Arch/InfrastructureLayerTest.php

declare(strict_types=1);

arch('controllers should be final')
    ->expect('Liangjin0228\Questionnaire\Infrastructure\Http\Controller')
    ->toBeFinal();

arch('controllers should extend base Controller')
    ->expect('Liangjin0228\Questionnaire\Infrastructure\Http\Controller')
    ->toExtend(\Illuminate\Routing\Controller::class);

arch('repositories should implement interfaces')
    ->expect('Liangjin0228\Questionnaire\Infrastructure\Persistence\Repository')
    ->toImplement([
        \Liangjin0228\Questionnaire\Contracts\Domain\Repository\QuestionnaireRepositoryInterface::class,
        \Liangjin0228\Questionnaire\Contracts\Domain\Repository\ResponseRepositoryInterface::class,
    ]);

arch('read models should extend Model')
    ->expect('Liangjin0228\Questionnaire\Infrastructure\Persistence\ReadModel')
    ->toExtend(\Illuminate\Database\Eloquent\Model::class);
```

---

## 7. 測試執行

### 7.1 命令列

```bash
# 執行所有測試
./vendor/bin/pest

# 執行特定測試
./vendor/bin/pest tests/Unit/Domain

# 執行帶覆蓋率
./vendor/bin/pest --coverage

# 執行架構測試
./vendor/bin/pest tests/Arch

# 執行並行測試（加速）
./vendor/bin/pest --parallel

# 監控模式（檔案變更自動執行）
./vendor/bin/pest --watch
```

### 7.2 CI/CD 配置

```yaml
# .github/workflows/tests.yml
name: Tests

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php: [8.2, 8.3]
        laravel: [10.*, 11.*, 12.*]

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: mbstring, dom, fileinfo
          coverage: xdebug

      - name: Install dependencies
        run: |
          composer require "laravel/framework:${{ matrix.laravel }}" --no-interaction --no-update
          composer update --prefer-stable --prefer-dist --no-interaction

      - name: Run PHPStan
        run: ./vendor/bin/phpstan analyse --level=9

      - name: Run tests
        run: ./vendor/bin/pest --coverage --min=90

      - name: Upload coverage
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage.xml
```

---

## 8. 覆蓋率報告

```bash
# 生成 HTML 覆蓋率報告
./vendor/bin/pest --coverage-html=coverage

# 設定最低覆蓋率要求
./vendor/bin/pest --coverage --min=90
```

---

這份測試策略確保了：
- **Domain 層** 100% 純單元測試，不依賴框架
- **Application 層** 使用 Mock 進行隔離測試
- **Infrastructure 層** 使用整合測試驗證
- **架構測試** 確保分層規則被遵守

---

**完成！**

以上是完整的重構計劃文檔。請仔細閱讀並確認後，可以開始按照 [08-migration-guide.md](./08-migration-guide.md) 的步驟進行重構。
