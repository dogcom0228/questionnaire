# 依賴套件變更計劃

> **文件**: 07-dependencies.md  
> **上一篇**: [06-event-sourcing.md](./06-event-sourcing.md)  
> **下一篇**: [08-migration-guide.md](./08-migration-guide.md)

---

## 1. 現有依賴分析

### 1.1 目前的 composer.json

```json
{
    "require": {
        "php": "^8.2",
        "illuminate/support": "^10.0 || ^11.0 || ^12.0",
        "spatie/laravel-package-tools": "^1.92",
        "spatie/laravel-data": "^4.18"
    },
    "require-dev": {
        "fakerphp/faker": "^1.24",
        "laravel/pail": "^1.2",
        "laravel/pint": "^1.27",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.8",
        "phpunit/phpunit": "^12.5",
        "orchestra/testbench": "^10.8"
    }
}
```

### 1.2 依賴評估

| 套件 | 狀態 | 說明 |
|------|------|------|
| `illuminate/support` | 保留 | Laravel 核心 |
| `spatie/laravel-package-tools` | 保留 | 套件開發工具 |
| `spatie/laravel-data` | 保留 | DTO 支援 |
| `fakerphp/faker` | 保留 | 測試資料 |
| `laravel/pail` | 可選 | 日誌監控 |
| `laravel/pint` | 保留 | 代碼格式化 |
| `mockery/mockery` | 保留 | Mock 測試 |
| `nunomaduro/collision` | 保留 | 錯誤顯示 |
| `phpunit/phpunit` | 移除 | 改用 Pest |
| `orchestra/testbench` | 保留 | Package 測試 |

---

## 2. 新增依賴計劃

### 2.1 生產依賴 (require)

```json
{
    "require": {
        "php": "^8.2",
        "illuminate/support": "^10.0 || ^11.0 || ^12.0",
        "spatie/laravel-package-tools": "^1.92",
        "spatie/laravel-data": "^4.18",
        "spatie/laravel-event-sourcing": "^7.0",
        "ramsey/uuid": "^4.7"
    }
}
```

#### 新增套件說明

| 套件 | 版本 | 用途 | GitHub Stars | 安全性 |
|------|------|------|--------------|--------|
| `spatie/laravel-event-sourcing` | ^7.0 | Event Sourcing 實作 | 1.4k+ | 良好 |
| `ramsey/uuid` | ^4.7 | UUID 生成（UUID v7） | 12k+ | 良好 |

### 2.2 開發依賴 (require-dev)

```json
{
    "require-dev": {
        "fakerphp/faker": "^1.24",
        "laravel/pail": "^1.2",
        "laravel/pint": "^1.27",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.8",
        "pestphp/pest": "^3.0",
        "pestphp/pest-plugin-laravel": "^3.0",
        "pestphp/pest-plugin-arch": "^3.0",
        "orchestra/testbench": "^10.8",
        "larastan/larastan": "^2.9",
        "phpstan/phpstan": "^1.10",
        "rector/rector": "^1.0"
    }
}
```

#### 新增開發套件說明

| 套件 | 版本 | 用途 | GitHub Stars |
|------|------|------|--------------|
| `pestphp/pest` | ^3.0 | 現代化測試框架 | 9k+ |
| `pestphp/pest-plugin-laravel` | ^3.0 | Pest Laravel 整合 | - |
| `pestphp/pest-plugin-arch` | ^3.0 | 架構測試 | - |
| `larastan/larastan` | ^2.9 | PHPStan for Laravel | 5.5k+ |
| `phpstan/phpstan` | ^1.10 | 靜態分析 | 12k+ |
| `rector/rector` | ^1.0 | 自動重構工具 | 8k+ |

---

## 3. 完整的 composer.json

```json
{
    "name": "liangjin0228/questionnaire",
    "description": "A Laravel package for creating and managing questionnaires with DDD, CQRS, and Event Sourcing.",
    "keywords": [
        "laravel",
        "questionnaire",
        "survey",
        "form",
        "ddd",
        "cqrs",
        "event-sourcing"
    ],
    "type": "library",
    "license": "MIT",
    "minimum-stability": "stable",
    "authors": [
        {
            "name": "LiangJin Tan",
            "email": "dogcom0228@gmail.com"
        }
    ],
    "require": {
        "php": "^8.2",
        "illuminate/support": "^10.0 || ^11.0 || ^12.0",
        "spatie/laravel-package-tools": "^1.92",
        "spatie/laravel-data": "^4.18",
        "spatie/laravel-event-sourcing": "^7.0",
        "ramsey/uuid": "^4.7"
    },
    "require-dev": {
        "fakerphp/faker": "^1.24",
        "laravel/pail": "^1.2",
        "laravel/pint": "^1.27",
        "larastan/larastan": "^2.9",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.8",
        "orchestra/testbench": "^10.8",
        "pestphp/pest": "^3.0",
        "pestphp/pest-plugin-arch": "^3.0",
        "pestphp/pest-plugin-laravel": "^3.0",
        "phpstan/phpstan": "^1.10",
        "rector/rector": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "Liangjin0228\\Questionnaire\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Liangjin0228\\Questionnaire\\Tests\\": "tests/",
            "Liangjin0228\\Questionnaire\\Database\\Factories\\": "database/factories/"
        }
    },
    "scripts": {
        "test": "pest",
        "test:coverage": "pest --coverage",
        "test:arch": "pest --filter=arch",
        "analyse": "phpstan analyse",
        "format": "pint",
        "refactor": "rector process",
        "refactor:dry": "rector process --dry-run"
    },
    "extra": {
        "branch-alias": {
            "dev-main": "2.0-dev"
        },
        "laravel": {
            "providers": [
                "Liangjin0228\\Questionnaire\\QuestionnaireServiceProvider"
            ]
        }
    },
    "config": {
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true
        }
    }
}
```

---

## 4. 配置文件

### 4.1 PHPStan 配置 (phpstan.neon)

```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    level: 9
    
    paths:
        - src
    
    excludePaths:
        - src/Console/stubs
    
    ignoreErrors:
        # 忽略特定的錯誤（如果需要）
    
    checkMissingIterableValueType: true
    checkGenericClassInNonGenericObjectType: true
    
    # 嚴格模式
    treatPhpDocTypesAsCertain: false
    reportUnmatchedIgnoredErrors: true
```

### 4.2 Pest 配置 (phpunit.xml → pest.php)

```php
<?php
// tests/Pest.php

use Liangjin0228\Questionnaire\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)
    ->in('Feature');

uses(TestCase::class)
    ->in('Unit');

// 自訂 Expectations
expect()->extend('toBeUuid', function () {
    return $this->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

// 自訂 Helper
function createQuestionnaire(array $attributes = []): \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire
{
    return \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::create(
        id: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId::generate(),
        title: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle::fromString(
            $attributes['title'] ?? 'Test Questionnaire'
        ),
        description: $attributes['description'] ?? null
    );
}
```

### 4.3 Rector 配置 (rector.php)

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Laravel\Set\LaravelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/src/Console/stubs',
    ]);

    // PHP 8.2 語法升級
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        LaravelSetList::LARAVEL_110,
    ]);
    
    // 自訂規則
    $rectorConfig->rules([
        // 添加嚴格類型聲明
        \Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector::class,
    ]);
};
```

### 4.4 Pint 配置 (pint.json)

```json
{
    "preset": "laravel",
    "rules": {
        "declare_strict_types": true,
        "final_class": true,
        "global_namespace_import": {
            "import_classes": true,
            "import_constants": true,
            "import_functions": true
        },
        "ordered_imports": {
            "sort_algorithm": "alpha",
            "imports_order": [
                "class",
                "function",
                "const"
            ]
        },
        "single_line_empty_body": true,
        "trailing_comma_in_multiline": {
            "elements": [
                "arrays",
                "arguments",
                "parameters"
            ]
        },
        "native_function_invocation": {
            "include": ["@all"]
        },
        "native_constant_invocation": true
    },
    "exclude": [
        "src/Console/stubs"
    ]
}
```

---

## 5. 套件配置

### 5.1 spatie/laravel-event-sourcing 配置

```php
<?php
// config/event-sourcing.php

return [
    /*
     * 這些目錄將被掃描尋找 Projectors 和 Reactors
     * 它們會被自動註冊為監聽器
     */
    'projector_directories' => [
        app_path('Projectors'),
    ],

    'reactor_directories' => [
        app_path('Reactors'),
    ],

    /*
     * Event Store 相關設定
     */
    'stored_event_model' => \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent::class,
    
    'stored_event_repository' => \Spatie\EventSourcing\StoredEvents\Repositories\EloquentStoredEventRepository::class,

    /*
     * 快照配置
     */
    'snapshot_model' => \Spatie\EventSourcing\Snapshots\EloquentSnapshot::class,
    
    'snapshot_repository' => \Spatie\EventSourcing\Snapshots\EloquentSnapshotRepository::class,

    /*
     * 事件序列化器
     */
    'event_serializer' => \Spatie\EventSourcing\EventSerializers\JsonEventSerializer::class,

    /*
     * 是否自動發現 Event Handlers
     */
    'auto_discover_projectors_and_reactors' => true,

    /*
     * 事件儲存連接
     */
    'connection' => config('database.default'),

    /*
     * 事件表名
     */
    'table' => 'stored_events',
    
    'snapshot_table' => 'snapshots',

    /*
     * 是否使用隊列處理 Projectors
     */
    'queue' => env('EVENT_SOURCING_QUEUE', false),
];
```

### 5.2 整合到 questionnaire 配置

```php
<?php
// config/questionnaire.php

return [
    // ... 現有配置

    /*
    |--------------------------------------------------------------------------
    | Event Sourcing Configuration
    |--------------------------------------------------------------------------
    */
    'event_sourcing' => [
        // 是否啟用 Event Sourcing
        'enabled' => env('QUESTIONNAIRE_EVENT_SOURCING', true),
        
        // 快照閾值（每多少事件創建快照）
        'snapshot_threshold' => 10,
        
        // 是否異步處理投影
        'async_projections' => env('QUESTIONNAIRE_ASYNC_PROJECTIONS', false),
        
        // 投影隊列
        'projection_queue' => env('QUESTIONNAIRE_PROJECTION_QUEUE', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Static Analysis Configuration
    |--------------------------------------------------------------------------
    */
    'strict_mode' => [
        // 是否啟用嚴格模式（會影響驗證等行為）
        'enabled' => env('QUESTIONNAIRE_STRICT_MODE', true),
    ],
];
```

---

## 6. 安裝步驟

### 6.1 更新依賴

```bash
# 更新 composer.json 後
composer update

# 或者單獨安裝新套件
composer require spatie/laravel-event-sourcing:^7.0 ramsey/uuid:^4.7

# 安裝開發依賴
composer require --dev pestphp/pest:^3.0 \
    pestphp/pest-plugin-laravel:^3.0 \
    pestphp/pest-plugin-arch:^3.0 \
    larastan/larastan:^2.9 \
    phpstan/phpstan:^1.10 \
    rector/rector:^1.0
```

### 6.2 初始化 Pest

```bash
# 初始化 Pest
./vendor/bin/pest --init

# 將現有 PHPUnit 測試轉換為 Pest
./vendor/bin/pest --migrate
```

### 6.3 發布配置

```bash
# 發布 Event Sourcing 配置和遷移
php artisan vendor:publish --provider="Spatie\EventSourcing\EventSourcingServiceProvider" --tag="event-sourcing-config"
php artisan vendor:publish --provider="Spatie\EventSourcing\EventSourcingServiceProvider" --tag="event-sourcing-migrations"
```

---

## 7. 移除的依賴

| 套件 | 原因 |
|------|------|
| `phpunit/phpunit` | 改用 Pest PHP |

---

## 8. 依賴版本矩陣

| 套件 | Laravel 10 | Laravel 11 | Laravel 12 |
|------|------------|------------|------------|
| spatie/laravel-event-sourcing | ^7.0 | ^7.0 | ^7.0 |
| spatie/laravel-data | ^4.0 | ^4.0 | ^4.0 |
| larastan/larastan | ^2.9 | ^2.9 | ^2.9 |
| pestphp/pest | ^2.0 | ^3.0 | ^3.0 |

---

**下一篇**: [08-migration-guide.md](./08-migration-guide.md) - 遷移步驟與執行順序
