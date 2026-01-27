# 遷移指南

> **文件**: 08-migration-guide.md  
> **上一篇**: [07-dependencies.md](./07-dependencies.md)  
> **下一篇**: [09-testing-strategy.md](./09-testing-strategy.md)

---

## 1. 遷移概述

### 1.1 遷移策略

由於這是一次 **Major 版本升級**（v1.x → v2.0），我們採用 **分階段遷移** 策略：

```
┌─────────────────────────────────────────────────────────────┐
│                      遷移階段                                │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Phase 1: 基礎設施 (Foundation)                             │
│     ├── 更新依賴套件                                        │
│     ├── 設置開發工具 (PHPStan, Pest)                        │
│     └── 創建新目錄結構                                      │
│                                                             │
│  Phase 2: Domain 層 (Core)                                  │
│     ├── 創建共享核心 (Shared)                               │
│     ├── 實作 Value Objects                                  │
│     ├── 重構 Aggregates                                     │
│     └── 重構 Domain Events                                  │
│                                                             │
│  Phase 3: Application 層 (CQRS)                             │
│     ├── 實作 Command Bus / Query Bus                        │
│     ├── 創建 Commands 和 Handlers                           │
│     ├── 創建 Queries 和 Handlers                            │
│     └── 實作 Projectors                                     │
│                                                             │
│  Phase 4: Event Sourcing                                    │
│     ├── 設置 Event Store                                    │
│     ├── 遷移現有資料                                        │
│     └── 設置快照機制                                        │
│                                                             │
│  Phase 5: Infrastructure 層                                  │
│     ├── 拆分 Controllers                                    │
│     ├── 更新 Routes                                         │
│     └── 更新 ServiceProvider                                │
│                                                             │
│  Phase 6: 測試和清理                                        │
│     ├── 更新所有測試                                        │
│     ├── 移除舊代碼                                          │
│     └── 更新文檔                                            │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 1.2 時間估算

| 階段 | 預估時間 | 累計時間 |
|------|----------|----------|
| Phase 1 | 16h | 16h |
| Phase 2 | 24h | 40h |
| Phase 3 | 20h | 60h |
| Phase 4 | 16h | 76h |
| Phase 5 | 12h | 88h |
| Phase 6 | 12h | 100h |

---

## 2. Phase 1: 基礎設施

### 2.1 更新依賴

```bash
# 1. 備份現有 composer.json
cp composer.json composer.json.bak

# 2. 更新 composer.json（參考 07-dependencies.md）

# 3. 更新依賴
composer update

# 4. 安裝新開發工具
./vendor/bin/pest --init
```

### 2.2 創建配置文件

```bash
# 創建 PHPStan 配置
touch phpstan.neon

# 創建 Rector 配置
touch rector.php

# 更新 Pint 配置
# 修改 pint.json
```

### 2.3 創建新目錄結構

```bash
# 創建 Domain 層目錄
mkdir -p src/Domain/Shared/{Aggregate,Entity,ValueObject,Event,Specification,Exception}
mkdir -p src/Domain/Questionnaire/{Aggregate,Entity,ValueObject,Enum,Event,Specification,Factory,Service,QuestionType,Exception}
mkdir -p src/Domain/Response/{Aggregate,Entity,ValueObject,Event,Specification,Guard,Submission/Pipe,Service,Exception}
mkdir -p src/Domain/User/ValueObject

# 創建 Application 層目錄
mkdir -p src/Application/Bus
mkdir -p src/Application/Command/Questionnaire/{CreateQuestionnaire,UpdateQuestionnaire,PublishQuestionnaire,CloseQuestionnaire,AddQuestion,UpdateQuestion,RemoveQuestion}
mkdir -p src/Application/Command/Response/SubmitResponse
mkdir -p src/Application/Query/Questionnaire/{GetQuestionnaire,ListQuestionnaires,GetQuestionnaireForFilling,GetQuestionTypes}
mkdir -p src/Application/Query/Response/{GetResponses,GetStatistics}
mkdir -p src/Application/DTO/{Input,Output}
mkdir -p src/Application/Projector
mkdir -p src/Application/ReadModel
mkdir -p src/Application/Mapper
mkdir -p src/Application/Listener

# 創建 Infrastructure 層目錄
mkdir -p src/Infrastructure/Http/Controller/{Command,Query}
mkdir -p src/Infrastructure/Http/{Request,Resource,Middleware}
mkdir -p src/Infrastructure/Persistence/{Repository,ReadModel,Factory}
mkdir -p src/Infrastructure/EventStore/Snapshot
mkdir -p src/Infrastructure/Bus
mkdir -p src/Infrastructure/Export
mkdir -p src/Infrastructure/Mail
mkdir -p src/Infrastructure/Console

# 創建 Contracts 目錄
mkdir -p src/Contracts/Domain/Repository
mkdir -p src/Contracts/Application/Bus
mkdir -p src/Contracts/Infrastructure

# 創建 Policy 目錄
mkdir -p src/Policy
```

### 2.4 驗證步驟

```bash
# 確認目錄結構
find src -type d | head -50

# 執行 PHPStan（預期會有錯誤，這是正常的）
./vendor/bin/phpstan analyse --level=0

# 確認 Pest 正常運作
./vendor/bin/pest --version
```

---

## 3. Phase 2: Domain 層

### 3.1 創建共享核心

按照 [02-domain-layer.md](./02-domain-layer.md) 的設計，依序創建：

1. `src/Domain/Shared/Aggregate/AggregateRoot.php`
2. `src/Domain/Shared/Aggregate/AggregateId.php`
3. `src/Domain/Shared/Entity/Entity.php`
4. `src/Domain/Shared/ValueObject/ValueObject.php`
5. `src/Domain/Shared/Event/DomainEvent.php`
6. `src/Domain/Shared/Specification/Specification.php`
7. `src/Domain/Shared/Specification/AndSpecification.php`
8. `src/Domain/Shared/Specification/OrSpecification.php`
9. `src/Domain/Shared/Specification/NotSpecification.php`
10. `src/Domain/Shared/Exception/DomainException.php`

### 3.2 遷移 Value Objects

從現有代碼中抽取並創建 Value Objects：

```php
// 遷移前（現有代碼）
$questionnaire->title = $data->title;

// 遷移後
$questionnaire = Questionnaire::create(
    id: QuestionnaireId::generate(),
    title: QuestionnaireTitle::fromString($data->title),
    // ...
);
```

創建順序：
1. `QuestionnaireId`, `QuestionId`, `ResponseId`, `AnswerId`
2. `QuestionnaireTitle`, `QuestionnaireSlug`
3. `QuestionnaireSettings`, `DateRange`
4. `QuestionContent`, `QuestionOptions`
5. `AnswerValue`, `RespondentInfo`, `SubmissionMetadata`

### 3.3 重構 Aggregates

將現有 Eloquent Model 重構為純 Domain Aggregate：

```php
// 遷移前（現有 Eloquent Model）
class Questionnaire extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['title', 'description', ...];
}

// 遷移後（Domain Aggregate）
final class Questionnaire extends AggregateRoot
{
    private QuestionnaireTitle $title;
    private ?string $description = null;
    
    public static function create(
        QuestionnaireId $id,
        QuestionnaireTitle $title,
        // ...
    ): self {
        $instance = new self();
        $instance->recordThat(new QuestionnaireCreated(...));
        return $instance;
    }
    
    protected function applyQuestionnaireCreated(QuestionnaireCreated $event): void
    {
        $this->id = $event->getAggregateId();
        $this->title = $event->title;
        // ...
    }
}
```

### 3.4 重構 Domain Events

確保所有 Domain Events 實作 `toPayload()` 和 `fromPayload()` 方法。

### 3.5 驗證步驟

```bash
# 執行 PHPStan Level 5
./vendor/bin/phpstan analyse --level=5 src/Domain

# 執行單元測試
./vendor/bin/pest tests/Unit
```

---

## 4. Phase 3: Application 層

### 4.1 實作 Bus

創建 Command Bus 和 Query Bus：

1. `src/Contracts/Application/Bus/CommandBusInterface.php`
2. `src/Contracts/Application/Bus/QueryBusInterface.php`
3. `src/Contracts/Application/Bus/EventBusInterface.php`
4. `src/Infrastructure/Bus/IlluminateCommandBus.php`
5. `src/Infrastructure/Bus/IlluminateQueryBus.php`
6. `src/Infrastructure/Bus/IlluminateEventBus.php`

### 4.2 遷移 Actions 到 Commands

```php
// 遷移前（現有 Action）
class CreateQuestionnaireAction implements CreateQuestionnaireActionInterface
{
    public function execute(QuestionnaireData $data, ?int $userId = null): Questionnaire
    {
        // ...
    }
}

// 遷移後（Command + Handler）
final readonly class CreateQuestionnaireCommand implements CommandInterface
{
    public function __construct(
        public QuestionnaireInput $input,
        public ?string $userId = null
    ) {}
}

final readonly class CreateQuestionnaireHandler implements CommandHandlerInterface
{
    public function handle(CreateQuestionnaireCommand $command): string
    {
        // ...
    }
}
```

### 4.3 創建 Queries

為現有的讀取操作創建 Query 和 Handler。

### 4.4 實作 Projectors

創建 Projectors 來維護 Read Models：

1. `QuestionnaireProjector`
2. `ResponseProjector`

### 4.5 驗證步驟

```bash
# 執行 PHPStan Level 7
./vendor/bin/phpstan analyse --level=7 src/Application

# 執行功能測試
./vendor/bin/pest tests/Feature
```

---

## 5. Phase 4: Event Sourcing

### 5.1 設置 Event Store

```bash
# 發布 Event Sourcing 遷移
php artisan vendor:publish --provider="Spatie\EventSourcing\EventSourcingServiceProvider" --tag="event-sourcing-migrations"

# 執行遷移
php artisan migrate
```

### 5.2 資料遷移

創建資料遷移命令，將現有資料轉換為事件：

```php
<?php

namespace Liangjin0228\Questionnaire\Infrastructure\Console;

use Illuminate\Console\Command;

final class MigrateToEventSourcingCommand extends Command
{
    protected $signature = 'questionnaire:migrate-to-event-sourcing 
                            {--dry-run : 不實際執行，只顯示將要執行的操作}';

    protected $description = 'Migrate existing data to event sourcing';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info('Starting migration to Event Sourcing...');

        // 1. 遷移問卷
        $this->migrateQuestionnaires($dryRun);

        // 2. 遷移回應
        $this->migrateResponses($dryRun);

        $this->info('Migration completed!');

        return self::SUCCESS;
    }

    private function migrateQuestionnaires(bool $dryRun): void
    {
        $questionnaires = DB::table('questionnaires')->get();

        $bar = $this->output->createProgressBar($questionnaires->count());

        foreach ($questionnaires as $row) {
            if (!$dryRun) {
                // 為每個問卷創建初始事件
                $event = new QuestionnaireCreated(
                    aggregateId: QuestionnaireId::fromString($row->id),
                    title: QuestionnaireTitle::fromString($row->title),
                    description: $row->description,
                    slug: QuestionnaireSlug::fromString($row->slug),
                    status: QuestionnaireStatus::from($row->status),
                    ownerId: $row->user_id ? UserId::fromString($row->user_id) : null,
                    settings: QuestionnaireSettings::fromArray($row->settings ?? []),
                    occurredAt: new DateTimeImmutable($row->created_at)
                );

                $this->eventStore->append($row->id, [$event], 0);

                // 如果已發布，創建發布事件
                if ($row->status === 'published') {
                    // ...
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function migrateResponses(bool $dryRun): void
    {
        // 類似邏輯
    }
}
```

### 5.3 驗證步驟

```bash
# 執行遷移（先用 dry-run）
php artisan questionnaire:migrate-to-event-sourcing --dry-run

# 確認無誤後執行
php artisan questionnaire:migrate-to-event-sourcing

# 驗證投影
php artisan questionnaire:rebuild-projections
```

---

## 6. Phase 5: Infrastructure 層

### 6.1 拆分 Controllers

將現有的 `QuestionnaireController` 拆分為：

1. `QuestionnaireCommandController`
2. `QuestionCommandController`
3. `ResponseCommandController`
4. `QuestionnaireQueryController`
5. `ResponseQueryController`

### 6.2 更新路由

```php
// routes/api.php

// 公開路由
Route::get('/question-types', [QuestionnaireQueryController::class, 'questionTypes']);
Route::get('/public/{questionnaire}', [QuestionnaireQueryController::class, 'public']);
Route::post('/public/{questionnaire}/submit', [ResponseCommandController::class, 'store']);

// 認證路由
Route::middleware(['auth:sanctum'])->group(function () {
    // Questionnaire CRUD
    Route::get('/', [QuestionnaireQueryController::class, 'index']);
    Route::post('/', [QuestionnaireCommandController::class, 'store']);
    // ...
});
```

### 6.3 更新 ServiceProvider

重構 `QuestionnaireServiceProvider`，使用新的綁定方式。

### 6.4 移動並重命名文件

```bash
# 移動 Requests
mv src/Http/Requests/* src/Infrastructure/Http/Request/

# 移動 Resources
mv src/Http/Resources/* src/Infrastructure/Http/Resource/

# 移動 QuestionTypes
mv src/QuestionTypes/* src/Domain/Questionnaire/QuestionType/

# 移動 Guards
mv src/Guards/* src/Domain/Response/Guard/

# 移動 Submission
mv src/Submission/* src/Domain/Response/Submission/

# 移動 DTOs
mv src/DTOs/* src/Application/DTO/Input/

# 移動 Listeners
mv src/Listeners/* src/Application/Listener/

# 移動 Mail
mv src/Mail/* src/Infrastructure/Mail/

# 移動 Export
mv src/Export/* src/Infrastructure/Export/

# 移動 Console
mv src/Console/* src/Infrastructure/Console/

# 移動 Policies
mv src/Policies/* src/Policy/
```

### 6.5 驗證步驟

```bash
# 執行完整測試套件
./vendor/bin/pest

# 執行 PHPStan Level 9
./vendor/bin/phpstan analyse --level=9
```

---

## 7. Phase 6: 測試和清理

### 7.1 更新測試

將所有 PHPUnit 測試轉換為 Pest：

```php
// 遷移前（PHPUnit）
class QuestionnaireTest extends TestCase
{
    public function test_can_create_questionnaire(): void
    {
        $this->actingAs($user)
            ->postJson('/api/questionnaire', [...])
            ->assertStatus(201);
    }
}

// 遷移後（Pest）
it('can create a questionnaire', function () {
    actingAs($user)
        ->postJson('/api/questionnaire', [...])
        ->assertStatus(201);
});
```

### 7.2 添加架構測試

```php
// tests/Arch/DomainLayerTest.php
arch('domain layer should not depend on infrastructure')
    ->expect('Liangjin0228\Questionnaire\Domain')
    ->toUseNothing()
    ->except([
        'Ramsey\Uuid',
        'DateTimeImmutable',
        'JsonSerializable',
    ]);

arch('aggregates should be final')
    ->expect('Liangjin0228\Questionnaire\Domain\*\Aggregate')
    ->toBeFinal();

arch('value objects should be final and readonly')
    ->expect('Liangjin0228\Questionnaire\Domain\*\ValueObject')
    ->toBeFinal();
```

### 7.3 清理舊代碼

```bash
# 確認舊目錄為空後刪除
rmdir src/Http/Requests
rmdir src/Http/Resources
rmdir src/Http
rmdir src/DTOs
rmdir src/Services
rmdir src/QuestionTypes
rmdir src/Guards
rmdir src/Submission
rmdir src/Managers
rmdir src/Listeners
rmdir src/Mail
rmdir src/Export
rmdir src/Exceptions
rmdir src/Console
rmdir src/Policies
```

### 7.4 更新命名空間

使用 Rector 自動更新所有命名空間引用：

```bash
./vendor/bin/rector process
```

### 7.5 最終驗證

```bash
# 完整測試套件
./vendor/bin/pest --coverage

# PHPStan Level 9
./vendor/bin/phpstan analyse --level=9

# 代碼格式化
./vendor/bin/pint

# 確認沒有未使用的依賴
composer-unused
```

---

## 8. 回滾計劃

如果遷移過程中出現嚴重問題：

### 8.1 Git 回滾

```bash
# 回滾到遷移前
git checkout v1.x-stable

# 或回滾到特定 commit
git reset --hard <commit-hash>
```

### 8.2 資料庫回滾

```bash
# 回滾 Event Sourcing 遷移
php artisan migrate:rollback --step=2
```

### 8.3 Composer 回滾

```bash
# 恢復舊的 composer.json
cp composer.json.bak composer.json
composer update
```

---

## 9. 驗收清單

- [ ] 所有測試通過
- [ ] PHPStan Level 9 無錯誤
- [ ] 代碼覆蓋率 > 90%
- [ ] 所有 API 端點正常運作
- [ ] Event Sourcing 正常運作
- [ ] 投影正確重建
- [ ] 文檔已更新
- [ ] CHANGELOG 已更新
- [ ] README 已更新

---

**下一篇**: [09-testing-strategy.md](./09-testing-strategy.md) - 測試策略與規範
