# Questionnaire Package 重構計劃總覽

> **版本**: v2.0.0 (Major Refactoring)  
> **日期**: 2026-01-27  
> **狀態**: 待確認

---

## 目錄

| 文件 | 內容 |
|------|------|
| [00-overview.md](./00-overview.md) | 重構計劃總覽（本文件） |
| [01-architecture.md](./01-architecture.md) | 新架構設計與目錄結構 |
| [02-domain-layer.md](./02-domain-layer.md) | Domain 層重構計劃 |
| [03-application-layer.md](./03-application-layer.md) | Application 層（CQRS）設計 |
| [04-infrastructure-layer.md](./04-infrastructure-layer.md) | Infrastructure 層重構計劃 |
| [05-contracts.md](./05-contracts.md) | 介面與契約重新設計 |
| [06-event-sourcing.md](./06-event-sourcing.md) | Event Sourcing 實作計劃 |
| [07-dependencies.md](./07-dependencies.md) | 依賴套件變更計劃 |
| [08-migration-guide.md](./08-migration-guide.md) | 遷移步驟與執行順序 |
| [09-testing-strategy.md](./09-testing-strategy.md) | 測試策略與規範 |

---

## 1. 重構目標

### 1.1 核心目標

1. **嚴格的 DDD 分層架構** - 清晰分離 Domain、Application、Infrastructure 三層
2. **完整的 CQRS 模式** - Command/Query 完全分離，使用 Bus 分發
3. **Event Sourcing** - 所有聚合狀態變更通過事件重建
4. **設計模式的優雅應用** - Specification、Repository、Factory、Strategy 等
5. **高度可測試性** - 依賴注入、介面隔離、易於 Mock

### 1.2 預期成果

| 指標 | 現狀 | 目標 |
|------|------|------|
| PHPStan Level | 未使用 | Level 9 |
| 測試覆蓋率 | ~60% | >90% |
| 目錄層級一致性 | 混亂 | 統一 |
| 循環依賴 | 存在 | 零 |
| CQRS | 無 | 完整 |
| Event Sourcing | 無 | 完整 |

---

## 2. 現有架構問題分析

### 2.1 目錄結構不一致

```
問題：HTTP 層存在於兩處
├── src/Http/                    (Requests, Resources)
└── src/Infrastructure/Http/     (Controllers)

問題：Services 命名與實際用途不符
└── src/Services/                (實際是 Actions)

問題：缺少明確的 Application 層
├── src/DTOs/                    (應歸屬 Application)
├── src/Services/                (應歸屬 Application)
└── src/Submission/              (應歸屬 Domain/Response)

問題：領域相關組件散落各處
├── src/QuestionTypes/           (應歸屬 Domain/Question)
├── src/Guards/                  (應歸屬 Domain/Response)
└── src/Managers/                (應歸屬 Infrastructure)
```

### 2.2 缺失的架構元素

| 元素 | 現狀 | 重構後 |
|------|------|--------|
| Aggregate Root 基類 | 無 | 有 |
| Domain Service | 無 | 有 |
| Value Objects | 無 | 有 |
| Specification Pattern | 無 | 有 |
| CQRS Bus | 無 | 有 |
| Event Store | 無 | 有 |
| Read Model | 無 | 有 |

### 2.3 Controller 臃腫

現有 `QuestionnaireController` 有 13+ 方法，違反單一職責原則：

```php
// 現有方法
- index(), store(), show(), update(), destroy()  // CRUD
- publish(), close()                             // 狀態變更
- public(), submit()                             // 公開填寫
- responses(), statistics()                      // 查詢
- questionTypes()                                // 元數據
```

---

## 3. 新架構概覽

### 3.1 分層架構圖

```
┌─────────────────────────────────────────────────────────────┐
│                    Infrastructure Layer                      │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐            │
│  │ Controllers │ │ Persistence │ │  External   │            │
│  │  (HTTP)     │ │(Repository) │ │  Services   │            │
│  └──────┬──────┘ └──────┬──────┘ └──────┬──────┘            │
└─────────┼───────────────┼───────────────┼───────────────────┘
          │               │               │
          ▼               ▼               ▼
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                         │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐            │
│  │  Commands   │ │   Queries   │ │   DTOs      │            │
│  │  Handlers   │ │   Handlers  │ │   Mappers   │            │
│  └──────┬──────┘ └──────┬──────┘ └─────────────┘            │
│         │               │                                    │
│         └───────┬───────┘                                    │
│                 │  CommandBus / QueryBus                     │
└─────────────────┼───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│                      Domain Layer                            │
│  ┌───────────────────────────────────────────────────────┐  │
│  │                   Aggregates                          │  │
│  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐     │  │
│  │  │Questionnaire│ │  Question   │ │  Response   │     │  │
│  │  │ Aggregate   │ │  (Entity)   │ │  Aggregate  │     │  │
│  │  └─────────────┘ └─────────────┘ └─────────────┘     │  │
│  └───────────────────────────────────────────────────────┘  │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐            │
│  │   Value     │ │   Domain    │ │  Domain     │            │
│  │   Objects   │ │   Events    │ │  Services   │            │
│  └─────────────┘ └─────────────┘ └─────────────┘            │
│  ┌─────────────┐ ┌─────────────┐                            │
│  │Specifications│ │  Factories │                            │
│  └─────────────┘ └─────────────┘                            │
└─────────────────────────────────────────────────────────────┘
```

### 3.2 CQRS 流程圖

```
                    ┌──────────────────┐
                    │   HTTP Request   │
                    └────────┬─────────┘
                             │
              ┌──────────────┴──────────────┐
              ▼                             ▼
    ┌─────────────────┐           ┌─────────────────┐
    │    Command      │           │     Query       │
    │   Controller    │           │   Controller    │
    └────────┬────────┘           └────────┬────────┘
             │                             │
             ▼                             ▼
    ┌─────────────────┐           ┌─────────────────┐
    │   Command DTO   │           │    Query DTO    │
    └────────┬────────┘           └────────┬────────┘
             │                             │
             ▼                             ▼
    ┌─────────────────┐           ┌─────────────────┐
    │   Command Bus   │           │   Query Bus     │
    └────────┬────────┘           └────────┬────────┘
             │                             │
             ▼                             ▼
    ┌─────────────────┐           ┌─────────────────┐
    │ Command Handler │           │  Query Handler  │
    └────────┬────────┘           └────────┬────────┘
             │                             │
             ▼                             ▼
    ┌─────────────────┐           ┌─────────────────┐
    │  Domain Model   │           │   Read Model    │
    │  (Aggregates)   │           │   (Projections) │
    └────────┬────────┘           └────────┬────────┘
             │                             │
             ▼                             ▼
    ┌─────────────────┐           ┌─────────────────┐
    │   Event Store   │───────────│    Database     │
    └─────────────────┘  Project  └─────────────────┘
```

### 3.3 Event Sourcing 流程

```
┌─────────────────────────────────────────────────────────────┐
│                    Command Processing                        │
├─────────────────────────────────────────────────────────────┤
│  1. Command Handler 接收命令                                 │
│  2. 從 Event Store 載入聚合歷史事件                          │
│  3. 重建聚合當前狀態                                         │
│  4. 執行領域邏輯，產生新事件                                  │
│  5. 追加事件到 Event Store                                   │
│  6. 發布事件到 Event Bus                                     │
└─────────────────────────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                    Event Projection                          │
├─────────────────────────────────────────────────────────────┤
│  1. Projector 監聽領域事件                                   │
│  2. 更新 Read Model（投影）                                  │
│  3. Query Handler 直接查詢 Read Model                        │
└─────────────────────────────────────────────────────────────┘
```

---

## 4. 重構範圍

### 4.1 需要重構的組件

| 組件 | 變更類型 | 優先級 |
|------|----------|--------|
| 目錄結構 | 完全重組 | P0 |
| Aggregate 基類 | 新增 | P0 |
| Value Objects | 新增 | P0 |
| Domain Events | 重構為 Event Sourcing | P0 |
| CQRS Commands | 新增 | P0 |
| CQRS Queries | 新增 | P0 |
| Command/Query Bus | 新增 | P0 |
| Event Store | 新增 | P1 |
| Read Models | 新增 | P1 |
| Projectors | 新增 | P1 |
| Specifications | 新增 | P1 |
| Domain Services | 新增 | P1 |
| Controllers | 拆分重組 | P1 |
| Repositories | 重構 | P2 |
| ServiceProvider | 重構 | P2 |

### 4.2 需要保留的組件（重新組織）

| 組件 | 新位置 |
|------|--------|
| QuestionTypes | `Domain/Questionnaire/QuestionType/` |
| Guards | `Domain/Response/Guard/` |
| Pipeline Pipes | `Domain/Response/Submission/Pipe/` |
| DTOs | `Application/DTO/` |
| Resources | `Infrastructure/Http/Resource/` |
| Requests | `Infrastructure/Http/Request/` |

---

## 5. 設計模式使用計劃

| 模式 | 用途 | 位置 |
|------|------|------|
| **Aggregate Root** | 確保聚合邊界一致性 | Domain |
| **Repository** | 聚合持久化抽象 | Domain (Interface) / Infrastructure (Impl) |
| **Factory** | 複雜聚合創建 | Domain |
| **Specification** | 業務規則封裝 | Domain |
| **Strategy** | 問題類型處理 | Domain |
| **Observer** | 領域事件監聽 | Application/Infrastructure |
| **Command** | CQRS 寫入操作 | Application |
| **Query** | CQRS 讀取操作 | Application |
| **Mediator** | Command/Query Bus | Application |
| **Decorator** | 橫切關注點（日誌、快取） | Infrastructure |
| **Adapter** | 外部服務整合 | Infrastructure |
| **Facade** | 簡化複雜子系統訪問 | Infrastructure |

---

## 6. 預估時程

| 階段 | 內容 | 預估工時 |
|------|------|----------|
| **Phase 1** | 基礎設施建置 | 16h |
| | - 新增依賴套件 | 2h |
| | - 設置 PHPStan/Pest | 2h |
| | - 建立新目錄結構 | 2h |
| | - 建立基礎類（Aggregate, Value Object） | 10h |
| **Phase 2** | Domain 層重構 | 24h |
| | - Value Objects 實作 | 8h |
| | - Aggregate Roots 重構 | 8h |
| | - Domain Events 重構 | 4h |
| | - Specifications 實作 | 4h |
| **Phase 3** | Application 層建置 | 20h |
| | - CQRS Bus 設置 | 4h |
| | - Commands & Handlers | 8h |
| | - Queries & Handlers | 8h |
| **Phase 4** | Event Sourcing | 16h |
| | - Event Store 實作 | 8h |
| | - Projectors 實作 | 8h |
| **Phase 5** | Infrastructure 重構 | 12h |
| | - Controllers 拆分 | 6h |
| | - Repositories 重構 | 4h |
| | - ServiceProvider 更新 | 2h |
| **Phase 6** | 測試與文檔 | 12h |
| | - 單元測試 | 6h |
| | - 整合測試 | 4h |
| | - 文檔更新 | 2h |
| **總計** | | **100h** |

---

## 7. 風險與注意事項

### 7.1 主要風險

| 風險 | 影響 | 緩解措施 |
|------|------|----------|
| Event Sourcing 複雜度 | 開發時間增加 | 使用成熟套件（spatie/laravel-event-sourcing） |
| CQRS 學習曲線 | 團隊適應期 | 提供詳細文檔和範例 |
| 向後不相容 | 現有使用者影響 | 提供遷移指南 |
| 效能影響 | 事件重建開銷 | 使用快照機制優化 |

### 7.2 注意事項

1. **漸進式重構** - 每個 Phase 完成後進行完整測試
2. **Git 分支策略** - 使用 feature branch，完成後 squash merge
3. **代碼審查** - 每個 Phase 完成後進行 code review
4. **文檔同步** - 代碼變更需同步更新文檔

---

## 8. 快速參考

### 8.1 新的命名空間映射

```php
// Domain Layer
Liangjin0228\Questionnaire\Domain\Questionnaire\...
Liangjin0228\Questionnaire\Domain\Response\...
Liangjin0228\Questionnaire\Domain\Shared\...

// Application Layer
Liangjin0228\Questionnaire\Application\Command\...
Liangjin0228\Questionnaire\Application\Query\...
Liangjin0228\Questionnaire\Application\DTO\...
Liangjin0228\Questionnaire\Application\Projector\...

// Infrastructure Layer
Liangjin0228\Questionnaire\Infrastructure\Http\...
Liangjin0228\Questionnaire\Infrastructure\Persistence\...
Liangjin0228\Questionnaire\Infrastructure\EventStore\...
Liangjin0228\Questionnaire\Infrastructure\Bus\...

// Contracts (Interface)
Liangjin0228\Questionnaire\Contracts\...
```

### 8.2 主要設計決策

| 決策項目 | 選擇 |
|----------|------|
| 向後相容性 | 不保持（Major 版本升級） |
| Contracts 組織 | 集中式（src/Contracts） |
| 靜態分析 | PHPStan Level 9 + Larastan |
| 測試框架 | Pest PHP |
| CQRS 模式 | 完整（Commands + Queries + Bus） |
| Event Sourcing | 完整實作 |
| Domain Services | 啟用 |
| Specification Pattern | 啟用 |
| 依賴套件偏好 | 混合使用 |

---

**下一步**: 請閱讀 [01-architecture.md](./01-architecture.md) 了解詳細的架構設計。
