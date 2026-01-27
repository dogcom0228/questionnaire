# Event Sourcing 實作計劃

> **文件**: 06-event-sourcing.md  
> **上一篇**: [05-contracts.md](./05-contracts.md)  
> **下一篇**: [07-dependencies.md](./07-dependencies.md)

---

## 1. Event Sourcing 概述

### 1.1 核心概念

Event Sourcing 是一種持久化模式，將應用程式狀態的所有變更儲存為一系列事件，而非直接儲存當前狀態。

```
┌─────────────────────────────────────────────────────────────┐
│                    傳統 CRUD vs Event Sourcing               │
├──────────────────────────────┬──────────────────────────────┤
│         傳統 CRUD            │       Event Sourcing         │
├──────────────────────────────┼──────────────────────────────┤
│ 儲存當前狀態                 │ 儲存所有事件歷史             │
│ 覆蓋舊數據                   │ 只追加新事件                 │
│ 無法追溯歷史                 │ 完整審計軌跡                 │
│ 簡單但有限                   │ 複雜但強大                   │
└──────────────────────────────┴──────────────────────────────┘
```

### 1.2 Event Sourcing 流程

```
┌─────────────────────────────────────────────────────────────┐
│                     寫入流程（Command）                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. 接收 Command                                            │
│           │                                                 │
│           ▼                                                 │
│  2. 從 Event Store 載入事件                                 │
│           │                                                 │
│           ▼                                                 │
│  3. 重建聚合狀態                                            │
│           │                                                 │
│           ▼                                                 │
│  4. 執行領域邏輯，產生新事件                                 │
│           │                                                 │
│           ▼                                                 │
│  5. 追加事件到 Event Store                                  │
│           │                                                 │
│           ▼                                                 │
│  6. 發布事件到 Event Bus                                    │
│           │                                                 │
│           ▼                                                 │
│  7. Projector 更新 Read Model                               │
│                                                             │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                     讀取流程（Query）                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. 接收 Query                                              │
│           │                                                 │
│           ▼                                                 │
│  2. 直接查詢 Read Model（投影表）                            │
│           │                                                 │
│           ▼                                                 │
│  3. 返回結果                                                │
│                                                             │
│  ※ 不需要重建聚合，查詢速度快                               │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Event Store 設計

### 2.1 資料表結構

```php
<?php
// database/migrations/2026_01_27_000001_create_stored_events_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stored_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('aggregate_id')->index();
            $table->string('aggregate_type', 100)->index();
            $table->string('event_type', 255)->index();
            $table->json('payload');
            $table->json('metadata')->nullable();
            $table->unsignedInteger('version');
            $table->timestamp('created_at')->useCurrent();

            // 確保同一聚合的版本唯一
            $table->unique(['aggregate_id', 'version']);
            
            // 用於重建投影的索引
            $table->index(['created_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_events');
    }
};
```

### 2.2 快照表結構

```php
<?php
// database/migrations/2026_01_27_000002_create_snapshots_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snapshots', function (Blueprint $table) {
            $table->uuid('aggregate_id')->primary();
            $table->string('aggregate_type', 100);
            $table->json('state');
            $table->unsignedInteger('version');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
```

### 2.3 Event Store 實作

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\EventStore;

use Illuminate\Database\Connection;
use Liangjin0228\Questionnaire\Contracts\Infrastructure\EventStoreInterface;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;
use Liangjin0228\Questionnaire\Infrastructure\EventStore\Exception\ConcurrencyException;

final class EloquentEventStore implements EventStoreInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly EventSerializer $serializer,
        private readonly string $table = 'stored_events'
    ) {}

    public function append(string $aggregateId, array $events, int $expectedVersion): void
    {
        if (empty($events)) {
            return;
        }

        $this->connection->transaction(function () use ($aggregateId, $events, $expectedVersion) {
            // 樂觀鎖定：檢查當前版本
            $currentVersion = $this->getCurrentVersion($aggregateId);
            
            if ($currentVersion !== $expectedVersion) {
                throw new ConcurrencyException(
                    sprintf(
                        'Concurrency conflict for aggregate %s. Expected version %d, found %d.',
                        $aggregateId,
                        $expectedVersion,
                        $currentVersion
                    )
                );
            }

            $version = $expectedVersion;
            $records = [];

            foreach ($events as $event) {
                $version++;
                
                $records[] = [
                    'id' => $event->getEventId()->toString(),
                    'aggregate_id' => $aggregateId,
                    'aggregate_type' => $this->getAggregateType($event),
                    'event_type' => get_class($event),
                    'payload' => $this->serializer->serialize($event),
                    'metadata' => json_encode([
                        'occurred_at' => $event->getOccurredAt()->format('c'),
                        'correlation_id' => $this->getCorrelationId(),
                        'causation_id' => $this->getCausationId(),
                    ]),
                    'version' => $version,
                    'created_at' => now(),
                ];
            }

            $this->connection->table($this->table)->insert($records);
        });
    }

    public function getEventsForAggregate(string $aggregateId, int $fromVersion = 0): array
    {
        $rows = $this->connection->table($this->table)
            ->where('aggregate_id', $aggregateId)
            ->where('version', '>', $fromVersion)
            ->orderBy('version')
            ->get();

        return $rows->map(fn ($row) => $this->serializer->deserialize(
            $row->event_type,
            $row->payload,
            $aggregateId
        ))->all();
    }

    public function hasEventsForAggregate(string $aggregateId): bool
    {
        return $this->connection->table($this->table)
            ->where('aggregate_id', $aggregateId)
            ->exists();
    }

    public function getAllEvents(?string $eventType = null, int $limit = 1000): array
    {
        $query = $this->connection->table($this->table)
            ->orderBy('created_at')
            ->orderBy('version')
            ->limit($limit);

        if ($eventType !== null) {
            $query->where('event_type', $eventType);
        }

        return $query->get()
            ->map(fn ($row) => $this->serializer->deserialize(
                $row->event_type,
                $row->payload,
                $row->aggregate_id
            ))
            ->all();
    }

    public function getEventsFromPosition(int $position, int $limit = 1000): array
    {
        return $this->connection->table($this->table)
            ->orderBy('created_at')
            ->orderBy('version')
            ->offset($position)
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->serializer->deserialize(
                $row->event_type,
                $row->payload,
                $row->aggregate_id
            ))
            ->all();
    }

    private function getCurrentVersion(string $aggregateId): int
    {
        return (int) $this->connection->table($this->table)
            ->where('aggregate_id', $aggregateId)
            ->max('version') ?? 0;
    }

    private function getAggregateType(DomainEvent $event): string
    {
        $eventClass = get_class($event);
        
        if (str_contains($eventClass, 'Questionnaire\\Event')) {
            return 'Questionnaire';
        }
        
        if (str_contains($eventClass, 'Response\\Event')) {
            return 'Response';
        }

        return 'Unknown';
    }

    private function getCorrelationId(): ?string
    {
        // 可從請求上下文獲取
        return request()?->header('X-Correlation-ID');
    }

    private function getCausationId(): ?string
    {
        // 可從請求上下文獲取
        return request()?->header('X-Causation-ID');
    }
}
```

### 2.4 Snapshot Store 實作

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\EventStore\Snapshot;

use DateTimeImmutable;
use Illuminate\Database\Connection;
use Liangjin0228\Questionnaire\Contracts\Domain\AggregateRootInterface;
use Liangjin0228\Questionnaire\Contracts\Infrastructure\Snapshot;
use Liangjin0228\Questionnaire\Contracts\Infrastructure\SnapshotStoreInterface;

final class EloquentSnapshotStore implements SnapshotStoreInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SnapshotSerializer $serializer,
        private readonly string $table = 'snapshots'
    ) {}

    public function save(string $aggregateId, AggregateRootInterface $aggregate, int $version): void
    {
        $this->connection->table($this->table)->updateOrInsert(
            ['aggregate_id' => $aggregateId],
            [
                'aggregate_type' => get_class($aggregate),
                'state' => $this->serializer->serialize($aggregate),
                'version' => $version,
                'created_at' => now(),
            ]
        );
    }

    public function get(string $aggregateId): ?Snapshot
    {
        $row = $this->connection->table($this->table)
            ->where('aggregate_id', $aggregateId)
            ->first();

        if ($row === null) {
            return null;
        }

        $aggregate = $this->serializer->deserialize($row->aggregate_type, $row->state);

        return new Snapshot(
            aggregateId: $row->aggregate_id,
            aggregate: $aggregate,
            version: $row->version,
            createdAt: new DateTimeImmutable($row->created_at)
        );
    }

    public function delete(string $aggregateId): void
    {
        $this->connection->table($this->table)
            ->where('aggregate_id', $aggregateId)
            ->delete();
    }

    public function exists(string $aggregateId): bool
    {
        return $this->connection->table($this->table)
            ->where('aggregate_id', $aggregateId)
            ->exists();
    }
}
```

---

## 3. 領域事件設計

### 3.1 Questionnaire Events

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Event;

use DateTimeImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateId;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

final class QuestionAdded extends DomainEvent
{
    public function __construct(
        QuestionnaireId $aggregateId,
        public readonly Question $question,
        ?DateTimeImmutable $occurredAt = null
    ) {
        parent::__construct($aggregateId, $occurredAt);
    }

    public function toPayload(): array
    {
        return [
            'question' => [
                'id' => (string) $this->question->getId(),
                'type' => $this->question->getType()->value,
                'content' => (string) $this->question->getContent(),
                'order' => $this->question->getOrder(),
                'is_required' => $this->question->isRequired(),
                'options' => $this->question->getOptions()?->toArray(),
                'settings' => $this->question->getSettings(),
            ],
        ];
    }

    public static function fromPayload(array $payload, AggregateId $aggregateId): static
    {
        $questionData = $payload['question'];
        
        $question = Question::reconstitute(
            id: QuestionId::fromString($questionData['id']),
            type: QuestionType::from($questionData['type']),
            content: QuestionContent::fromString($questionData['content']),
            order: $questionData['order'],
            isRequired: $questionData['is_required'],
            options: isset($questionData['options']) 
                ? QuestionOptions::fromArray($questionData['options']) 
                : null,
            settings: $questionData['settings'] ?? []
        );

        return new self(aggregateId: $aggregateId, question: $question);
    }
}
```

### 3.2 Response Events

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Event;

use DateTimeImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Response\Entity\Answer;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\RespondentInfo;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\SubmissionMetadata;
use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateId;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

final class ResponseSubmitted extends DomainEvent
{
    public function __construct(
        ResponseId $aggregateId,
        public readonly QuestionnaireId $questionnaireId,
        /** @var array<Answer> */
        public readonly array $answers,
        public readonly RespondentInfo $respondentInfo,
        public readonly SubmissionMetadata $metadata,
        public readonly DateTimeImmutable $submittedAt,
        ?DateTimeImmutable $occurredAt = null
    ) {
        parent::__construct($aggregateId, $occurredAt);
    }

    public function toPayload(): array
    {
        return [
            'questionnaire_id' => (string) $this->questionnaireId,
            'answers' => array_map(fn (Answer $a) => [
                'id' => (string) $a->getId(),
                'question_id' => (string) $a->getQuestionId(),
                'value' => $a->getValue()->toArray(),
            ], $this->answers),
            'respondent_info' => $this->respondentInfo->toArray(),
            'metadata' => $this->metadata->toArray(),
            'submitted_at' => $this->submittedAt->format('c'),
        ];
    }

    public static function fromPayload(array $payload, AggregateId $aggregateId): static
    {
        $answers = array_map(fn (array $a) => Answer::reconstitute(
            id: AnswerId::fromString($a['id']),
            questionId: QuestionId::fromString($a['question_id']),
            value: AnswerValue::fromArray($a['value'])
        ), $payload['answers']);

        return new self(
            aggregateId: $aggregateId,
            questionnaireId: QuestionnaireId::fromString($payload['questionnaire_id']),
            answers: $answers,
            respondentInfo: RespondentInfo::fromArray($payload['respondent_info']),
            metadata: SubmissionMetadata::fromArray($payload['metadata']),
            submittedAt: new DateTimeImmutable($payload['submitted_at'])
        );
    }
}
```

---

## 4. Projector 設計

### 4.1 Questionnaire Projector

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Projector;

use Liangjin0228\Questionnaire\Contracts\Infrastructure\ProjectorInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\*;
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

    public function onQuestionnaireArchived(QuestionnaireArchived $event): void
    {
        QuestionnaireModel::where('id', (string) $event->getAggregateId())
            ->update([
                'status' => 'archived',
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

    public function onQuestionUpdated(QuestionUpdated $event): void
    {
        QuestionModel::where('id', (string) $event->questionId)
            ->update([
                'type' => $event->type->value,
                'content' => (string) $event->content,
                'order' => $event->order,
                'is_required' => $event->isRequired,
                'options' => $event->options?->toArray(),
                'settings' => $event->settings,
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
            QuestionnaireArchived::class => 'onQuestionnaireArchived',
            QuestionAdded::class => 'onQuestionAdded',
            QuestionUpdated::class => 'onQuestionUpdated',
            QuestionRemoved::class => 'onQuestionRemoved',
        ];
    }

    public function reset(): void
    {
        QuestionModel::query()->delete();
        QuestionnaireModel::query()->delete();
    }
}
```

### 4.2 Response Projector

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Projector;

use Liangjin0228\Questionnaire\Contracts\Infrastructure\ProjectorInterface;
use Liangjin0228\Questionnaire\Domain\Response\Event\ResponseSubmitted;
use Liangjin0228\Questionnaire\Infrastructure\Persistence\ReadModel\AnswerModel;
use Liangjin0228\Questionnaire\Infrastructure\Persistence\ReadModel\ResponseModel;

final class ResponseProjector implements ProjectorInterface
{
    public function onResponseSubmitted(ResponseSubmitted $event): void
    {
        // 創建回應記錄
        ResponseModel::create([
            'id' => (string) $event->getAggregateId(),
            'questionnaire_id' => (string) $event->questionnaireId,
            'user_id' => $event->respondentInfo->getUserId(),
            'email' => $event->respondentInfo->getEmail(),
            'name' => $event->respondentInfo->getName(),
            'ip_address' => $event->metadata->getIpAddress(),
            'session_id' => $event->metadata->getSessionId(),
            'user_agent' => $event->metadata->getUserAgent(),
            'submitted_at' => $event->submittedAt,
            'created_at' => $event->getOccurredAt(),
            'updated_at' => $event->getOccurredAt(),
        ]);

        // 創建答案記錄
        foreach ($event->answers as $answer) {
            AnswerModel::create([
                'id' => (string) $answer->getId(),
                'response_id' => (string) $event->getAggregateId(),
                'question_id' => (string) $answer->getQuestionId(),
                'value' => $answer->getValue()->toArray(),
                'created_at' => $event->getOccurredAt(),
            ]);
        }
    }

    public function getSubscribedEvents(): array
    {
        return [
            ResponseSubmitted::class => 'onResponseSubmitted',
        ];
    }

    public function reset(): void
    {
        AnswerModel::query()->delete();
        ResponseModel::query()->delete();
    }
}
```

---

## 5. 快照策略

### 5.1 何時創建快照

```php
<?php

// 在 Repository 中實作快照策略
final class EventSourcedQuestionnaireRepository implements QuestionnaireRepositoryInterface
{
    private const SNAPSHOT_THRESHOLD = 10;  // 每 10 個事件創建快照

    public function save(Questionnaire $questionnaire): void
    {
        $events = $questionnaire->releaseEvents();
        
        if (empty($events)) {
            return;
        }

        $aggregateId = (string) $questionnaire->getAggregateId();
        $expectedVersion = $questionnaire->getAggregateVersion() - count($events);

        // 保存事件
        $this->eventStore->append($aggregateId, $events, $expectedVersion);

        // 檢查是否需要快照
        $newVersion = $questionnaire->getAggregateVersion();
        if ($this->shouldSnapshot($newVersion)) {
            $this->snapshotStore->save($aggregateId, $questionnaire, $newVersion);
        }
    }

    private function shouldSnapshot(int $version): bool
    {
        return $version > 0 && $version % self::SNAPSHOT_THRESHOLD === 0;
    }
}
```

### 5.2 從快照恢復

```php
public function get(QuestionnaireId $id): Questionnaire
{
    $aggregateId = (string) $id;

    // 1. 嘗試載入快照
    $snapshot = $this->snapshotStore?->get($aggregateId);
    
    if ($snapshot !== null) {
        // 2. 從快照恢復，只載入之後的事件
        $questionnaire = $snapshot->getAggregate();
        $events = $this->eventStore->getEventsForAggregate($aggregateId, $snapshot->getVersion());
        
        foreach ($events as $event) {
            $questionnaire->applyEvent($event);
        }
        
        return $questionnaire;
    }

    // 3. 沒有快照，從頭載入所有事件
    $events = $this->eventStore->getEventsForAggregate($aggregateId);
    
    if (empty($events)) {
        throw new AggregateNotFoundException("Questionnaire not found: {$aggregateId}");
    }

    return Questionnaire::reconstituteFromHistory($events);
}
```

---

## 6. 使用 spatie/laravel-event-sourcing

雖然上面展示了自定義實作，但建議使用成熟的 `spatie/laravel-event-sourcing` 套件：

```php
<?php

use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

// 使用 Spatie 套件的聚合根
final class Questionnaire extends AggregateRoot
{
    // ... 聚合邏輯
    
    protected function applyQuestionnaireCreated(QuestionnaireCreated $event): void
    {
        $this->id = $event->id;
        $this->title = $event->title;
        // ...
    }
}

// 使用
$questionnaire = Questionnaire::retrieve($id);
$questionnaire->publish();
$questionnaire->persist();
```

詳見 [07-dependencies.md](./07-dependencies.md) 中的套件配置說明。

---

**下一篇**: [07-dependencies.md](./07-dependencies.md) - 依賴套件變更計劃
