# 新架構設計與目錄結構

> **文件**: 01-architecture.md  
> **上一篇**: [00-overview.md](./00-overview.md)  
> **下一篇**: [02-domain-layer.md](./02-domain-layer.md)

---

## 1. 目標目錄結構

以下是重構後的完整目錄結構，遵循嚴格的 DDD 分層架構：

```
src/
├── Contracts/                              # 所有介面定義（集中管理）
│   ├── Domain/
│   │   ├── AggregateRootInterface.php
│   │   ├── DomainEventInterface.php
│   │   ├── EntityInterface.php
│   │   ├── ValueObjectInterface.php
│   │   ├── SpecificationInterface.php
│   │   └── Repository/
│   │       ├── QuestionnaireRepositoryInterface.php
│   │       └── ResponseRepositoryInterface.php
│   │
│   ├── Application/
│   │   ├── Bus/
│   │   │   ├── CommandBusInterface.php
│   │   │   ├── QueryBusInterface.php
│   │   │   └── EventBusInterface.php
│   │   ├── CommandInterface.php
│   │   ├── CommandHandlerInterface.php
│   │   ├── QueryInterface.php
│   │   └── QueryHandlerInterface.php
│   │
│   └── Infrastructure/
│       ├── EventStoreInterface.php
│       ├── ProjectorInterface.php
│       ├── SnapshotStoreInterface.php
│       └── ExporterInterface.php
│
├── Domain/                                 # 領域層（純業務邏輯）
│   ├── Shared/                             # 共享核心
│   │   ├── Aggregate/
│   │   │   ├── AggregateRoot.php
│   │   │   └── AggregateId.php
│   │   ├── Entity/
│   │   │   └── Entity.php
│   │   ├── ValueObject/
│   │   │   └── ValueObject.php
│   │   ├── Event/
│   │   │   └── DomainEvent.php
│   │   ├── Specification/
│   │   │   ├── Specification.php
│   │   │   ├── AndSpecification.php
│   │   │   ├── OrSpecification.php
│   │   │   └── NotSpecification.php
│   │   └── Exception/
│   │       ├── DomainException.php
│   │       └── InvalidArgumentException.php
│   │
│   ├── Questionnaire/                      # 問卷聚合（包含問題）
│   │   ├── Aggregate/
│   │   │   └── Questionnaire.php
│   │   ├── Entity/
│   │   │   └── Question.php
│   │   ├── ValueObject/
│   │   │   ├── QuestionnaireId.php
│   │   │   ├── QuestionnaireTitle.php
│   │   │   ├── QuestionnaireSlug.php
│   │   │   ├── QuestionnaireSettings.php
│   │   │   ├── DateRange.php
│   │   │   ├── QuestionId.php
│   │   │   ├── QuestionContent.php
│   │   │   └── QuestionOptions.php
│   │   ├── Enum/
│   │   │   ├── QuestionnaireStatus.php
│   │   │   ├── QuestionType.php
│   │   │   └── DuplicateSubmissionStrategy.php
│   │   ├── Event/
│   │   │   ├── QuestionnaireCreated.php
│   │   │   ├── QuestionnaireUpdated.php
│   │   │   ├── QuestionnairePublished.php
│   │   │   ├── QuestionnaireClosed.php
│   │   │   ├── QuestionnaireArchived.php
│   │   │   ├── QuestionAdded.php
│   │   │   ├── QuestionUpdated.php
│   │   │   └── QuestionRemoved.php
│   │   ├── Specification/
│   │   │   ├── QuestionnaireIsActiveSpec.php
│   │   │   ├── QuestionnaireIsPublishedSpec.php
│   │   │   ├── QuestionnaireAcceptsResponsesSpec.php
│   │   │   └── QuestionnaireHasQuestionsSpec.php
│   │   ├── Factory/
│   │   │   ├── QuestionnaireFactory.php
│   │   │   └── QuestionFactory.php
│   │   ├── Service/
│   │   │   └── QuestionnaireValidationService.php
│   │   ├── QuestionType/                   # 問題類型策略
│   │   │   ├── QuestionTypeInterface.php
│   │   │   ├── AbstractQuestionType.php
│   │   │   ├── TextQuestionType.php
│   │   │   ├── TextareaQuestionType.php
│   │   │   ├── RadioQuestionType.php
│   │   │   ├── CheckboxQuestionType.php
│   │   │   ├── SelectQuestionType.php
│   │   │   ├── NumberQuestionType.php
│   │   │   ├── DateQuestionType.php
│   │   │   └── QuestionTypeRegistry.php
│   │   └── Exception/
│   │       ├── QuestionnaireException.php
│   │       ├── QuestionnaireNotPublishedException.php
│   │       ├── QuestionnaireClosedException.php
│   │       └── InvalidQuestionException.php
│   │
│   ├── Response/                           # 回應聚合
│   │   ├── Aggregate/
│   │   │   └── Response.php
│   │   ├── Entity/
│   │   │   └── Answer.php
│   │   ├── ValueObject/
│   │   │   ├── ResponseId.php
│   │   │   ├── AnswerId.php
│   │   │   ├── AnswerValue.php
│   │   │   ├── RespondentInfo.php
│   │   │   └── SubmissionMetadata.php
│   │   ├── Event/
│   │   │   ├── ResponseSubmitted.php
│   │   │   ├── ResponseValidated.php
│   │   │   └── ResponseRejected.php
│   │   ├── Specification/
│   │   │   ├── ResponseIsCompleteSpec.php
│   │   │   └── ResponseIsValidSpec.php
│   │   ├── Guard/                          # 重複提交防護
│   │   │   ├── DuplicateSubmissionGuardInterface.php
│   │   │   ├── AllowMultipleGuard.php
│   │   │   ├── OnePerUserGuard.php
│   │   │   ├── OnePerSessionGuard.php
│   │   │   ├── OnePerIpGuard.php
│   │   │   └── GuardFactory.php
│   │   ├── Submission/                     # 提交流程
│   │   │   ├── SubmissionPipeline.php
│   │   │   ├── SubmissionContext.php
│   │   │   └── Pipe/
│   │   │       ├── PipeInterface.php
│   │   │       ├── EnsureQuestionnaireIsOpen.php
│   │   │       ├── CheckDuplicateSubmission.php
│   │   │       ├── ValidateAnswers.php
│   │   │       ├── CreateResponse.php
│   │   │       └── DispatchEvents.php
│   │   ├── Service/
│   │   │   ├── ResponseValidationService.php
│   │   │   └── StatisticsCalculationService.php
│   │   └── Exception/
│   │       ├── ResponseException.php
│   │       ├── DuplicateSubmissionException.php
│   │       └── ValidationException.php
│   │
│   └── User/                               # 使用者值物件
│       └── ValueObject/
│           └── UserId.php
│
├── Application/                            # 應用層（CQRS）
│   ├── Bus/
│   │   ├── CommandBus.php
│   │   ├── QueryBus.php
│   │   └── EventBus.php
│   │
│   ├── Command/                            # 命令（寫入操作）
│   │   ├── Questionnaire/
│   │   │   ├── CreateQuestionnaire/
│   │   │   │   ├── CreateQuestionnaireCommand.php
│   │   │   │   └── CreateQuestionnaireHandler.php
│   │   │   ├── UpdateQuestionnaire/
│   │   │   │   ├── UpdateQuestionnaireCommand.php
│   │   │   │   └── UpdateQuestionnaireHandler.php
│   │   │   ├── PublishQuestionnaire/
│   │   │   │   ├── PublishQuestionnaireCommand.php
│   │   │   │   └── PublishQuestionnaireHandler.php
│   │   │   ├── CloseQuestionnaire/
│   │   │   │   ├── CloseQuestionnaireCommand.php
│   │   │   │   └── CloseQuestionnaireHandler.php
│   │   │   ├── ArchiveQuestionnaire/
│   │   │   │   ├── ArchiveQuestionnaireCommand.php
│   │   │   │   └── ArchiveQuestionnaireHandler.php
│   │   │   ├── AddQuestion/
│   │   │   │   ├── AddQuestionCommand.php
│   │   │   │   └── AddQuestionHandler.php
│   │   │   ├── UpdateQuestion/
│   │   │   │   ├── UpdateQuestionCommand.php
│   │   │   │   └── UpdateQuestionHandler.php
│   │   │   └── RemoveQuestion/
│   │   │       ├── RemoveQuestionCommand.php
│   │   │       └── RemoveQuestionHandler.php
│   │   │
│   │   └── Response/
│   │       └── SubmitResponse/
│   │           ├── SubmitResponseCommand.php
│   │           └── SubmitResponseHandler.php
│   │
│   ├── Query/                              # 查詢（讀取操作）
│   │   ├── Questionnaire/
│   │   │   ├── GetQuestionnaire/
│   │   │   │   ├── GetQuestionnaireQuery.php
│   │   │   │   └── GetQuestionnaireHandler.php
│   │   │   ├── ListQuestionnaires/
│   │   │   │   ├── ListQuestionnairesQuery.php
│   │   │   │   └── ListQuestionnairesHandler.php
│   │   │   ├── GetQuestionnaireForFilling/
│   │   │   │   ├── GetQuestionnaireForFillingQuery.php
│   │   │   │   └── GetQuestionnaireForFillingHandler.php
│   │   │   └── GetQuestionTypes/
│   │   │       ├── GetQuestionTypesQuery.php
│   │   │       └── GetQuestionTypesHandler.php
│   │   │
│   │   └── Response/
│   │       ├── GetResponses/
│   │       │   ├── GetResponsesQuery.php
│   │       │   └── GetResponsesHandler.php
│   │       └── GetStatistics/
│   │           ├── GetStatisticsQuery.php
│   │           └── GetStatisticsHandler.php
│   │
│   ├── DTO/                                # 資料傳輸物件
│   │   ├── Input/
│   │   │   ├── QuestionnaireInput.php
│   │   │   ├── QuestionInput.php
│   │   │   └── SubmitResponseInput.php
│   │   └── Output/
│   │       ├── QuestionnaireOutput.php
│   │       ├── QuestionOutput.php
│   │       ├── ResponseOutput.php
│   │       ├── StatisticsOutput.php
│   │       └── QuestionTypeOutput.php
│   │
│   ├── Projector/                          # Event Sourcing 投影器
│   │   ├── QuestionnaireProjector.php
│   │   └── ResponseProjector.php
│   │
│   ├── ReadModel/                          # 讀取模型定義
│   │   ├── QuestionnaireReadModel.php
│   │   ├── QuestionReadModel.php
│   │   └── ResponseReadModel.php
│   │
│   ├── Mapper/                             # DTO 映射器
│   │   ├── QuestionnaireMapper.php
│   │   ├── QuestionMapper.php
│   │   └── ResponseMapper.php
│   │
│   └── Listener/                           # 應用層事件監聽器
│       ├── SendResponseNotification.php
│       └── LogResponseSubmission.php
│
├── Infrastructure/                         # 基礎設施層
│   ├── Http/
│   │   ├── Controller/
│   │   │   ├── Command/                    # 命令控制器
│   │   │   │   ├── QuestionnaireCommandController.php
│   │   │   │   ├── QuestionCommandController.php
│   │   │   │   └── ResponseCommandController.php
│   │   │   └── Query/                      # 查詢控制器
│   │   │       ├── QuestionnaireQueryController.php
│   │   │       └── ResponseQueryController.php
│   │   ├── Request/
│   │   │   ├── StoreQuestionnaireRequest.php
│   │   │   ├── UpdateQuestionnaireRequest.php
│   │   │   ├── StoreQuestionRequest.php
│   │   │   ├── UpdateQuestionRequest.php
│   │   │   └── SubmitResponseRequest.php
│   │   ├── Resource/
│   │   │   ├── QuestionnaireResource.php
│   │   │   ├── QuestionResource.php
│   │   │   ├── ResponseResource.php
│   │   │   └── QuestionTypeResource.php
│   │   └── Middleware/
│   │       ├── EnsureQuestionnaireAccessible.php
│   │       └── ThrottleResponses.php
│   │
│   ├── Persistence/
│   │   ├── Repository/
│   │   │   ├── EventSourcedQuestionnaireRepository.php
│   │   │   └── EventSourcedResponseRepository.php
│   │   ├── ReadModel/                      # Eloquent Read Models
│   │   │   ├── QuestionnaireModel.php
│   │   │   ├── QuestionModel.php
│   │   │   ├── ResponseModel.php
│   │   │   └── AnswerModel.php
│   │   └── Factory/
│   │       └── QuestionnaireModelFactory.php
│   │
│   ├── EventStore/
│   │   ├── EloquentEventStore.php
│   │   ├── StoredEvent.php
│   │   ├── EventSerializer.php
│   │   └── Snapshot/
│   │       ├── EloquentSnapshotStore.php
│   │       └── Snapshot.php
│   │
│   ├── Bus/
│   │   ├── IlluminateCommandBus.php
│   │   ├── IlluminateQueryBus.php
│   │   └── IlluminateEventBus.php
│   │
│   ├── Export/
│   │   ├── CsvExporter.php
│   │   └── ExcelExporter.php
│   │
│   ├── Mail/
│   │   └── ResponseSubmittedMail.php
│   │
│   └── Console/
│       ├── InstallCommand.php
│       ├── ListQuestionTypesCommand.php
│       ├── ReplayEventsCommand.php
│       ├── RebuildProjectionsCommand.php
│       └── CreateSnapshotCommand.php
│
├── Policy/
│   ├── QuestionnairePolicy.php
│   └── ResponsePolicy.php
│
└── QuestionnaireServiceProvider.php
```

---

## 2. 分層職責說明

### 2.1 Domain Layer（領域層）

**職責**：
- 包含核心業務邏輯
- 定義領域模型（Aggregates, Entities, Value Objects）
- 發布領域事件
- 業務規則驗證（Specifications）
- **不依賴任何框架**

**原則**：
- 純 PHP，無框架依賴
- 所有狀態變更透過領域事件
- 聚合根是一致性邊界
- 值物件是不可變的

### 2.2 Application Layer（應用層）

**職責**：
- 協調領域層完成用例
- 實作 CQRS（Command/Query 分離）
- 事件投影（Projectors）
- DTO 轉換
- 事務管理

**原則**：
- 不包含業務邏輯
- 透過 Bus 分發命令和查詢
- 維護讀取模型

### 2.3 Infrastructure Layer（基礎設施層）

**職責**：
- HTTP 處理（Controllers, Requests, Resources）
- 持久化實作（Repositories, Event Store）
- 外部服務整合（Mail, Export）
- 框架整合

**原則**：
- 實作所有介面
- 可隨時替換實作
- 依賴注入

---

## 3. 依賴規則

```
┌─────────────────────────────────────────────────────────────┐
│                        依賴方向                              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│   Infrastructure ──────▶ Application ──────▶ Domain        │
│        │                      │                  │          │
│        │                      │                  │          │
│        ▼                      ▼                  ▼          │
│   Contracts             Contracts            Pure PHP       │
│   (Interfaces)          (Interfaces)         (No deps)      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 規則詳解

1. **Domain 層不依賴任何外部**
   - 不使用 Eloquent
   - 不使用 Laravel facades
   - 純 PHP + 自定義異常

2. **Application 層只依賴 Domain 層**
   - 通過介面與 Infrastructure 互動
   - 不直接使用 Eloquent

3. **Infrastructure 層可依賴所有層**
   - 實作 Domain 和 Application 定義的介面
   - 可使用 Laravel 全部功能

4. **Contracts 層提供介面**
   - 所有介面集中管理
   - 支援依賴反轉

---

## 4. 命名空間對照表

### 4.1 現有 → 新的命名空間

| 現有位置 | 新位置 |
|----------|--------|
| `Contracts\QuestionnaireRepositoryInterface` | `Contracts\Domain\Repository\QuestionnaireRepositoryInterface` |
| `Contracts\ResponseRepositoryInterface` | `Contracts\Domain\Repository\ResponseRepositoryInterface` |
| `Contracts\QuestionTypeInterface` | `Domain\Questionnaire\QuestionType\QuestionTypeInterface` |
| `Contracts\ValidationStrategyInterface` | `Domain\Response\Service\ResponseValidationService` |
| `Contracts\Actions\*` | `Application\Command\*` |
| `Domain\Questionnaire\Models\Questionnaire` | `Domain\Questionnaire\Aggregate\Questionnaire` |
| `Domain\Question\Models\Question` | `Domain\Questionnaire\Entity\Question` |
| `Domain\Response\Models\Response` | `Domain\Response\Aggregate\Response` |
| `Domain\Response\Models\Answer` | `Domain\Response\Entity\Answer` |
| `DTOs\*` | `Application\DTO\Input\*` |
| `Services\*Action` | `Application\Command\*\*Handler` |
| `QuestionTypes\*` | `Domain\Questionnaire\QuestionType\*` |
| `Guards\*` | `Domain\Response\Guard\*` |
| `Submission\*` | `Domain\Response\Submission\*` |
| `Http\Requests\*` | `Infrastructure\Http\Request\*` |
| `Http\Resources\*` | `Infrastructure\Http\Resource\*` |
| `Infrastructure\Http\Controllers\*` | `Infrastructure\Http\Controller\Command\*` or `Query\*` |
| `Infrastructure\Persistence\Repositories\*` | `Infrastructure\Persistence\Repository\*` |
| `Listeners\*` | `Application\Listener\*` |
| `Mail\*` | `Infrastructure\Mail\*` |
| `Export\*` | `Infrastructure\Export\*` |
| `Managers\*` | `Domain\Questionnaire\QuestionType\QuestionTypeRegistry` |
| `Policies\*` | `Policy\*` |
| `Console\*` | `Infrastructure\Console\*` |

### 4.2 新增的命名空間

| 命名空間 | 用途 |
|----------|------|
| `Domain\Shared\*` | 共享領域基礎類 |
| `Domain\*\ValueObject\*` | 值物件 |
| `Domain\*\Specification\*` | 規格模式 |
| `Domain\*\Factory\*` | 工廠模式 |
| `Domain\*\Service\*` | 領域服務 |
| `Application\Command\*` | CQRS 命令 |
| `Application\Query\*` | CQRS 查詢 |
| `Application\Bus\*` | 命令/查詢總線 |
| `Application\Projector\*` | 事件投影器 |
| `Application\ReadModel\*` | 讀取模型定義 |
| `Application\Mapper\*` | DTO 映射器 |
| `Infrastructure\EventStore\*` | 事件儲存 |
| `Infrastructure\Bus\*` | Bus 實作 |
| `Infrastructure\Persistence\ReadModel\*` | Eloquent 讀取模型 |

---

## 5. 聚合邊界設計

### 5.1 Questionnaire 聚合

```
┌─────────────────────────────────────────────────────┐
│              Questionnaire Aggregate                 │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │         Questionnaire (Aggregate Root)       │   │
│  │                                              │   │
│  │  - QuestionnaireId (Value Object)           │   │
│  │  - QuestionnaireTitle (Value Object)        │   │
│  │  - QuestionnaireSlug (Value Object)         │   │
│  │  - QuestionnaireStatus (Enum)               │   │
│  │  - QuestionnaireSettings (Value Object)     │   │
│  │  - DateRange (Value Object)                 │   │
│  │  - UserId (Value Object)                    │   │
│  │                                              │   │
│  └─────────────────────────────────────────────┘   │
│                       │                             │
│                       │ 1:N                         │
│                       ▼                             │
│  ┌─────────────────────────────────────────────┐   │
│  │            Question (Entity)                 │   │
│  │                                              │   │
│  │  - QuestionId (Value Object)                │   │
│  │  - QuestionType (Enum)                      │   │
│  │  - QuestionContent (Value Object)           │   │
│  │  - QuestionOptions (Value Object)           │   │
│  │  - order: int                               │   │
│  │  - isRequired: bool                         │   │
│  │  - settings: array                          │   │
│  │                                              │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**不變條件（Invariants）**：
- 問卷標題必須存在且不超過 255 字元
- 已發布的問卷不可修改問題
- 發布前必須至少有一個問題
- 已關閉的問卷不可再發布

### 5.2 Response 聚合

```
┌─────────────────────────────────────────────────────┐
│               Response Aggregate                     │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │          Response (Aggregate Root)           │   │
│  │                                              │   │
│  │  - ResponseId (Value Object)                │   │
│  │  - QuestionnaireId (Value Object) [ref]     │   │
│  │  - RespondentInfo (Value Object)            │   │
│  │  - SubmissionMetadata (Value Object)        │   │
│  │  - submittedAt: DateTimeImmutable           │   │
│  │                                              │   │
│  └─────────────────────────────────────────────┘   │
│                       │                             │
│                       │ 1:N                         │
│                       ▼                             │
│  ┌─────────────────────────────────────────────┐   │
│  │             Answer (Entity)                  │   │
│  │                                              │   │
│  │  - AnswerId (Value Object)                  │   │
│  │  - QuestionId (Value Object) [ref]          │   │
│  │  - AnswerValue (Value Object)               │   │
│  │                                              │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**不變條件（Invariants）**：
- 必須回答所有必填問題
- 答案值必須符合問題類型的驗證規則
- 提交後不可修改

---

## 6. 模組化設計

### 6.1 Feature Flags 控制

```php
// config/questionnaire.php

return [
    'features' => [
        'event_sourcing' => true,      // 是否啟用 Event Sourcing
        'cqrs' => true,                 // 是否啟用 CQRS
        'snapshots' => true,            // 是否啟用快照
        'async_projections' => false,   // 是否使用異步投影
        'read_model_cache' => true,     // 是否快取讀取模型
        // ... 其他功能開關
    ],
];
```

### 6.2 可替換組件

所有核心組件都可透過配置替換：

```php
// config/questionnaire.php

return [
    'bindings' => [
        // Repositories
        'questionnaire_repository' => EventSourcedQuestionnaireRepository::class,
        'response_repository' => EventSourcedResponseRepository::class,
        
        // Buses
        'command_bus' => IlluminateCommandBus::class,
        'query_bus' => IlluminateQueryBus::class,
        'event_bus' => IlluminateEventBus::class,
        
        // Event Store
        'event_store' => EloquentEventStore::class,
        'snapshot_store' => EloquentSnapshotStore::class,
        
        // Domain Services
        'response_validation_service' => ResponseValidationService::class,
        'statistics_service' => StatisticsCalculationService::class,
    ],
];
```

---

**下一篇**: [02-domain-layer.md](./02-domain-layer.md) - Domain 層詳細設計
