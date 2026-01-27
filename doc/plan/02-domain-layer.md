# Domain 層重構計劃

> **文件**: 02-domain-layer.md  
> **上一篇**: [01-architecture.md](./01-architecture.md)  
> **下一篇**: [03-application-layer.md](./03-application-layer.md)

---

## 1. Domain 層設計原則

### 1.1 核心原則

1. **純淨領域模型** - 不依賴任何框架（Laravel, Eloquent）
2. **富模型設計** - 業務邏輯在 Aggregate/Entity 內部
3. **不變條件保護** - Aggregate 確保數據一致性
4. **Event Sourcing** - 所有狀態變更透過領域事件

### 1.2 禁止使用

```php
// Domain 層禁止使用以下內容：

// ❌ Laravel Facades
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

// ❌ Eloquent Model 基類
extends Model;

// ❌ Laravel 特定的 Trait
use HasFactory, SoftDeletes;

// ❌ 框架異常
throw new \Illuminate\Validation\ValidationException();
```

### 1.3 允許使用

```php
// Domain 層允許使用以下內容：

// ✅ 純 PHP
class Questionnaire extends AggregateRoot {}

// ✅ PHP 內建類
use DateTimeImmutable;
use JsonSerializable;

// ✅ 第三方純 PHP 套件（無框架依賴）
use Ramsey\Uuid\Uuid;

// ✅ 自定義領域異常
throw new QuestionnaireClosedException();
```

---

## 2. 共享核心（Domain\Shared）

### 2.1 Aggregate Root 基類

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Aggregate;

use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

abstract class AggregateRoot
{
    protected int $aggregateVersion = 0;
    
    /** @var array<DomainEvent> */
    private array $recordedEvents = [];

    /**
     * 獲取聚合 ID
     */
    abstract public function getAggregateId(): AggregateId;

    /**
     * 獲取聚合版本
     */
    public function getAggregateVersion(): int
    {
        return $this->aggregateVersion;
    }

    /**
     * 從事件歷史重建聚合狀態
     * 
     * @param iterable<DomainEvent> $events
     */
    public static function reconstituteFromHistory(iterable $events): static
    {
        $instance = new static();
        
        foreach ($events as $event) {
            $instance->applyEvent($event);
            $instance->aggregateVersion++;
        }
        
        return $instance;
    }

    /**
     * 錄製領域事件
     */
    protected function recordThat(DomainEvent $event): void
    {
        $this->recordedEvents[] = $event;
        $this->applyEvent($event);
    }

    /**
     * 獲取並清除已錄製的事件
     * 
     * @return array<DomainEvent>
     */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }

    /**
     * 獲取待提交事件數量
     */
    public function getUncommittedEventsCount(): int
    {
        return count($this->recordedEvents);
    }

    /**
     * 應用事件到聚合狀態
     */
    protected function applyEvent(DomainEvent $event): void
    {
        $method = $this->getApplyMethodName($event);
        
        if (!method_exists($this, $method)) {
            throw new \RuntimeException(
                sprintf('Missing apply method "%s" for event "%s"', $method, get_class($event))
            );
        }
        
        $this->{$method}($event);
    }

    /**
     * 根據事件類型獲取應用方法名稱
     * 
     * @example QuestionnaireCreated -> applyQuestionnaireCreated
     */
    private function getApplyMethodName(DomainEvent $event): string
    {
        $className = (new \ReflectionClass($event))->getShortName();
        return 'apply' . $className;
    }
}
```

### 2.2 Aggregate ID 基類

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Aggregate;

use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Stringable;

abstract class AggregateId extends ValueObject implements Stringable
{
    protected UuidInterface $value;

    protected function __construct(UuidInterface $value)
    {
        $this->value = $value;
    }

    /**
     * 生成新的 ID
     */
    public static function generate(): static
    {
        return new static(Uuid::uuid7());
    }

    /**
     * 從字串建立
     */
    public static function fromString(string $value): static
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid UUID format: %s', $value)
            );
        }
        return new static(Uuid::fromString($value));
    }

    /**
     * 獲取 UUID 值
     */
    public function getValue(): UuidInterface
    {
        return $this->value;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof static && $this->value->equals($other->value);
    }

    public function __toString(): string
    {
        return $this->value->toString();
    }

    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
}
```

### 2.3 Entity 基類

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Entity;

use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateId;

abstract class Entity
{
    /**
     * 獲取實體 ID
     */
    abstract public function getId(): AggregateId;

    /**
     * 比較兩個實體是否相同（基於 ID）
     */
    public function equals(Entity $other): bool
    {
        return get_class($this) === get_class($other) 
            && $this->getId()->equals($other->getId());
    }
}
```

### 2.4 Value Object 基類

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\ValueObject;

use JsonSerializable;

abstract class ValueObject implements JsonSerializable
{
    /**
     * 比較兩個值物件是否相等
     */
    abstract public function equals(self $other): bool;

    /**
     * 返回字串表示
     */
    abstract public function __toString(): string;

    /**
     * JSON 序列化
     */
    public function jsonSerialize(): mixed
    {
        return $this->__toString();
    }
}
```

### 2.5 Domain Event 基類

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Event;

use DateTimeImmutable;
use JsonSerializable;
use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateId;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class DomainEvent implements JsonSerializable
{
    private UuidInterface $eventId;
    private DateTimeImmutable $occurredAt;

    public function __construct(
        private readonly AggregateId $aggregateId,
        ?DateTimeImmutable $occurredAt = null
    ) {
        $this->eventId = Uuid::uuid7();
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    /**
     * 獲取事件 ID
     */
    public function getEventId(): UuidInterface
    {
        return $this->eventId;
    }

    /**
     * 獲取聚合 ID
     */
    public function getAggregateId(): AggregateId
    {
        return $this->aggregateId;
    }

    /**
     * 獲取事件發生時間
     */
    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * 獲取事件類型名稱
     */
    public function getEventType(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * 序列化事件數據（不含元數據）
     * 
     * @return array<string, mixed>
     */
    abstract public function toPayload(): array;

    /**
     * 從數據重建事件
     * 
     * @param array<string, mixed> $payload
     */
    abstract public static function fromPayload(array $payload, AggregateId $aggregateId): static;

    public function jsonSerialize(): array
    {
        return [
            'event_id' => $this->eventId->toString(),
            'event_type' => $this->getEventType(),
            'aggregate_id' => (string) $this->aggregateId,
            'occurred_at' => $this->occurredAt->format('c'),
            'payload' => $this->toPayload(),
        ];
    }
}
```

### 2.6 Specification 基類

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Specification;

use Liangjin0228\Questionnaire\Contracts\Domain\SpecificationInterface;

abstract class Specification implements SpecificationInterface
{
    /**
     * 檢查候選者是否滿足規格
     */
    abstract public function isSatisfiedBy(object $candidate): bool;

    /**
     * 獲取不滿足的原因
     */
    public function getUnsatisfiedReason(object $candidate): ?string
    {
        return $this->isSatisfiedBy($candidate) ? null : 'Specification not satisfied';
    }

    /**
     * AND 組合
     */
    public function and(SpecificationInterface $other): SpecificationInterface
    {
        return new AndSpecification($this, $other);
    }

    /**
     * OR 組合
     */
    public function or(SpecificationInterface $other): SpecificationInterface
    {
        return new OrSpecification($this, $other);
    }

    /**
     * NOT 反轉
     */
    public function not(): SpecificationInterface
    {
        return new NotSpecification($this);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Specification;

use Liangjin0228\Questionnaire\Contracts\Domain\SpecificationInterface;

final class AndSpecification extends Specification
{
    public function __construct(
        private readonly SpecificationInterface $left,
        private readonly SpecificationInterface $right
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        return $this->left->isSatisfiedBy($candidate) 
            && $this->right->isSatisfiedBy($candidate);
    }

    public function getUnsatisfiedReason(object $candidate): ?string
    {
        if (!$this->left->isSatisfiedBy($candidate)) {
            return $this->left->getUnsatisfiedReason($candidate);
        }
        if (!$this->right->isSatisfiedBy($candidate)) {
            return $this->right->getUnsatisfiedReason($candidate);
        }
        return null;
    }
}
```

---

## 3. Questionnaire 聚合設計

### 3.1 Value Objects

#### QuestionnaireId

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject;

use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateId;

final class QuestionnaireId extends AggregateId
{
    // 繼承 AggregateId 的所有功能
}
```

#### QuestionnaireTitle

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionnaireException;
use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class QuestionnaireTitle extends ValueObject
{
    private const MIN_LENGTH = 1;
    private const MAX_LENGTH = 255;

    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = trim($value);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function validate(string $value): void
    {
        $trimmed = trim($value);
        $length = mb_strlen($trimmed);
        
        if ($length < self::MIN_LENGTH) {
            throw InvalidQuestionnaireException::emptyTitle();
        }

        if ($length > self::MAX_LENGTH) {
            throw InvalidQuestionnaireException::titleTooLong(self::MAX_LENGTH);
        }
    }
}
```

#### QuestionnaireSlug

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject;

use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class QuestionnaireSlug extends ValueObject
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function fromTitle(QuestionnaireTitle $title): self
    {
        $slug = self::slugify((string) $title);
        
        // 如果 slugify 後為空，生成隨機 slug
        if (empty($slug)) {
            $slug = bin2hex(random_bytes(5));
        }
        
        return new self($slug);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function slugify(string $text): string
    {
        // 轉換為小寫
        $text = mb_strtolower($text);
        
        // 替換非字母數字為連字符
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
        
        // 移除連續連字符
        $text = preg_replace('/-+/', '-', $text);
        
        // 移除首尾連字符
        return trim($text, '-');
    }
}
```

#### DateRange

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject;

use DateTimeImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionnaireException;
use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class DateRange extends ValueObject
{
    private function __construct(
        private readonly ?DateTimeImmutable $startsAt,
        private readonly ?DateTimeImmutable $endsAt
    ) {
        $this->validate();
    }

    public static function create(?DateTimeImmutable $startsAt, ?DateTimeImmutable $endsAt): self
    {
        return new self($startsAt, $endsAt);
    }

    public static function openEnded(): self
    {
        return new self(null, null);
    }

    public function getStartsAt(): ?DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getEndsAt(): ?DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function isActive(?DateTimeImmutable $at = null): bool
    {
        $now = $at ?? new DateTimeImmutable();

        if ($this->startsAt !== null && $this->startsAt > $now) {
            return false;
        }

        if ($this->endsAt !== null && $this->endsAt < $now) {
            return false;
        }

        return true;
    }

    public function hasStarted(?DateTimeImmutable $at = null): bool
    {
        if ($this->startsAt === null) {
            return true;
        }
        
        $now = $at ?? new DateTimeImmutable();
        return $this->startsAt <= $now;
    }

    public function hasEnded(?DateTimeImmutable $at = null): bool
    {
        if ($this->endsAt === null) {
            return false;
        }
        
        $now = $at ?? new DateTimeImmutable();
        return $this->endsAt < $now;
    }

    public function equals(ValueObject $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->startsAt?->getTimestamp() === $other->startsAt?->getTimestamp()
            && $this->endsAt?->getTimestamp() === $other->endsAt?->getTimestamp();
    }

    public function __toString(): string
    {
        $start = $this->startsAt?->format('Y-m-d H:i:s') ?? 'open';
        $end = $this->endsAt?->format('Y-m-d H:i:s') ?? 'open';
        return sprintf('%s - %s', $start, $end);
    }

    public function jsonSerialize(): array
    {
        return [
            'starts_at' => $this->startsAt?->format('c'),
            'ends_at' => $this->endsAt?->format('c'),
        ];
    }

    private function validate(): void
    {
        if ($this->startsAt !== null && $this->endsAt !== null) {
            if ($this->startsAt > $this->endsAt) {
                throw InvalidQuestionnaireException::invalidDateRange();
            }
        }
    }
}
```

#### QuestionnaireSettings

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\DuplicateSubmissionStrategy;
use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class QuestionnaireSettings extends ValueObject
{
    private function __construct(
        private readonly bool $requiresAuth,
        private readonly ?int $submissionLimit,
        private readonly DuplicateSubmissionStrategy $duplicateSubmissionStrategy,
        private readonly bool $showProgressBar,
        private readonly bool $randomizeQuestions,
        private readonly ?string $thankYouMessage,
        private readonly array $customSettings = []
    ) {}

    public static function default(): self
    {
        return new self(
            requiresAuth: false,
            submissionLimit: null,
            duplicateSubmissionStrategy: DuplicateSubmissionStrategy::ALLOW_MULTIPLE,
            showProgressBar: true,
            randomizeQuestions: false,
            thankYouMessage: null
        );
    }

    public static function create(
        bool $requiresAuth = false,
        ?int $submissionLimit = null,
        DuplicateSubmissionStrategy $duplicateSubmissionStrategy = DuplicateSubmissionStrategy::ALLOW_MULTIPLE,
        bool $showProgressBar = true,
        bool $randomizeQuestions = false,
        ?string $thankYouMessage = null,
        array $customSettings = []
    ): self {
        return new self(
            $requiresAuth,
            $submissionLimit,
            $duplicateSubmissionStrategy,
            $showProgressBar,
            $randomizeQuestions,
            $thankYouMessage,
            $customSettings
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            requiresAuth: $data['requires_auth'] ?? false,
            submissionLimit: $data['submission_limit'] ?? null,
            duplicateSubmissionStrategy: isset($data['duplicate_submission_strategy'])
                ? DuplicateSubmissionStrategy::from($data['duplicate_submission_strategy'])
                : DuplicateSubmissionStrategy::ALLOW_MULTIPLE,
            showProgressBar: $data['show_progress_bar'] ?? true,
            randomizeQuestions: $data['randomize_questions'] ?? false,
            thankYouMessage: $data['thank_you_message'] ?? null,
            customSettings: $data['custom'] ?? []
        );
    }

    public function requiresAuth(): bool
    {
        return $this->requiresAuth;
    }

    public function getSubmissionLimit(): ?int
    {
        return $this->submissionLimit;
    }

    public function getDuplicateSubmissionStrategy(): DuplicateSubmissionStrategy
    {
        return $this->duplicateSubmissionStrategy;
    }

    public function showProgressBar(): bool
    {
        return $this->showProgressBar;
    }

    public function randomizeQuestions(): bool
    {
        return $this->randomizeQuestions;
    }

    public function getThankYouMessage(): ?string
    {
        return $this->thankYouMessage;
    }

    public function getCustomSetting(string $key, mixed $default = null): mixed
    {
        return $this->customSettings[$key] ?? $default;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->toArray() === $other->toArray();
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    public function toArray(): array
    {
        return [
            'requires_auth' => $this->requiresAuth,
            'submission_limit' => $this->submissionLimit,
            'duplicate_submission_strategy' => $this->duplicateSubmissionStrategy->value,
            'show_progress_bar' => $this->showProgressBar,
            'randomize_questions' => $this->randomizeQuestions,
            'thank_you_message' => $this->thankYouMessage,
            'custom' => $this->customSettings,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
```

### 3.2 Enums

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Enum;

enum QuestionnaireStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case CLOSED = 'closed';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::CLOSED => 'Closed',
            self::ARCHIVED => 'Archived',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return match($this) {
            self::DRAFT => $target === self::PUBLISHED || $target === self::ARCHIVED,
            self::PUBLISHED => $target === self::CLOSED,
            self::CLOSED => $target === self::ARCHIVED,
            self::ARCHIVED => false,
        };
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Enum;

enum QuestionType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case RADIO = 'radio';
    case CHECKBOX = 'checkbox';
    case SELECT = 'select';
    case NUMBER = 'number';
    case DATE = 'date';
    case EMAIL = 'email';
    case URL = 'url';
    case RATING = 'rating';

    public function label(): string
    {
        return match($this) {
            self::TEXT => 'Short Text',
            self::TEXTAREA => 'Long Text',
            self::RADIO => 'Single Choice',
            self::CHECKBOX => 'Multiple Choice',
            self::SELECT => 'Dropdown',
            self::NUMBER => 'Number',
            self::DATE => 'Date',
            self::EMAIL => 'Email',
            self::URL => 'URL',
            self::RATING => 'Rating',
        };
    }

    public function supportsOptions(): bool
    {
        return match($this) {
            self::RADIO, self::CHECKBOX, self::SELECT => true,
            default => false,
        };
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Enum;

enum DuplicateSubmissionStrategy: string
{
    case ALLOW_MULTIPLE = 'allow_multiple';
    case ONE_PER_USER = 'one_per_user';
    case ONE_PER_SESSION = 'one_per_session';
    case ONE_PER_IP = 'one_per_ip';

    public function label(): string
    {
        return match($this) {
            self::ALLOW_MULTIPLE => 'Allow Multiple Submissions',
            self::ONE_PER_USER => 'One Submission Per User',
            self::ONE_PER_SESSION => 'One Submission Per Session',
            self::ONE_PER_IP => 'One Submission Per IP Address',
        };
    }
}
```

### 3.3 Domain Events

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Event;

use DateTimeImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;
use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateId;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;
use Liangjin0228\Questionnaire\Domain\User\ValueObject\UserId;

final class QuestionnaireCreated extends DomainEvent
{
    public function __construct(
        QuestionnaireId $aggregateId,
        public readonly QuestionnaireTitle $title,
        public readonly ?string $description,
        public readonly QuestionnaireSlug $slug,
        public readonly QuestionnaireStatus $status,
        public readonly ?UserId $ownerId,
        public readonly QuestionnaireSettings $settings,
        ?DateTimeImmutable $occurredAt = null
    ) {
        parent::__construct($aggregateId, $occurredAt);
    }

    public function toPayload(): array
    {
        return [
            'title' => (string) $this->title,
            'description' => $this->description,
            'slug' => (string) $this->slug,
            'status' => $this->status->value,
            'owner_id' => $this->ownerId ? (string) $this->ownerId : null,
            'settings' => $this->settings->toArray(),
        ];
    }

    public static function fromPayload(array $payload, AggregateId $aggregateId): static
    {
        return new self(
            aggregateId: $aggregateId,
            title: QuestionnaireTitle::fromString($payload['title']),
            description: $payload['description'],
            slug: QuestionnaireSlug::fromString($payload['slug']),
            status: QuestionnaireStatus::from($payload['status']),
            ownerId: isset($payload['owner_id']) ? UserId::fromString($payload['owner_id']) : null,
            settings: QuestionnaireSettings::fromArray($payload['settings'])
        );
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Event;

use DateTimeImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateId;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

final class QuestionnairePublished extends DomainEvent
{
    public function __construct(
        QuestionnaireId $aggregateId,
        public readonly ?DateRange $dateRange,
        public readonly DateTimeImmutable $publishedAt,
        ?DateTimeImmutable $occurredAt = null
    ) {
        parent::__construct($aggregateId, $occurredAt);
    }

    public function toPayload(): array
    {
        return [
            'date_range' => $this->dateRange?->jsonSerialize(),
            'published_at' => $this->publishedAt->format('c'),
        ];
    }

    public static function fromPayload(array $payload, AggregateId $aggregateId): static
    {
        $dateRange = isset($payload['date_range'])
            ? DateRange::create(
                isset($payload['date_range']['starts_at']) 
                    ? new DateTimeImmutable($payload['date_range']['starts_at']) 
                    : null,
                isset($payload['date_range']['ends_at']) 
                    ? new DateTimeImmutable($payload['date_range']['ends_at']) 
                    : null
            )
            : null;

        return new self(
            aggregateId: $aggregateId,
            dateRange: $dateRange,
            publishedAt: new DateTimeImmutable($payload['published_at'])
        );
    }
}
```

### 3.4 Questionnaire Aggregate Root

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate;

use DateTimeImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionAdded;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireClosed;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireCreated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnairePublished;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireUpdated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionRemoved;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\QuestionnaireClosedException;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\QuestionnaireNotPublishedException;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionnaireException;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;
use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateId;
use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateRoot;
use Liangjin0228\Questionnaire\Domain\User\ValueObject\UserId;

final class Questionnaire extends AggregateRoot
{
    private QuestionnaireId $id;
    private QuestionnaireTitle $title;
    private ?string $description = null;
    private QuestionnaireSlug $slug;
    private QuestionnaireStatus $status;
    private QuestionnaireSettings $settings;
    private ?DateRange $dateRange = null;
    private ?UserId $ownerId = null;
    private ?DateTimeImmutable $publishedAt = null;
    private ?DateTimeImmutable $closedAt = null;
    
    /** @var array<string, Question> */
    private array $questions = [];

    private function __construct()
    {
        // Private constructor for reconstitution
    }

    // =====================================
    // Factory Methods
    // =====================================

    public static function create(
        QuestionnaireId $id,
        QuestionnaireTitle $title,
        ?string $description = null,
        ?UserId $ownerId = null,
        ?QuestionnaireSettings $settings = null
    ): self {
        $questionnaire = new self();
        
        $questionnaire->recordThat(new QuestionnaireCreated(
            aggregateId: $id,
            title: $title,
            description: $description,
            slug: QuestionnaireSlug::fromTitle($title),
            status: QuestionnaireStatus::DRAFT,
            ownerId: $ownerId,
            settings: $settings ?? QuestionnaireSettings::default()
        ));

        return $questionnaire;
    }

    // =====================================
    // Command Methods
    // =====================================

    public function update(
        QuestionnaireTitle $title,
        ?string $description = null,
        ?QuestionnaireSettings $settings = null
    ): void {
        $this->ensureNotClosed();

        $this->recordThat(new QuestionnaireUpdated(
            aggregateId: $this->id,
            title: $title,
            description: $description,
            settings: $settings ?? $this->settings
        ));
    }

    public function publish(?DateRange $dateRange = null): void
    {
        $this->ensureIsDraft();
        $this->ensureHasQuestions();

        $this->recordThat(new QuestionnairePublished(
            aggregateId: $this->id,
            dateRange: $dateRange,
            publishedAt: new DateTimeImmutable()
        ));
    }

    public function close(): void
    {
        $this->ensureIsPublished();

        $this->recordThat(new QuestionnaireClosed(
            aggregateId: $this->id,
            closedAt: new DateTimeImmutable()
        ));
    }

    public function addQuestion(Question $question): void
    {
        $this->ensureCanModifyQuestions();

        if ($this->hasQuestion($question->getId())) {
            throw InvalidQuestionnaireException::questionAlreadyExists((string) $question->getId());
        }

        $this->recordThat(new QuestionAdded(
            aggregateId: $this->id,
            question: $question
        ));
    }

    public function removeQuestion(QuestionId $questionId): void
    {
        $this->ensureCanModifyQuestions();

        if (!$this->hasQuestion($questionId)) {
            throw InvalidQuestionnaireException::questionNotFound((string) $questionId);
        }

        $this->recordThat(new QuestionRemoved(
            aggregateId: $this->id,
            questionId: $questionId
        ));
    }

    // =====================================
    // Query Methods
    // =====================================

    public function isAcceptingResponses(?DateTimeImmutable $at = null): bool
    {
        if ($this->status !== QuestionnaireStatus::PUBLISHED) {
            return false;
        }

        if ($this->dateRange !== null && !$this->dateRange->isActive($at)) {
            return false;
        }

        return true;
    }

    public function hasQuestion(QuestionId $questionId): bool
    {
        return isset($this->questions[(string) $questionId]);
    }

    public function getQuestion(QuestionId $questionId): ?Question
    {
        return $this->questions[(string) $questionId] ?? null;
    }

    public function getQuestionCount(): int
    {
        return count($this->questions);
    }

    // =====================================
    // Event Apply Methods
    // =====================================

    protected function applyQuestionnaireCreated(QuestionnaireCreated $event): void
    {
        $this->id = $event->getAggregateId();
        $this->title = $event->title;
        $this->description = $event->description;
        $this->slug = $event->slug;
        $this->status = $event->status;
        $this->ownerId = $event->ownerId;
        $this->settings = $event->settings;
    }

    protected function applyQuestionnaireUpdated(QuestionnaireUpdated $event): void
    {
        $this->title = $event->title;
        $this->description = $event->description;
        $this->settings = $event->settings;
    }

    protected function applyQuestionnairePublished(QuestionnairePublished $event): void
    {
        $this->status = QuestionnaireStatus::PUBLISHED;
        $this->dateRange = $event->dateRange;
        $this->publishedAt = $event->publishedAt;
    }

    protected function applyQuestionnaireClosed(QuestionnaireClosed $event): void
    {
        $this->status = QuestionnaireStatus::CLOSED;
        $this->closedAt = $event->closedAt;
    }

    protected function applyQuestionAdded(QuestionAdded $event): void
    {
        $this->questions[(string) $event->question->getId()] = $event->question;
    }

    protected function applyQuestionRemoved(QuestionRemoved $event): void
    {
        unset($this->questions[(string) $event->questionId]);
    }

    // =====================================
    // Guard Methods
    // =====================================

    private function ensureIsDraft(): void
    {
        if ($this->status !== QuestionnaireStatus::DRAFT) {
            throw InvalidQuestionnaireException::cannotPublishNonDraft($this->status);
        }
    }

    private function ensureIsPublished(): void
    {
        if ($this->status !== QuestionnaireStatus::PUBLISHED) {
            throw new QuestionnaireNotPublishedException(
                'Questionnaire must be published before it can be closed.'
            );
        }
    }

    private function ensureNotClosed(): void
    {
        if ($this->status === QuestionnaireStatus::CLOSED || $this->status === QuestionnaireStatus::ARCHIVED) {
            throw new QuestionnaireClosedException(
                'Cannot modify a closed or archived questionnaire.'
            );
        }
    }

    private function ensureCanModifyQuestions(): void
    {
        if ($this->status !== QuestionnaireStatus::DRAFT) {
            throw InvalidQuestionnaireException::cannotModifyQuestionsAfterPublish();
        }
    }

    private function ensureHasQuestions(): void
    {
        if (empty($this->questions)) {
            throw InvalidQuestionnaireException::noQuestions();
        }
    }

    // =====================================
    // Getters
    // =====================================

    public function getAggregateId(): AggregateId
    {
        return $this->id;
    }

    public function getId(): QuestionnaireId
    {
        return $this->id;
    }

    public function getTitle(): QuestionnaireTitle
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getSlug(): QuestionnaireSlug
    {
        return $this->slug;
    }

    public function getStatus(): QuestionnaireStatus
    {
        return $this->status;
    }

    public function getSettings(): QuestionnaireSettings
    {
        return $this->settings;
    }

    public function getDateRange(): ?DateRange
    {
        return $this->dateRange;
    }

    public function getOwnerId(): ?UserId
    {
        return $this->ownerId;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getClosedAt(): ?DateTimeImmutable
    {
        return $this->closedAt;
    }

    /** @return array<Question> */
    public function getQuestions(): array
    {
        return array_values($this->questions);
    }
}
```

### 3.5 Specifications

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Specification;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Shared\Specification\Specification;

final class QuestionnaireIsActiveSpec extends Specification
{
    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof Questionnaire) {
            return false;
        }

        return $candidate->isAcceptingResponses();
    }

    public function getUnsatisfiedReason(object $candidate): ?string
    {
        if ($this->isSatisfiedBy($candidate)) {
            return null;
        }

        if (!$candidate instanceof Questionnaire) {
            return 'Candidate is not a Questionnaire';
        }

        return 'Questionnaire is not currently accepting responses';
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Specification;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Shared\Specification\Specification;

final class QuestionnaireHasQuestionsSpec extends Specification
{
    public function __construct(
        private readonly int $minimumQuestions = 1
    ) {}

    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof Questionnaire) {
            return false;
        }

        return $candidate->getQuestionCount() >= $this->minimumQuestions;
    }

    public function getUnsatisfiedReason(object $candidate): ?string
    {
        if ($this->isSatisfiedBy($candidate)) {
            return null;
        }

        if (!$candidate instanceof Questionnaire) {
            return 'Candidate is not a Questionnaire';
        }

        return sprintf(
            'Questionnaire must have at least %d question(s), but has %d',
            $this->minimumQuestions,
            $candidate->getQuestionCount()
        );
    }
}
```

---

## 4. Response 聚合設計

Response 聚合的設計遵循相同的原則，包含：

- **Aggregate**: `Response`
- **Entity**: `Answer`
- **Value Objects**: `ResponseId`, `AnswerId`, `AnswerValue`, `RespondentInfo`, `SubmissionMetadata`
- **Events**: `ResponseSubmitted`, `ResponseValidated`, `ResponseRejected`
- **Specifications**: `ResponseIsCompleteSpec`, `ResponseIsValidSpec`
- **Guards**: `AllowMultipleGuard`, `OnePerUserGuard`, `OnePerSessionGuard`, `OnePerIpGuard`

詳細實作請參考 [06-event-sourcing.md](./06-event-sourcing.md)。

---

## 5. Domain Exceptions

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Exception;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Shared\Exception\DomainException;

final class InvalidQuestionnaireException extends DomainException
{
    public static function emptyTitle(): self
    {
        return new self('Questionnaire title cannot be empty.');
    }

    public static function titleTooLong(int $maxLength): self
    {
        return new self(sprintf('Questionnaire title cannot exceed %d characters.', $maxLength));
    }

    public static function invalidDateRange(): self
    {
        return new self('Start date must be before end date.');
    }

    public static function noQuestions(): self
    {
        return new self('Cannot publish questionnaire without questions.');
    }

    public static function questionAlreadyExists(string $questionId): self
    {
        return new self(sprintf('Question with ID %s already exists.', $questionId));
    }

    public static function questionNotFound(string $questionId): self
    {
        return new self(sprintf('Question with ID %s not found.', $questionId));
    }

    public static function cannotPublishNonDraft(QuestionnaireStatus $currentStatus): self
    {
        return new self(sprintf(
            'Cannot publish questionnaire with status "%s". Only draft questionnaires can be published.',
            $currentStatus->value
        ));
    }

    public static function cannotModifyQuestionsAfterPublish(): self
    {
        return new self('Cannot modify questions after questionnaire has been published.');
    }
}
```

---

**下一篇**: [03-application-layer.md](./03-application-layer.md) - Application 層（CQRS）設計
