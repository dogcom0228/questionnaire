# Application 層（CQRS）設計

> **文件**: 03-application-layer.md  
> **上一篇**: [02-domain-layer.md](./02-domain-layer.md)  
> **下一篇**: [04-infrastructure-layer.md](./04-infrastructure-layer.md)

---

## 1. CQRS 架構概述

### 1.1 核心概念

```
┌─────────────────────────────────────────────────────────────┐
│                         CQRS                                 │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│   ┌─────────────────┐           ┌─────────────────┐        │
│   │    Command      │           │     Query       │        │
│   │    (Write)      │           │     (Read)      │        │
│   └────────┬────────┘           └────────┬────────┘        │
│            │                             │                  │
│            ▼                             ▼                  │
│   ┌─────────────────┐           ┌─────────────────┐        │
│   │  Command Bus    │           │   Query Bus     │        │
│   └────────┬────────┘           └────────┬────────┘        │
│            │                             │                  │
│            ▼                             ▼                  │
│   ┌─────────────────┐           ┌─────────────────┐        │
│   │ Command Handler │           │  Query Handler  │        │
│   └────────┬────────┘           └────────┬────────┘        │
│            │                             │                  │
│            ▼                             ▼                  │
│   ┌─────────────────┐           ┌─────────────────┐        │
│   │ Domain Model    │           │  Read Model     │        │
│   │ (Event Store)   │           │  (Projections)  │        │
│   └─────────────────┘           └─────────────────┘        │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 1.2 職責分離

| 類型 | 職責 | 返回值 |
|------|------|--------|
| **Command** | 修改系統狀態（寫入） | void 或 ID |
| **Query** | 查詢系統狀態（讀取） | DTO |

### 1.3 Bus 設計

```php
// Command Bus - 一個命令對應一個處理器
interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): mixed;
}

// Query Bus - 一個查詢對應一個處理器
interface QueryBusInterface
{
    public function dispatch(QueryInterface $query): mixed;
}

// Event Bus - 一個事件可對應多個處理器
interface EventBusInterface
{
    public function dispatch(DomainEvent $event): void;
}
```

---

## 2. Command 設計

### 2.1 Command Interface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application;

/**
 * 命令介面
 * 
 * @template TResult
 */
interface CommandInterface
{
    // Marker interface
}
```

### 2.2 Command Handler Interface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application;

interface CommandHandlerInterface
{
    /**
     * 處理命令
     */
    public function handle(CommandInterface $command): mixed;
}
```

### 2.3 CreateQuestionnaire Command

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Questionnaire\CreateQuestionnaire;

use Liangjin0228\Questionnaire\Application\DTO\Input\QuestionnaireInput;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;

/**
 * 創建問卷命令
 * 
 * @implements CommandInterface<string>
 */
final readonly class CreateQuestionnaireCommand implements CommandInterface
{
    public function __construct(
        public QuestionnaireInput $input,
        public ?string $userId = null
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Questionnaire\CreateQuestionnaire;

use Liangjin0228\Questionnaire\Contracts\Application\Bus\EventBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\CommandHandlerInterface;
use Liangjin0228\Questionnaire\Contracts\Domain\Repository\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\QuestionType;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionContent;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionOptions;
use Liangjin0228\Questionnaire\Domain\User\ValueObject\UserId;

final readonly class CreateQuestionnaireHandler implements CommandHandlerInterface
{
    public function __construct(
        private QuestionnaireRepositoryInterface $repository,
        private EventBusInterface $eventBus
    ) {}

    public function handle(CreateQuestionnaireCommand $command): string
    {
        $id = QuestionnaireId::generate();

        // 建立問卷聚合
        $questionnaire = Questionnaire::create(
            id: $id,
            title: QuestionnaireTitle::fromString($command->input->title),
            description: $command->input->description,
            ownerId: $command->userId ? UserId::fromString($command->userId) : null,
            settings: $command->input->settings 
                ? QuestionnaireSettings::fromArray($command->input->settings)
                : null
        );

        // 添加問題
        foreach ($command->input->questions as $questionInput) {
            $question = $this->createQuestion($questionInput);
            $questionnaire->addQuestion($question);
        }

        // 保存聚合（會保存事件到 Event Store）
        $this->repository->save($questionnaire);

        // 發布領域事件
        foreach ($questionnaire->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }

        return (string) $id;
    }

    private function createQuestion(QuestionInput $input): Question
    {
        return Question::create(
            id: QuestionId::generate(),
            type: QuestionType::from($input->type),
            content: QuestionContent::fromString($input->content),
            order: $input->order,
            isRequired: $input->isRequired ?? false,
            options: $input->options 
                ? QuestionOptions::fromArray($input->options) 
                : null,
            settings: $input->settings ?? []
        );
    }
}
```

### 2.4 UpdateQuestionnaire Command

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Questionnaire\UpdateQuestionnaire;

use Liangjin0228\Questionnaire\Application\DTO\Input\QuestionnaireInput;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;

final readonly class UpdateQuestionnaireCommand implements CommandInterface
{
    public function __construct(
        public string $id,
        public QuestionnaireInput $input
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Questionnaire\UpdateQuestionnaire;

use Liangjin0228\Questionnaire\Contracts\Application\Bus\EventBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\CommandHandlerInterface;
use Liangjin0228\Questionnaire\Contracts\Domain\Repository\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;

final readonly class UpdateQuestionnaireHandler implements CommandHandlerInterface
{
    public function __construct(
        private QuestionnaireRepositoryInterface $repository,
        private EventBusInterface $eventBus
    ) {}

    public function handle(UpdateQuestionnaireCommand $command): void
    {
        $id = QuestionnaireId::fromString($command->id);
        
        // 從 Event Store 載入聚合
        $questionnaire = $this->repository->get($id);

        // 執行領域邏輯
        $questionnaire->update(
            title: QuestionnaireTitle::fromString($command->input->title),
            description: $command->input->description,
            settings: $command->input->settings 
                ? QuestionnaireSettings::fromArray($command->input->settings)
                : null
        );

        // 保存變更
        $this->repository->save($questionnaire);

        // 發布事件
        foreach ($questionnaire->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
```

### 2.5 PublishQuestionnaire Command

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Questionnaire\PublishQuestionnaire;

use DateTimeImmutable;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;

final readonly class PublishQuestionnaireCommand implements CommandInterface
{
    public function __construct(
        public string $id,
        public ?DateTimeImmutable $startsAt = null,
        public ?DateTimeImmutable $endsAt = null
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Questionnaire\PublishQuestionnaire;

use Liangjin0228\Questionnaire\Contracts\Application\Bus\EventBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\CommandHandlerInterface;
use Liangjin0228\Questionnaire\Contracts\Domain\Repository\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;

final readonly class PublishQuestionnaireHandler implements CommandHandlerInterface
{
    public function __construct(
        private QuestionnaireRepositoryInterface $repository,
        private EventBusInterface $eventBus
    ) {}

    public function handle(PublishQuestionnaireCommand $command): void
    {
        $id = QuestionnaireId::fromString($command->id);
        $questionnaire = $this->repository->get($id);

        $dateRange = ($command->startsAt !== null || $command->endsAt !== null)
            ? DateRange::create($command->startsAt, $command->endsAt)
            : null;

        $questionnaire->publish($dateRange);

        $this->repository->save($questionnaire);

        foreach ($questionnaire->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
```

### 2.6 SubmitResponse Command

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Response\SubmitResponse;

use Liangjin0228\Questionnaire\Application\DTO\Input\SubmitResponseInput;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;

final readonly class SubmitResponseCommand implements CommandInterface
{
    public function __construct(
        public string $questionnaireId,
        public SubmitResponseInput $input,
        public ?string $userId = null,
        public ?string $sessionId = null,
        public ?string $ipAddress = null
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Response\SubmitResponse;

use Liangjin0228\Questionnaire\Contracts\Application\Bus\EventBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\CommandHandlerInterface;
use Liangjin0228\Questionnaire\Contracts\Domain\Repository\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Contracts\Domain\Repository\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Specification\QuestionnaireIsActiveSpec;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Response\Aggregate\Response;
use Liangjin0228\Questionnaire\Domain\Response\Guard\GuardFactory;
use Liangjin0228\Questionnaire\Domain\Response\Service\ResponseValidationService;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\RespondentInfo;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\SubmissionMetadata;
use Liangjin0228\Questionnaire\Domain\Response\Exception\DuplicateSubmissionException;
use Liangjin0228\Questionnaire\Domain\Response\Exception\ValidationException;

final readonly class SubmitResponseHandler implements CommandHandlerInterface
{
    public function __construct(
        private QuestionnaireRepositoryInterface $questionnaireRepository,
        private ResponseRepositoryInterface $responseRepository,
        private ResponseValidationService $validationService,
        private GuardFactory $guardFactory,
        private EventBusInterface $eventBus
    ) {}

    public function handle(SubmitResponseCommand $command): string
    {
        $questionnaireId = QuestionnaireId::fromString($command->questionnaireId);
        $questionnaire = $this->questionnaireRepository->get($questionnaireId);

        // 檢查問卷是否接受回應
        $activeSpec = new QuestionnaireIsActiveSpec();
        if (!$activeSpec->isSatisfiedBy($questionnaire)) {
            throw new \DomainException($activeSpec->getUnsatisfiedReason($questionnaire));
        }

        // 檢查重複提交
        $guard = $this->guardFactory->create($questionnaire->getSettings()->getDuplicateSubmissionStrategy());
        $metadata = SubmissionMetadata::create(
            userId: $command->userId,
            sessionId: $command->sessionId,
            ipAddress: $command->ipAddress
        );

        if (!$guard->canSubmit($questionnaire, $metadata, $this->responseRepository)) {
            throw new DuplicateSubmissionException('You have already submitted a response to this questionnaire.');
        }

        // 驗證答案
        $errors = $this->validationService->validate($command->input->answers, $questionnaire);
        if (!empty($errors)) {
            throw ValidationException::withErrors($errors);
        }

        // 創建回應聚合
        $responseId = ResponseId::generate();
        $response = Response::submit(
            id: $responseId,
            questionnaireId: $questionnaireId,
            answers: $this->mapAnswers($command->input->answers),
            respondentInfo: RespondentInfo::create(
                userId: $command->userId,
                email: $command->input->email,
                name: $command->input->name
            ),
            metadata: $metadata
        );

        // 保存回應
        $this->responseRepository->save($response);

        // 發布事件
        foreach ($response->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }

        return (string) $responseId;
    }

    private function mapAnswers(array $answers): array
    {
        // 將輸入的答案轉換為 Answer 實體
        // ...
        return $answers;
    }
}
```

---

## 3. Query 設計

### 3.1 Query Interface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application;

/**
 * 查詢介面
 * 
 * @template TResult
 */
interface QueryInterface
{
    // Marker interface
}
```

### 3.2 Query Handler Interface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application;

interface QueryHandlerInterface
{
    public function handle(QueryInterface $query): mixed;
}
```

### 3.3 ListQuestionnaires Query

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Query\Questionnaire\ListQuestionnaires;

use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

/**
 * @implements QueryInterface<PaginatedResult<QuestionnaireOutput>>
 */
final readonly class ListQuestionnairesQuery implements QueryInterface
{
    public function __construct(
        public ?string $userId = null,
        public ?string $status = null,
        public ?string $search = null,
        public int $page = 1,
        public int $perPage = 15,
        public string $sortBy = 'created_at',
        public string $sortDirection = 'desc'
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Query\Questionnaire\ListQuestionnaires;

use Liangjin0228\Questionnaire\Application\DTO\Output\PaginatedResult;
use Liangjin0228\Questionnaire\Application\DTO\Output\QuestionnaireOutput;
use Liangjin0228\Questionnaire\Application\Mapper\QuestionnaireMapper;
use Liangjin0228\Questionnaire\Contracts\Application\QueryHandlerInterface;
use Liangjin0228\Questionnaire\Infrastructure\Persistence\ReadModel\QuestionnaireModel;

final readonly class ListQuestionnairesHandler implements QueryHandlerInterface
{
    public function __construct(
        private QuestionnaireMapper $mapper
    ) {}

    public function handle(ListQuestionnairesQuery $query): PaginatedResult
    {
        // 直接查詢讀取模型（投影表）
        $eloquentQuery = QuestionnaireModel::query();

        // 應用過濾條件
        if ($query->userId !== null) {
            $eloquentQuery->where('user_id', $query->userId);
        }

        if ($query->status !== null) {
            $eloquentQuery->where('status', $query->status);
        }

        if ($query->search !== null) {
            $eloquentQuery->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query->search}%")
                  ->orWhere('description', 'like', "%{$query->search}%");
            });
        }

        // 排序
        $eloquentQuery->orderBy($query->sortBy, $query->sortDirection);

        // 分頁
        $paginator = $eloquentQuery->paginate($query->perPage, ['*'], 'page', $query->page);

        // 轉換為 DTO
        $items = collect($paginator->items())
            ->map(fn ($model) => $this->mapper->toOutput($model))
            ->all();

        return new PaginatedResult(
            items: $items,
            total: $paginator->total(),
            page: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            lastPage: $paginator->lastPage()
        );
    }
}
```

### 3.4 GetQuestionnaire Query

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Query\Questionnaire\GetQuestionnaire;

use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

final readonly class GetQuestionnaireQuery implements QueryInterface
{
    public function __construct(
        public string $id,
        public bool $includeQuestions = true
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Query\Questionnaire\GetQuestionnaire;

use Liangjin0228\Questionnaire\Application\DTO\Output\QuestionnaireOutput;
use Liangjin0228\Questionnaire\Application\Mapper\QuestionnaireMapper;
use Liangjin0228\Questionnaire\Contracts\Application\QueryHandlerInterface;
use Liangjin0228\Questionnaire\Infrastructure\Persistence\ReadModel\QuestionnaireModel;

final readonly class GetQuestionnaireHandler implements QueryHandlerInterface
{
    public function __construct(
        private QuestionnaireMapper $mapper
    ) {}

    public function handle(GetQuestionnaireQuery $query): ?QuestionnaireOutput
    {
        $eloquentQuery = QuestionnaireModel::query();

        if ($query->includeQuestions) {
            $eloquentQuery->with('questions');
        }

        $model = $eloquentQuery->find($query->id);

        if ($model === null) {
            return null;
        }

        return $this->mapper->toOutput($model);
    }
}
```

### 3.5 GetStatistics Query

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Query\Response\GetStatistics;

use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

final readonly class GetStatisticsQuery implements QueryInterface
{
    public function __construct(
        public string $questionnaireId
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Query\Response\GetStatistics;

use Liangjin0228\Questionnaire\Application\DTO\Output\StatisticsOutput;
use Liangjin0228\Questionnaire\Contracts\Application\QueryHandlerInterface;
use Liangjin0228\Questionnaire\Domain\Response\Service\StatisticsCalculationService;
use Liangjin0228\Questionnaire\Infrastructure\Persistence\ReadModel\QuestionnaireModel;
use Liangjin0228\Questionnaire\Infrastructure\Persistence\ReadModel\ResponseModel;

final readonly class GetStatisticsHandler implements QueryHandlerInterface
{
    public function __construct(
        private StatisticsCalculationService $statisticsService
    ) {}

    public function handle(GetStatisticsQuery $query): StatisticsOutput
    {
        $questionnaire = QuestionnaireModel::with('questions')
            ->findOrFail($query->questionnaireId);

        $responses = ResponseModel::with('answers')
            ->where('questionnaire_id', $query->questionnaireId)
            ->get();

        return $this->statisticsService->calculate($questionnaire, $responses);
    }
}
```

---

## 4. DTO 設計

### 4.1 Input DTOs

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Input;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;

final class QuestionnaireInput extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public readonly string $title,

        public readonly ?string $description = null,

        /** @var array<QuestionInput> */
        public readonly array $questions = [],

        public readonly ?array $settings = null,

        public readonly ?string $startsAt = null,

        public readonly ?string $endsAt = null,
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Input;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;

final class QuestionInput extends Data
{
    public function __construct(
        #[Required]
        public readonly string $type,

        #[Required]
        public readonly string $content,

        public readonly int $order = 0,

        public readonly bool $isRequired = false,

        public readonly ?array $options = null,

        public readonly ?array $settings = null,
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Input;

use Spatie\LaravelData\Data;

final class SubmitResponseInput extends Data
{
    public function __construct(
        /** @var array<string, mixed> question_id => answer_value */
        public readonly array $answers,

        public readonly ?string $email = null,

        public readonly ?string $name = null,
    ) {}
}
```

### 4.2 Output DTOs

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Output;

use DateTimeImmutable;
use Spatie\LaravelData\Data;

final class QuestionnaireOutput extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $slug,
        public readonly string $status,
        public readonly bool $isActive,
        public readonly bool $isAcceptingResponses,
        public readonly ?string $userId,
        /** @var array<QuestionOutput> */
        public readonly array $questions,
        public readonly ?array $settings,
        public readonly ?string $startsAt,
        public readonly ?string $endsAt,
        public readonly ?string $publishedAt,
        public readonly ?string $closedAt,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly int $responseCount,
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Output;

use Spatie\LaravelData\Data;

final class QuestionOutput extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $questionnaireId,
        public readonly string $type,
        public readonly string $content,
        public readonly int $order,
        public readonly bool $isRequired,
        public readonly ?array $options,
        public readonly ?array $settings,
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Output;

use Spatie\LaravelData\Data;

/**
 * @template T
 */
final class PaginatedResult extends Data
{
    public function __construct(
        /** @var array<T> */
        public readonly array $items,
        public readonly int $total,
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $lastPage,
    ) {}

    public function hasMorePages(): bool
    {
        return $this->page < $this->lastPage;
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Output;

use Spatie\LaravelData\Data;

final class StatisticsOutput extends Data
{
    public function __construct(
        public readonly string $questionnaireId,
        public readonly int $totalResponses,
        public readonly ?string $firstResponseAt,
        public readonly ?string $lastResponseAt,
        /** @var array<QuestionStatisticsOutput> */
        public readonly array $questionStatistics,
        public readonly float $completionRate,
        public readonly float $averageCompletionTime,
    ) {}
}
```

---

## 5. Mapper 設計

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Mapper;

use Liangjin0228\Questionnaire\Application\DTO\Output\QuestionnaireOutput;
use Liangjin0228\Questionnaire\Application\DTO\Output\QuestionOutput;
use Liangjin0228\Questionnaire\Infrastructure\Persistence\ReadModel\QuestionnaireModel;

final class QuestionnaireMapper
{
    public function __construct(
        private QuestionMapper $questionMapper
    ) {}

    public function toOutput(QuestionnaireModel $model): QuestionnaireOutput
    {
        $questions = $model->relationLoaded('questions')
            ? collect($model->questions)->map(fn ($q) => $this->questionMapper->toOutput($q))->all()
            : [];

        $now = now();
        $isActive = $model->status === 'published'
            && ($model->starts_at === null || $model->starts_at <= $now)
            && ($model->ends_at === null || $model->ends_at >= $now);

        return new QuestionnaireOutput(
            id: $model->id,
            title: $model->title,
            description: $model->description,
            slug: $model->slug,
            status: $model->status,
            isActive: $isActive,
            isAcceptingResponses: $isActive,
            userId: $model->user_id,
            questions: $questions,
            settings: $model->settings,
            startsAt: $model->starts_at?->toISOString(),
            endsAt: $model->ends_at?->toISOString(),
            publishedAt: $model->published_at?->toISOString(),
            closedAt: $model->closed_at?->toISOString(),
            createdAt: $model->created_at->toISOString(),
            updatedAt: $model->updated_at->toISOString(),
            responseCount: $model->responses_count ?? 0
        );
    }
}
```

---

## 6. Bus 實作

### 6.1 Command Bus

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Bus;

use Illuminate\Contracts\Container\Container;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\CommandBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;

final class IlluminateCommandBus implements CommandBusInterface
{
    /** @var array<class-string<CommandInterface>, class-string> */
    private array $handlers = [];

    public function __construct(
        private readonly Container $container
    ) {}

    public function register(string $commandClass, string $handlerClass): void
    {
        $this->handlers[$commandClass] = $handlerClass;
    }

    public function dispatch(CommandInterface $command): mixed
    {
        $handlerClass = $this->resolveHandler($command);
        $handler = $this->container->make($handlerClass);

        return $handler->handle($command);
    }

    private function resolveHandler(CommandInterface $command): string
    {
        $commandClass = get_class($command);

        // 先檢查已註冊的處理器
        if (isset($this->handlers[$commandClass])) {
            return $this->handlers[$commandClass];
        }

        // 約定優於配置：將 Command 替換為 Handler
        $handlerClass = preg_replace('/Command$/', 'Handler', $commandClass);

        if ($handlerClass && class_exists($handlerClass)) {
            return $handlerClass;
        }

        throw new \RuntimeException(
            sprintf('No handler found for command: %s', $commandClass)
        );
    }
}
```

### 6.2 Query Bus

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Bus;

use Illuminate\Contracts\Container\Container;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\QueryBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

final class IlluminateQueryBus implements QueryBusInterface
{
    /** @var array<class-string<QueryInterface>, class-string> */
    private array $handlers = [];

    public function __construct(
        private readonly Container $container
    ) {}

    public function register(string $queryClass, string $handlerClass): void
    {
        $this->handlers[$queryClass] = $handlerClass;
    }

    public function dispatch(QueryInterface $query): mixed
    {
        $handlerClass = $this->resolveHandler($query);
        $handler = $this->container->make($handlerClass);

        return $handler->handle($query);
    }

    private function resolveHandler(QueryInterface $query): string
    {
        $queryClass = get_class($query);

        if (isset($this->handlers[$queryClass])) {
            return $this->handlers[$queryClass];
        }

        // 約定：Query -> Handler
        $handlerClass = preg_replace('/Query$/', 'Handler', $queryClass);

        if ($handlerClass && class_exists($handlerClass)) {
            return $handlerClass;
        }

        throw new \RuntimeException(
            sprintf('No handler found for query: %s', $queryClass)
        );
    }
}
```

### 6.3 Event Bus

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Bus;

use Illuminate\Contracts\Events\Dispatcher;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\EventBusInterface;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

final class IlluminateEventBus implements EventBusInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher
    ) {}

    public function dispatch(DomainEvent $event): void
    {
        $this->dispatcher->dispatch($event);
    }

    public function dispatchMany(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }
}
```

---

## 7. Projector 設計

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Projector;

use Liangjin0228\Questionnaire\Contracts\Infrastructure\ProjectorInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionAdded;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireClosed;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireCreated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnairePublished;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireUpdated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionRemoved;
use Liangjin0228\Questionnaire\Infrastructure\Persistence\ReadModel\QuestionModel;
use Liangjin0228\Questionnaire\Infrastructure\Persistence\ReadModel\QuestionnaireModel;

final class QuestionnaireProjector implements ProjectorInterface
{
    public function onQuestionnaireCreated(QuestionnaireCreated $event): void
    {
        QuestionnaireModel::create([
            'id' => (string) $event->getAggregateId(),
            'title' => (string) $event->title,
            'description' => $event->description,
            'slug' => (string) $event->slug,
            'status' => $event->status->value,
            'user_id' => $event->ownerId ? (string) $event->ownerId : null,
            'settings' => $event->settings->toArray(),
            'created_at' => $event->getOccurredAt(),
            'updated_at' => $event->getOccurredAt(),
        ]);
    }

    public function onQuestionnaireUpdated(QuestionnaireUpdated $event): void
    {
        QuestionnaireModel::where('id', (string) $event->getAggregateId())
            ->update([
                'title' => (string) $event->title,
                'description' => $event->description,
                'settings' => $event->settings->toArray(),
                'updated_at' => $event->getOccurredAt(),
            ]);
    }

    public function onQuestionnairePublished(QuestionnairePublished $event): void
    {
        $update = [
            'status' => 'published',
            'published_at' => $event->publishedAt,
            'updated_at' => $event->getOccurredAt(),
        ];

        if ($event->dateRange !== null) {
            $update['starts_at'] = $event->dateRange->getStartsAt();
            $update['ends_at'] = $event->dateRange->getEndsAt();
        }

        QuestionnaireModel::where('id', (string) $event->getAggregateId())
            ->update($update);
    }

    public function onQuestionnaireClosed(QuestionnaireClosed $event): void
    {
        QuestionnaireModel::where('id', (string) $event->getAggregateId())
            ->update([
                'status' => 'closed',
                'closed_at' => $event->closedAt,
                'updated_at' => $event->getOccurredAt(),
            ]);
    }

    public function onQuestionAdded(QuestionAdded $event): void
    {
        $question = $event->question;

        QuestionModel::create([
            'id' => (string) $question->getId(),
            'questionnaire_id' => (string) $event->getAggregateId(),
            'type' => $question->getType()->value,
            'content' => (string) $question->getContent(),
            'order' => $question->getOrder(),
            'is_required' => $question->isRequired(),
            'options' => $question->getOptions()?->toArray(),
            'settings' => $question->getSettings(),
            'created_at' => $event->getOccurredAt(),
            'updated_at' => $event->getOccurredAt(),
        ]);
    }

    public function onQuestionRemoved(QuestionRemoved $event): void
    {
        QuestionModel::where('id', (string) $event->questionId)->delete();
    }

    public function getSubscribedEvents(): array
    {
        return [
            QuestionnaireCreated::class => 'onQuestionnaireCreated',
            QuestionnaireUpdated::class => 'onQuestionnaireUpdated',
            QuestionnairePublished::class => 'onQuestionnairePublished',
            QuestionnaireClosed::class => 'onQuestionnaireClosed',
            QuestionAdded::class => 'onQuestionAdded',
            QuestionRemoved::class => 'onQuestionRemoved',
        ];
    }
}
```

---

**下一篇**: [04-infrastructure-layer.md](./04-infrastructure-layer.md) - Infrastructure 層重構計劃
