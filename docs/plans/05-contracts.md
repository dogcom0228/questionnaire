# 介面與契約設計

> **文件**: 05-contracts.md  
> **上一篇**: [04-infrastructure-layer.md](./04-infrastructure-layer.md)  
> **下一篇**: [06-event-sourcing.md](./06-event-sourcing.md)

---

## 1. Contracts 組織結構

所有介面集中在 `src/Contracts/` 目錄，按層級分類：

```
src/Contracts/
├── Domain/
│   ├── AggregateRootInterface.php
│   ├── EntityInterface.php
│   ├── ValueObjectInterface.php
│   ├── DomainEventInterface.php
│   ├── SpecificationInterface.php
│   └── Repository/
│       ├── QuestionnaireRepositoryInterface.php
│       └── ResponseRepositoryInterface.php
│
├── Application/
│   ├── CommandInterface.php
│   ├── CommandHandlerInterface.php
│   ├── QueryInterface.php
│   ├── QueryHandlerInterface.php
│   └── Bus/
│       ├── CommandBusInterface.php
│       ├── QueryBusInterface.php
│       └── EventBusInterface.php
│
└── Infrastructure/
    ├── EventStoreInterface.php
    ├── SnapshotStoreInterface.php
    ├── ProjectorInterface.php
    └── ExporterInterface.php
```

---

## 2. Domain 層介面

### 2.1 AggregateRootInterface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Domain;

use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateId;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

interface AggregateRootInterface
{
    /**
     * 獲取聚合 ID
     */
    public function getAggregateId(): AggregateId;

    /**
     * 獲取聚合版本
     */
    public function getAggregateVersion(): int;

    /**
     * 從事件歷史重建聚合
     * 
     * @param iterable<DomainEvent> $events
     */
    public static function reconstituteFromHistory(iterable $events): static;

    /**
     * 獲取並清除已錄製的事件
     * 
     * @return array<DomainEvent>
     */
    public function releaseEvents(): array;

    /**
     * 獲取待提交事件數量
     */
    public function getUncommittedEventsCount(): int;
}
```

### 2.2 EntityInterface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Domain;

use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateId;

interface EntityInterface
{
    /**
     * 獲取實體 ID
     */
    public function getId(): AggregateId;

    /**
     * 比較兩個實體是否相同
     */
    public function equals(EntityInterface $other): bool;
}
```

### 2.3 ValueObjectInterface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Domain;

interface ValueObjectInterface
{
    /**
     * 比較兩個值物件是否相等
     */
    public function equals(self $other): bool;

    /**
     * 返回字串表示
     */
    public function __toString(): string;
}
```

### 2.4 DomainEventInterface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Domain;

use DateTimeImmutable;
use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateId;
use Ramsey\Uuid\UuidInterface;

interface DomainEventInterface
{
    /**
     * 獲取事件 ID
     */
    public function getEventId(): UuidInterface;

    /**
     * 獲取聚合 ID
     */
    public function getAggregateId(): AggregateId;

    /**
     * 獲取事件發生時間
     */
    public function getOccurredAt(): DateTimeImmutable;

    /**
     * 獲取事件類型名稱
     */
    public function getEventType(): string;

    /**
     * 序列化事件數據
     * 
     * @return array<string, mixed>
     */
    public function toPayload(): array;

    /**
     * 從數據重建事件
     * 
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload, AggregateId $aggregateId): static;
}
```

### 2.5 SpecificationInterface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Domain;

interface SpecificationInterface
{
    /**
     * 檢查候選者是否滿足規格
     */
    public function isSatisfiedBy(object $candidate): bool;

    /**
     * 獲取不滿足的原因
     */
    public function getUnsatisfiedReason(object $candidate): ?string;

    /**
     * AND 組合
     */
    public function and(self $other): self;

    /**
     * OR 組合
     */
    public function or(self $other): self;

    /**
     * NOT 反轉
     */
    public function not(): self;
}
```

### 2.6 Repository Interfaces

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Domain\Repository;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;

interface QuestionnaireRepositoryInterface
{
    /**
     * 根據 ID 獲取問卷聚合
     * 
     * @throws \RuntimeException 當問卷不存在時
     */
    public function get(QuestionnaireId $id): Questionnaire;

    /**
     * 保存問卷聚合
     */
    public function save(Questionnaire $questionnaire): void;

    /**
     * 檢查問卷是否存在
     */
    public function exists(QuestionnaireId $id): bool;
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Domain\Repository;

use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Response\Aggregate\Response;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\SubmissionMetadata;

interface ResponseRepositoryInterface
{
    /**
     * 根據 ID 獲取回應聚合
     */
    public function get(ResponseId $id): Response;

    /**
     * 保存回應聚合
     */
    public function save(Response $response): void;

    /**
     * 檢查回應是否存在
     */
    public function exists(ResponseId $id): bool;

    /**
     * 獲取問卷的回應數量
     */
    public function countByQuestionnaire(QuestionnaireId $questionnaireId): int;

    /**
     * 檢查是否存在重複提交
     */
    public function existsByMetadata(
        QuestionnaireId $questionnaireId,
        SubmissionMetadata $metadata
    ): bool;
}
```

---

## 3. Application 層介面

### 3.1 Command/Query Interfaces

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application;

/**
 * 命令介面（寫入操作）
 * 
 * @template TResult 命令執行結果類型
 */
interface CommandInterface
{
    // Marker interface
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application;

interface CommandHandlerInterface
{
    /**
     * 處理命令
     * 
     * @param CommandInterface $command
     * @return mixed
     */
    public function handle(CommandInterface $command): mixed;
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application;

/**
 * 查詢介面（讀取操作）
 * 
 * @template TResult 查詢結果類型
 */
interface QueryInterface
{
    // Marker interface
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application;

interface QueryHandlerInterface
{
    /**
     * 處理查詢
     * 
     * @param QueryInterface $query
     * @return mixed
     */
    public function handle(QueryInterface $query): mixed;
}
```

### 3.2 Bus Interfaces

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application\Bus;

use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;

interface CommandBusInterface
{
    /**
     * 分發命令到對應的處理器
     * 
     * @template T
     * @param CommandInterface<T> $command
     * @return T
     */
    public function dispatch(CommandInterface $command): mixed;

    /**
     * 註冊命令處理器
     * 
     * @param class-string<CommandInterface> $commandClass
     * @param class-string $handlerClass
     */
    public function register(string $commandClass, string $handlerClass): void;
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application\Bus;

use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

interface QueryBusInterface
{
    /**
     * 分發查詢到對應的處理器
     * 
     * @template T
     * @param QueryInterface<T> $query
     * @return T
     */
    public function dispatch(QueryInterface $query): mixed;

    /**
     * 註冊查詢處理器
     * 
     * @param class-string<QueryInterface> $queryClass
     * @param class-string $handlerClass
     */
    public function register(string $queryClass, string $handlerClass): void;
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application\Bus;

use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

interface EventBusInterface
{
    /**
     * 發布領域事件
     */
    public function dispatch(DomainEvent $event): void;

    /**
     * 批量發布事件
     * 
     * @param array<DomainEvent> $events
     */
    public function dispatchMany(array $events): void;
}
```

---

## 4. Infrastructure 層介面

### 4.1 EventStoreInterface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Infrastructure;

use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

interface EventStoreInterface
{
    /**
     * 追加事件到事件流
     * 
     * @param string $aggregateId
     * @param array<DomainEvent> $events
     * @param int $expectedVersion 預期的聚合版本（用於樂觀鎖定）
     * @throws ConcurrencyException 當版本衝突時
     */
    public function append(string $aggregateId, array $events, int $expectedVersion): void;

    /**
     * 獲取聚合的事件流
     * 
     * @param string $aggregateId
     * @param int $fromVersion 從哪個版本開始（用於快照後重播）
     * @return array<DomainEvent>
     */
    public function getEventsForAggregate(string $aggregateId, int $fromVersion = 0): array;

    /**
     * 檢查聚合是否有事件
     */
    public function hasEventsForAggregate(string $aggregateId): bool;

    /**
     * 獲取所有事件（用於重建投影）
     * 
     * @param string|null $eventType 過濾特定事件類型
     * @param int $limit 限制數量
     * @return array<DomainEvent>
     */
    public function getAllEvents(?string $eventType = null, int $limit = 1000): array;

    /**
     * 獲取從特定位置開始的事件
     * 
     * @param int $position 起始位置
     * @param int $limit 限制數量
     * @return array<DomainEvent>
     */
    public function getEventsFromPosition(int $position, int $limit = 1000): array;
}
```

### 4.2 SnapshotStoreInterface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Infrastructure;

use Liangjin0228\Questionnaire\Contracts\Domain\AggregateRootInterface;

interface SnapshotStoreInterface
{
    /**
     * 保存快照
     */
    public function save(string $aggregateId, AggregateRootInterface $aggregate, int $version): void;

    /**
     * 獲取快照
     */
    public function get(string $aggregateId): ?Snapshot;

    /**
     * 刪除快照
     */
    public function delete(string $aggregateId): void;

    /**
     * 檢查快照是否存在
     */
    public function exists(string $aggregateId): bool;
}

/**
 * 快照值物件
 */
final readonly class Snapshot
{
    public function __construct(
        private string $aggregateId,
        private AggregateRootInterface $aggregate,
        private int $version,
        private \DateTimeImmutable $createdAt
    ) {}

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    public function getAggregate(): AggregateRootInterface
    {
        return $this->aggregate;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
```

### 4.3 ProjectorInterface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Infrastructure;

interface ProjectorInterface
{
    /**
     * 獲取訂閱的事件類型
     * 
     * @return array<class-string, string> 事件類型 => 處理方法名
     */
    public function getSubscribedEvents(): array;

    /**
     * 重置投影（清除所有數據）
     */
    public function reset(): void;
}
```

### 4.4 ExporterInterface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Infrastructure;

use Symfony\Component\HttpFoundation\StreamedResponse;

interface ExporterInterface
{
    /**
     * 匯出問卷回應
     * 
     * @param string $questionnaireId
     * @param string $format 匯出格式 (csv, xlsx, json)
     */
    public function export(string $questionnaireId, string $format = 'csv'): StreamedResponse;

    /**
     * 獲取支援的匯出格式
     * 
     * @return array<string>
     */
    public function getSupportedFormats(): array;
}
```

---

## 5. Question Type Interface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\QuestionType;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question;

interface QuestionTypeInterface
{
    /**
     * 獲取問題類型標識符
     */
    public function getIdentifier(): string;

    /**
     * 獲取顯示名稱
     */
    public function getName(): string;

    /**
     * 獲取描述
     */
    public function getDescription(): string;

    /**
     * 獲取圖標
     */
    public function getIcon(): string;

    /**
     * 是否支援選項
     */
    public function supportsOptions(): bool;

    /**
     * 獲取驗證規則
     * 
     * @return array<string, mixed>
     */
    public function getValidationRules(Question $question): array;

    /**
     * 獲取驗證訊息
     * 
     * @return array<string, string>
     */
    public function getValidationMessages(): array;

    /**
     * 驗證答案值
     * 
     * @return array<string> 錯誤訊息
     */
    public function validate(mixed $value, Question $question): array;

    /**
     * 轉換答案值（用於儲存前處理）
     */
    public function transformValue(mixed $value): mixed;

    /**
     * 格式化答案值（用於顯示）
     */
    public function formatValue(mixed $value, Question $question): string;

    /**
     * 獲取 Vue 組件名稱
     */
    public function getVueComponent(): string;

    /**
     * 獲取配置
     * 
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * 序列化為陣列
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
```

---

## 6. Guard Interface

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Guard;

use Liangjin0228\Questionnaire\Contracts\Domain\Repository\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\SubmissionMetadata;

interface DuplicateSubmissionGuardInterface
{
    /**
     * 獲取防護策略標識符
     */
    public function getIdentifier(): string;

    /**
     * 檢查是否可以提交
     */
    public function canSubmit(
        Questionnaire $questionnaire,
        SubmissionMetadata $metadata,
        ResponseRepositoryInterface $responseRepository
    ): bool;

    /**
     * 獲取拒絕原因
     */
    public function getRejectionReason(): string;
}
```

---

## 7. 介面依賴圖

```
┌─────────────────────────────────────────────────────────────┐
│                      Contracts                               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Domain                                                      │
│  ├── AggregateRootInterface                                 │
│  ├── EntityInterface                                        │
│  ├── ValueObjectInterface                                   │
│  ├── DomainEventInterface                                   │
│  ├── SpecificationInterface                                 │
│  └── Repository/                                            │
│      ├── QuestionnaireRepositoryInterface                   │
│      └── ResponseRepositoryInterface                        │
│                     │                                       │
│                     ▼                                       │
│  Application                                                 │
│  ├── CommandInterface                                       │
│  ├── CommandHandlerInterface ──uses──▶ Repository           │
│  ├── QueryInterface                                         │
│  ├── QueryHandlerInterface                                  │
│  └── Bus/                                                   │
│      ├── CommandBusInterface ──dispatches──▶ CommandHandler │
│      ├── QueryBusInterface ──dispatches──▶ QueryHandler     │
│      └── EventBusInterface ──publishes──▶ DomainEvent       │
│                     │                                       │
│                     ▼                                       │
│  Infrastructure                                              │
│  ├── EventStoreInterface ──stores──▶ DomainEvent            │
│  ├── SnapshotStoreInterface ──stores──▶ AggregateRoot       │
│  ├── ProjectorInterface ──listens──▶ DomainEvent            │
│  └── ExporterInterface                                      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

**下一篇**: [06-event-sourcing.md](./06-event-sourcing.md) - Event Sourcing 實作計劃
