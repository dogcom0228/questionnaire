# Infrastructure 層重構計劃

> **文件**: 04-infrastructure-layer.md  
> **上一篇**: [03-application-layer.md](./03-application-layer.md)  
> **下一篇**: [05-contracts.md](./05-contracts.md)

---

## 1. Infrastructure 層職責

Infrastructure 層負責所有與外部世界的交互：

- **HTTP 層** - Controllers, Requests, Resources, Middleware
- **持久化** - Repository 實作, Read Models (Eloquent)
- **事件儲存** - Event Store 實作
- **外部服務** - Mail, Export, 第三方 API
- **Console** - Artisan Commands
- **Bus 實作** - Command/Query/Event Bus

---

## 2. HTTP 層設計

### 2.1 Controller 拆分策略

將原本臃腫的 `QuestionnaireController` 拆分為：

| 控制器 | 職責 | 方法 |
|--------|------|------|
| `QuestionnaireCommandController` | 問卷寫入操作 | store, update, destroy, publish, close |
| `QuestionCommandController` | 問題寫入操作 | store, update, destroy |
| `ResponseCommandController` | 回應寫入操作 | store |
| `QuestionnaireQueryController` | 問卷讀取操作 | index, show, public |
| `ResponseQueryController` | 回應讀取操作 | index, statistics, export |

### 2.2 Command Controllers

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Http\Controller\Command;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Liangjin0228\Questionnaire\Application\Command\Questionnaire\CloseQuestionnaire\CloseQuestionnaireCommand;
use Liangjin0228\Questionnaire\Application\Command\Questionnaire\CreateQuestionnaire\CreateQuestionnaireCommand;
use Liangjin0228\Questionnaire\Application\Command\Questionnaire\DeleteQuestionnaire\DeleteQuestionnaireCommand;
use Liangjin0228\Questionnaire\Application\Command\Questionnaire\PublishQuestionnaire\PublishQuestionnaireCommand;
use Liangjin0228\Questionnaire\Application\Command\Questionnaire\UpdateQuestionnaire\UpdateQuestionnaireCommand;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\CommandBusInterface;
use Liangjin0228\Questionnaire\Infrastructure\Http\Request\StoreQuestionnaireRequest;
use Liangjin0228\Questionnaire\Infrastructure\Http\Request\UpdateQuestionnaireRequest;

final class QuestionnaireCommandController extends Controller
{
    public function __construct(
        private readonly CommandBusInterface $commandBus
    ) {
        // 授權中間件
        if (config('questionnaire.features.authorization', true)) {
            $this->middleware('can:create,questionnaire')->only('store');
            $this->middleware('can:update,questionnaire')->only('update');
            $this->middleware('can:delete,questionnaire')->only('destroy');
            $this->middleware('can:publish,questionnaire')->only('publish');
            $this->middleware('can:close,questionnaire')->only('close');
        }
    }

    /**
     * 創建問卷
     */
    public function store(StoreQuestionnaireRequest $request): JsonResponse
    {
        $id = $this->commandBus->dispatch(
            new CreateQuestionnaireCommand(
                input: $request->toDto(),
                userId: $request->user()?->getKey()
            )
        );

        return response()->json([
            'data' => ['id' => $id],
            'message' => 'Questionnaire created successfully.',
        ], 201);
    }

    /**
     * 更新問卷
     */
    public function update(UpdateQuestionnaireRequest $request, string $questionnaire): JsonResponse
    {
        $this->commandBus->dispatch(
            new UpdateQuestionnaireCommand(
                id: $questionnaire,
                input: $request->toDto()
            )
        );

        return response()->json([
            'message' => 'Questionnaire updated successfully.',
        ]);
    }

    /**
     * 刪除問卷
     */
    public function destroy(string $questionnaire): JsonResponse
    {
        $this->commandBus->dispatch(
            new DeleteQuestionnaireCommand(id: $questionnaire)
        );

        return response()->json([
            'message' => 'Questionnaire deleted successfully.',
        ]);
    }

    /**
     * 發布問卷
     */
    public function publish(string $questionnaire): JsonResponse
    {
        $this->commandBus->dispatch(
            new PublishQuestionnaireCommand(id: $questionnaire)
        );

        return response()->json([
            'message' => 'Questionnaire published successfully.',
        ]);
    }

    /**
     * 關閉問卷
     */
    public function close(string $questionnaire): JsonResponse
    {
        $this->commandBus->dispatch(
            new CloseQuestionnaireCommand(id: $questionnaire)
        );

        return response()->json([
            'message' => 'Questionnaire closed successfully.',
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Http\Controller\Command;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Liangjin0228\Questionnaire\Application\Command\Response\SubmitResponse\SubmitResponseCommand;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\CommandBusInterface;
use Liangjin0228\Questionnaire\Domain\Response\Exception\DuplicateSubmissionException;
use Liangjin0228\Questionnaire\Domain\Response\Exception\ValidationException;
use Liangjin0228\Questionnaire\Infrastructure\Http\Request\SubmitResponseRequest;

final class ResponseCommandController extends Controller
{
    public function __construct(
        private readonly CommandBusInterface $commandBus
    ) {}

    /**
     * 提交回應
     */
    public function store(SubmitResponseRequest $request, string $questionnaire): JsonResponse
    {
        try {
            $id = $this->commandBus->dispatch(
                new SubmitResponseCommand(
                    questionnaireId: $questionnaire,
                    input: $request->toDto(),
                    userId: $request->user()?->getKey(),
                    sessionId: $request->session()->getId(),
                    ipAddress: $request->ip()
                )
            );

            return response()->json([
                'data' => ['id' => $id],
                'message' => 'Response submitted successfully.',
            ], 201);

        } catch (DuplicateSubmissionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->getErrors(),
            ], 422);
        }
    }
}
```

### 2.3 Query Controllers

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Http\Controller\Query;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Liangjin0228\Questionnaire\Application\Query\Questionnaire\GetQuestionnaire\GetQuestionnaireQuery;
use Liangjin0228\Questionnaire\Application\Query\Questionnaire\GetQuestionnaireForFilling\GetQuestionnaireForFillingQuery;
use Liangjin0228\Questionnaire\Application\Query\Questionnaire\GetQuestionTypes\GetQuestionTypesQuery;
use Liangjin0228\Questionnaire\Application\Query\Questionnaire\ListQuestionnaires\ListQuestionnairesQuery;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\QueryBusInterface;
use Liangjin0228\Questionnaire\Infrastructure\Http\Resource\QuestionnaireResource;

final class QuestionnaireQueryController extends Controller
{
    public function __construct(
        private readonly QueryBusInterface $queryBus
    ) {}

    /**
     * 列出問卷
     */
    public function index(Request $request): JsonResponse
    {
        $result = $this->queryBus->dispatch(
            new ListQuestionnairesQuery(
                userId: config('questionnaire.features.authorization', true) 
                    ? $request->user()?->getKey() 
                    : null,
                status: $request->input('status'),
                search: $request->input('search'),
                page: (int) $request->input('page', 1),
                perPage: (int) $request->input('per_page', 15),
                sortBy: $request->input('sort_by', 'created_at'),
                sortDirection: $request->input('sort_direction', 'desc')
            )
        );

        return response()->json([
            'data' => $result->items,
            'meta' => [
                'total' => $result->total,
                'page' => $result->page,
                'per_page' => $result->perPage,
                'last_page' => $result->lastPage,
            ],
        ]);
    }

    /**
     * 顯示單一問卷
     */
    public function show(Request $request, string $questionnaire): JsonResponse
    {
        $result = $this->queryBus->dispatch(
            new GetQuestionnaireQuery(
                id: $questionnaire,
                includeQuestions: true
            )
        );

        if ($result === null) {
            return response()->json([
                'message' => 'Questionnaire not found.',
            ], 404);
        }

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * 取得公開問卷（供填寫）
     */
    public function public(string $questionnaire): JsonResponse
    {
        $result = $this->queryBus->dispatch(
            new GetQuestionnaireForFillingQuery(id: $questionnaire)
        );

        if ($result === null) {
            return response()->json([
                'message' => 'Questionnaire not found or not available.',
            ], 404);
        }

        if (!$result->isAcceptingResponses) {
            return response()->json([
                'message' => 'This questionnaire is not accepting responses.',
            ], 403);
        }

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * 取得問題類型列表
     */
    public function questionTypes(): JsonResponse
    {
        $result = $this->queryBus->dispatch(new GetQuestionTypesQuery());

        return response()->json([
            'data' => $result,
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Http\Controller\Query;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Liangjin0228\Questionnaire\Application\Query\Response\GetResponses\GetResponsesQuery;
use Liangjin0228\Questionnaire\Application\Query\Response\GetStatistics\GetStatisticsQuery;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\QueryBusInterface;
use Liangjin0228\Questionnaire\Contracts\Infrastructure\ExporterInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ResponseQueryController extends Controller
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly ?ExporterInterface $exporter = null
    ) {}

    /**
     * 列出問卷回應
     */
    public function index(Request $request, string $questionnaire): JsonResponse
    {
        $result = $this->queryBus->dispatch(
            new GetResponsesQuery(
                questionnaireId: $questionnaire,
                page: (int) $request->input('page', 1),
                perPage: (int) $request->input('per_page', 15)
            )
        );

        return response()->json([
            'data' => $result->items,
            'meta' => [
                'total' => $result->total,
                'page' => $result->page,
                'per_page' => $result->perPage,
                'last_page' => $result->lastPage,
            ],
        ]);
    }

    /**
     * 取得問卷統計
     */
    public function statistics(string $questionnaire): JsonResponse
    {
        $result = $this->queryBus->dispatch(
            new GetStatisticsQuery(questionnaireId: $questionnaire)
        );

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * 匯出回應
     */
    public function export(Request $request, string $questionnaire): StreamedResponse
    {
        if ($this->exporter === null) {
            abort(503, 'Export feature is not available.');
        }

        $format = $request->input('format', 'csv');

        return $this->exporter->export($questionnaire, $format);
    }
}
```

### 2.4 Request 類

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Liangjin0228\Questionnaire\Application\DTO\Input\QuestionInput;
use Liangjin0228\Questionnaire\Application\DTO\Input\QuestionnaireInput;

final class StoreQuestionnaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 授權邏輯在 Controller 中處理
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'settings' => ['nullable', 'array'],
            'settings.requires_auth' => ['nullable', 'boolean'],
            'settings.submission_limit' => ['nullable', 'integer', 'min:1'],
            'settings.duplicate_submission_strategy' => ['nullable', 'string', 'in:allow_multiple,one_per_user,one_per_session,one_per_ip'],
            'questions' => ['nullable', 'array'],
            'questions.*.type' => ['required', 'string'],
            'questions.*.content' => ['required', 'string', 'max:1000'],
            'questions.*.order' => ['nullable', 'integer', 'min:0'],
            'questions.*.is_required' => ['nullable', 'boolean'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.settings' => ['nullable', 'array'],
        ];
    }

    public function toDto(): QuestionnaireInput
    {
        $questions = collect($this->input('questions', []))
            ->map(fn (array $q, int $index) => new QuestionInput(
                type: $q['type'],
                content: $q['content'],
                order: $q['order'] ?? $index,
                isRequired: $q['is_required'] ?? false,
                options: $q['options'] ?? null,
                settings: $q['settings'] ?? null
            ))
            ->all();

        return new QuestionnaireInput(
            title: $this->input('title'),
            description: $this->input('description'),
            questions: $questions,
            settings: $this->input('settings'),
            startsAt: $this->input('starts_at'),
            endsAt: $this->input('ends_at')
        );
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Liangjin0228\Questionnaire\Application\DTO\Input\SubmitResponseInput;

final class SubmitResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable'],
            'email' => ['nullable', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDto(): SubmitResponseInput
    {
        return new SubmitResponseInput(
            answers: $this->input('answers'),
            email: $this->input('email'),
            name: $this->input('name')
        );
    }
}
```

### 2.5 路由設計

```php
<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use Liangjin0228\Questionnaire\Infrastructure\Http\Controller\Command\QuestionnaireCommandController;
use Liangjin0228\Questionnaire\Infrastructure\Http\Controller\Command\QuestionCommandController;
use Liangjin0228\Questionnaire\Infrastructure\Http\Controller\Command\ResponseCommandController;
use Liangjin0228\Questionnaire\Infrastructure\Http\Controller\Query\QuestionnaireQueryController;
use Liangjin0228\Questionnaire\Infrastructure\Http\Controller\Query\ResponseQueryController;

// 公開路由
Route::get('/question-types', [QuestionnaireQueryController::class, 'questionTypes'])
    ->name('question-types');

Route::get('/public/{questionnaire}', [QuestionnaireQueryController::class, 'public'])
    ->name('public.show');

Route::post('/public/{questionnaire}/submit', [ResponseCommandController::class, 'store'])
    ->name('public.submit');

// 需認證的路由
Route::middleware(config('questionnaire.routes.api_middleware', ['api']))->group(function () {
    // Questionnaire 管理
    Route::get('/', [QuestionnaireQueryController::class, 'index'])
        ->name('index');
    
    Route::post('/', [QuestionnaireCommandController::class, 'store'])
        ->name('store');
    
    Route::get('/{questionnaire}', [QuestionnaireQueryController::class, 'show'])
        ->name('show');
    
    Route::put('/{questionnaire}', [QuestionnaireCommandController::class, 'update'])
        ->name('update');
    
    Route::delete('/{questionnaire}', [QuestionnaireCommandController::class, 'destroy'])
        ->name('destroy');
    
    Route::post('/{questionnaire}/publish', [QuestionnaireCommandController::class, 'publish'])
        ->name('publish');
    
    Route::post('/{questionnaire}/close', [QuestionnaireCommandController::class, 'close'])
        ->name('close');

    // Question 管理
    Route::post('/{questionnaire}/questions', [QuestionCommandController::class, 'store'])
        ->name('questions.store');
    
    Route::put('/{questionnaire}/questions/{question}', [QuestionCommandController::class, 'update'])
        ->name('questions.update');
    
    Route::delete('/{questionnaire}/questions/{question}', [QuestionCommandController::class, 'destroy'])
        ->name('questions.destroy');

    // Response 查詢
    Route::get('/{questionnaire}/responses', [ResponseQueryController::class, 'index'])
        ->name('responses.index');
    
    Route::get('/{questionnaire}/statistics', [ResponseQueryController::class, 'statistics'])
        ->name('responses.statistics');
    
    Route::get('/{questionnaire}/export', [ResponseQueryController::class, 'export'])
        ->name('responses.export');
});
```

---

## 3. Persistence 層設計

### 3.1 Event Sourced Repository

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Persistence\Repository;

use Liangjin0228\Questionnaire\Contracts\Domain\Repository\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Contracts\Infrastructure\EventStoreInterface;
use Liangjin0228\Questionnaire\Contracts\Infrastructure\SnapshotStoreInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;

final class EventSourcedQuestionnaireRepository implements QuestionnaireRepositoryInterface
{
    private const SNAPSHOT_THRESHOLD = 10;

    public function __construct(
        private readonly EventStoreInterface $eventStore,
        private readonly ?SnapshotStoreInterface $snapshotStore = null
    ) {}

    public function get(QuestionnaireId $id): Questionnaire
    {
        $aggregateId = (string) $id;

        // 嘗試從快照恢復
        $snapshot = $this->snapshotStore?->get($aggregateId);
        $fromVersion = 0;
        
        if ($snapshot !== null) {
            $questionnaire = $snapshot->getAggregate();
            $fromVersion = $snapshot->getVersion();
        } else {
            $questionnaire = null;
        }

        // 載入快照後的事件
        $events = $this->eventStore->getEventsForAggregate($aggregateId, $fromVersion);

        if ($questionnaire === null) {
            if (empty($events)) {
                throw new \RuntimeException(
                    sprintf('Questionnaire not found: %s', $aggregateId)
                );
            }
            return Questionnaire::reconstituteFromHistory($events);
        }

        // 重播快照後的事件
        foreach ($events as $event) {
            $questionnaire->applyEvent($event);
        }

        return $questionnaire;
    }

    public function save(Questionnaire $questionnaire): void
    {
        $events = $questionnaire->releaseEvents();
        
        if (empty($events)) {
            return;
        }

        $aggregateId = (string) $questionnaire->getAggregateId();
        $expectedVersion = $questionnaire->getAggregateVersion() - count($events);

        // 保存事件到 Event Store
        $this->eventStore->append($aggregateId, $events, $expectedVersion);

        // 檢查是否需要創建快照
        if ($this->snapshotStore !== null && $questionnaire->getAggregateVersion() % self::SNAPSHOT_THRESHOLD === 0) {
            $this->snapshotStore->save($aggregateId, $questionnaire, $questionnaire->getAggregateVersion());
        }
    }

    public function exists(QuestionnaireId $id): bool
    {
        return $this->eventStore->hasEventsForAggregate((string) $id);
    }
}
```

### 3.2 Read Models (Eloquent)

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Persistence\ReadModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 問卷讀取模型（投影表）
 * 
 * @property string $id
 * @property string $title
 * @property string|null $description
 * @property string $slug
 * @property string $status
 * @property string|null $user_id
 * @property array|null $settings
 * @property \Carbon\Carbon|null $starts_at
 * @property \Carbon\Carbon|null $ends_at
 * @property \Carbon\Carbon|null $published_at
 * @property \Carbon\Carbon|null $closed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class QuestionnaireModel extends Model
{
    use SoftDeletes;

    protected $table = 'questionnaires';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'description',
        'slug',
        'status',
        'user_id',
        'settings',
        'starts_at',
        'ends_at',
        'published_at',
        'closed_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('questionnaire.table_names.questionnaires', 'questionnaires');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionModel::class, 'questionnaire_id')->orderBy('order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ResponseModel::class, 'questionnaire_id');
    }

    public function user(): BelongsTo
    {
        $userModel = config('questionnaire.models.user') 
            ?? config('auth.providers.users.model') 
            ?? 'App\\Models\\User';

        return $this->belongsTo($userModel);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeActive($query)
    {
        return $query->published()
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Persistence\ReadModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionModel extends Model
{
    protected $table = 'questions';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'questionnaire_id',
        'type',
        'content',
        'order',
        'is_required',
        'options',
        'settings',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
        'settings' => 'array',
    ];

    public function getTable(): string
    {
        return config('questionnaire.table_names.questions', 'questions');
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireModel::class, 'questionnaire_id');
    }
}
```

---

## 4. Event Store 實作

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\EventStore;

use Illuminate\Database\Connection;
use Liangjin0228\Questionnaire\Contracts\Infrastructure\EventStoreInterface;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

final class EloquentEventStore implements EventStoreInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly EventSerializer $serializer,
        private readonly string $table = 'stored_events'
    ) {}

    public function append(string $aggregateId, array $events, int $expectedVersion): void
    {
        $this->connection->transaction(function () use ($aggregateId, $events, $expectedVersion) {
            // 樂觀鎖定檢查
            $currentVersion = $this->getCurrentVersion($aggregateId);
            
            if ($currentVersion !== $expectedVersion) {
                throw new ConcurrencyException(
                    sprintf(
                        'Concurrency conflict for aggregate %s. Expected version %d, but found %d.',
                        $aggregateId,
                        $expectedVersion,
                        $currentVersion
                    )
                );
            }

            $version = $expectedVersion;

            foreach ($events as $event) {
                $version++;
                
                $this->connection->table($this->table)->insert([
                    'id' => $event->getEventId()->toString(),
                    'aggregate_id' => $aggregateId,
                    'aggregate_type' => $this->getAggregateType($event),
                    'event_type' => get_class($event),
                    'payload' => $this->serializer->serialize($event),
                    'metadata' => json_encode([
                        'occurred_at' => $event->getOccurredAt()->format('c'),
                    ]),
                    'version' => $version,
                    'created_at' => now(),
                ]);
            }
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

    private function getCurrentVersion(string $aggregateId): int
    {
        return (int) $this->connection->table($this->table)
            ->where('aggregate_id', $aggregateId)
            ->max('version') ?? 0;
    }

    private function getAggregateType(DomainEvent $event): string
    {
        $namespace = get_class($event);
        
        if (str_contains($namespace, 'Questionnaire\\Event')) {
            return 'Questionnaire';
        }
        
        if (str_contains($namespace, 'Response\\Event')) {
            return 'Response';
        }

        return 'Unknown';
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\EventStore;

use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;
use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateId;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

final class EventSerializer
{
    public function serialize(DomainEvent $event): string
    {
        return json_encode($event->toPayload(), JSON_THROW_ON_ERROR);
    }

    public function deserialize(string $eventType, string $payload, string $aggregateId): DomainEvent
    {
        $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        if (!class_exists($eventType)) {
            throw new \RuntimeException(sprintf('Unknown event type: %s', $eventType));
        }

        $aggregateIdObject = $this->resolveAggregateId($eventType, $aggregateId);

        return $eventType::fromPayload($data, $aggregateIdObject);
    }

    private function resolveAggregateId(string $eventType, string $aggregateId): AggregateId
    {
        if (str_contains($eventType, 'Questionnaire')) {
            return QuestionnaireId::fromString($aggregateId);
        }

        if (str_contains($eventType, 'Response')) {
            return ResponseId::fromString($aggregateId);
        }

        throw new \RuntimeException(sprintf('Cannot resolve aggregate ID type for event: %s', $eventType));
    }
}
```

---

## 5. Console Commands

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Console;

use Illuminate\Console\Command;
use Liangjin0228\Questionnaire\Application\Projector\QuestionnaireProjector;
use Liangjin0228\Questionnaire\Application\Projector\ResponseProjector;
use Liangjin0228\Questionnaire\Contracts\Infrastructure\EventStoreInterface;

final class RebuildProjectionsCommand extends Command
{
    protected $signature = 'questionnaire:rebuild-projections 
                            {--projector= : Specific projector to rebuild}
                            {--batch=1000 : Number of events per batch}';

    protected $description = 'Rebuild read model projections from event store';

    public function handle(
        EventStoreInterface $eventStore,
        QuestionnaireProjector $questionnaireProjector,
        ResponseProjector $responseProjector
    ): int {
        $this->info('Rebuilding projections...');

        $projector = $this->option('projector');
        $batchSize = (int) $this->option('batch');

        // 清除現有投影
        if ($projector === null || $projector === 'questionnaire') {
            $this->truncateTable('questionnaires');
            $this->truncateTable('questions');
        }

        if ($projector === null || $projector === 'response') {
            $this->truncateTable('questionnaire_responses');
            $this->truncateTable('questionnaire_answers');
        }

        // 重播所有事件
        $events = $eventStore->getAllEvents(limit: $batchSize);
        $bar = $this->output->createProgressBar(count($events));

        foreach ($events as $event) {
            $eventType = get_class($event);

            // Questionnaire 投影
            $questionnaireEvents = $questionnaireProjector->getSubscribedEvents();
            if (isset($questionnaireEvents[$eventType])) {
                $method = $questionnaireEvents[$eventType];
                $questionnaireProjector->{$method}($event);
            }

            // Response 投影
            $responseEvents = $responseProjector->getSubscribedEvents();
            if (isset($responseEvents[$eventType])) {
                $method = $responseEvents[$eventType];
                $responseProjector->{$method}($event);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Projections rebuilt successfully.');

        return self::SUCCESS;
    }

    private function truncateTable(string $table): void
    {
        $tableName = config("questionnaire.table_names.{$table}", $table);
        \DB::table($tableName)->truncate();
        $this->line("Truncated table: {$tableName}");
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Console;

use Illuminate\Console\Command;
use Liangjin0228\Questionnaire\Contracts\Infrastructure\EventStoreInterface;
use Liangjin0228\Questionnaire\Contracts\Infrastructure\SnapshotStoreInterface;

final class CreateSnapshotCommand extends Command
{
    protected $signature = 'questionnaire:create-snapshot 
                            {aggregate-id : The aggregate ID to snapshot}
                            {--type=questionnaire : The aggregate type (questionnaire or response)}';

    protected $description = 'Create a snapshot for an aggregate';

    public function handle(
        EventStoreInterface $eventStore,
        SnapshotStoreInterface $snapshotStore
    ): int {
        $aggregateId = $this->argument('aggregate-id');
        $type = $this->option('type');

        $this->info("Creating snapshot for {$type}: {$aggregateId}");

        // 載入並重建聚合
        $events = $eventStore->getEventsForAggregate($aggregateId);
        
        if (empty($events)) {
            $this->error('No events found for this aggregate.');
            return self::FAILURE;
        }

        $aggregateClass = match ($type) {
            'questionnaire' => \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::class,
            'response' => \Liangjin0228\Questionnaire\Domain\Response\Aggregate\Response::class,
            default => throw new \InvalidArgumentException("Unknown aggregate type: {$type}"),
        };

        $aggregate = $aggregateClass::reconstituteFromHistory($events);

        // 保存快照
        $snapshotStore->save($aggregateId, $aggregate, $aggregate->getAggregateVersion());

        $this->info("Snapshot created at version {$aggregate->getAggregateVersion()}");

        return self::SUCCESS;
    }
}
```

---

## 6. Service Provider 更新

```php
<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire;

use Illuminate\Support\ServiceProvider;
use Liangjin0228\Questionnaire\Application\Projector\QuestionnaireProjector;
use Liangjin0228\Questionnaire\Application\Projector\ResponseProjector;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\CommandBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\EventBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\QueryBusInterface;
use Liangjin0228\Questionnaire\Contracts\Domain\Repository\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Contracts\Domain\Repository\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Contracts\Infrastructure\EventStoreInterface;
use Liangjin0228\Questionnaire\Contracts\Infrastructure\SnapshotStoreInterface;
use Liangjin0228\Questionnaire\Infrastructure\Bus\IlluminateCommandBus;
use Liangjin0228\Questionnaire\Infrastructure\Bus\IlluminateEventBus;
use Liangjin0228\Questionnaire\Infrastructure\Bus\IlluminateQueryBus;
use Liangjin0228\Questionnaire\Infrastructure\EventStore\EloquentEventStore;
use Liangjin0228\Questionnaire\Infrastructure\EventStore\Snapshot\EloquentSnapshotStore;
use Liangjin0228\Questionnaire\Infrastructure\Persistence\Repository\EventSourcedQuestionnaireRepository;
use Liangjin0228\Questionnaire\Infrastructure\Persistence\Repository\EventSourcedResponseRepository;

final class QuestionnaireServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/questionnaire.php', 'questionnaire');

        $this->registerBuses();
        $this->registerEventStore();
        $this->registerRepositories();
        $this->registerProjectors();
    }

    public function boot(): void
    {
        $this->bootPublishing();
        $this->bootRoutes();
        $this->bootMigrations();
        $this->bootCommands();
        $this->bootEventListeners();
    }

    private function registerBuses(): void
    {
        $this->app->singleton(CommandBusInterface::class, IlluminateCommandBus::class);
        $this->app->singleton(QueryBusInterface::class, IlluminateQueryBus::class);
        $this->app->singleton(EventBusInterface::class, IlluminateEventBus::class);
    }

    private function registerEventStore(): void
    {
        $this->app->singleton(EventStoreInterface::class, function ($app) {
            return new EloquentEventStore(
                connection: $app['db']->connection(),
                serializer: $app->make(\Liangjin0228\Questionnaire\Infrastructure\EventStore\EventSerializer::class),
                table: config('questionnaire.table_names.stored_events', 'stored_events')
            );
        });

        if (config('questionnaire.features.snapshots', true)) {
            $this->app->singleton(SnapshotStoreInterface::class, EloquentSnapshotStore::class);
        }
    }

    private function registerRepositories(): void
    {
        $this->app->bind(
            QuestionnaireRepositoryInterface::class,
            EventSourcedQuestionnaireRepository::class
        );

        $this->app->bind(
            ResponseRepositoryInterface::class,
            EventSourcedResponseRepository::class
        );
    }

    private function registerProjectors(): void
    {
        $this->app->singleton(QuestionnaireProjector::class);
        $this->app->singleton(ResponseProjector::class);
    }

    private function bootEventListeners(): void
    {
        $events = $this->app['events'];

        // 註冊投影器監聽器
        $questionnaireProjector = $this->app->make(QuestionnaireProjector::class);
        foreach ($questionnaireProjector->getSubscribedEvents() as $eventClass => $method) {
            $events->listen($eventClass, fn ($event) => $questionnaireProjector->{$method}($event));
        }

        $responseProjector = $this->app->make(ResponseProjector::class);
        foreach ($responseProjector->getSubscribedEvents() as $eventClass => $method) {
            $events->listen($eventClass, fn ($event) => $responseProjector->{$method}($event));
        }

        // 其他監聽器（通知、日誌等）
        if (config('questionnaire.features.log_submissions', false)) {
            $events->listen(
                \Liangjin0228\Questionnaire\Domain\Response\Event\ResponseSubmitted::class,
                \Liangjin0228\Questionnaire\Application\Listener\LogResponseSubmission::class
            );
        }

        if (config('questionnaire.features.email_notifications', false)) {
            $events->listen(
                \Liangjin0228\Questionnaire\Domain\Response\Event\ResponseSubmitted::class,
                \Liangjin0228\Questionnaire\Application\Listener\SendResponseNotification::class
            );
        }
    }

    private function bootCommands(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            \Liangjin0228\Questionnaire\Infrastructure\Console\InstallCommand::class,
            \Liangjin0228\Questionnaire\Infrastructure\Console\ListQuestionTypesCommand::class,
            \Liangjin0228\Questionnaire\Infrastructure\Console\RebuildProjectionsCommand::class,
            \Liangjin0228\Questionnaire\Infrastructure\Console\CreateSnapshotCommand::class,
            \Liangjin0228\Questionnaire\Infrastructure\Console\ReplayEventsCommand::class,
        ]);
    }

    // ... 其他 boot 方法保持不變
}
```

---

**下一篇**: [05-contracts.md](./05-contracts.md) - 介面與契約重新設計
