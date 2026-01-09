# Laravel Questionnaire Package

一個功能完整、高度可客製化的 Laravel 問卷/調查系統 Composer Package，支援 Vue 3 + Vuetify 3 + Inertia.js 前端。

## 特性

- ✅ **Auto-Discovery**: 安裝後自動註冊，最小配置即可運作
- ✅ **高度可客製化**: Controller、Service、Repository、Policy 皆可覆寫
- ✅ **現代化前端**: Vue 3 + Vuetify 3 + Inertia.js
- ✅ **可擴充題型**: 內建 7 種題型，支援自訂題型
- ✅ **重複提交防護**: 支援多種策略（每人一次、每 Session 一次、每 IP 一次等）
- ✅ **完整授權**: Policy-based 授權機制
- ✅ **事件驅動**: 關鍵操作皆觸發事件
- ✅ **匯出功能**: CSV 匯出（支援 UTF-8 BOM）
- ✅ **RESTful API**: 可選的 API 端點

## 系統需求

- PHP 8.2+
- Laravel 11/12
- Node.js 18+
- Vue 3.4+
- Vuetify 3.5+

## 安裝

### 1. 安裝 Package

```bash
composer require liangjin0228/questionnaire
```

### 2. 發布配置和資源

```bash
# 發布配置文件
php artisan vendor:publish --tag=questionnaire-config

# 發布遷移文件
php artisan vendor:publish --tag=questionnaire-migrations

# 發布視圖文件（可選，用於自訂模板）
php artisan vendor:publish --tag=questionnaire-views

# 發布前端資源（可選，用於自訂前端）
php artisan vendor:publish --tag=questionnaire-frontend

# 發布所有資源
php artisan vendor:publish --provider="Liangjin0228\Questionnaire\QuestionnaireServiceProvider"
```

### 3. 運行遷移

```bash
php artisan migrate
```

### 4. 安裝前端依賴

```bash
npm install @inertiajs/vue3 vue vuetify @mdi/font vuedraggable
npm install -D vite-plugin-vuetify
```

### 5. 配置 Vite

在你的 `vite.config.js` 中加入：

```javascript
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vuetify from 'vite-plugin-vuetify';

export default defineConfig({
    plugins: [
        vue(),
        vuetify({ autoImport: true }),
    ],
    resolve: {
        alias: {
            '@questionnaire': 'vendor/liangjin0228/questionnaire/resources/js/questionnaire',
        },
    },
});
```

### 6. 完成

訪問 `/questionnaire/admin` 開始使用！

## 快速開始

### 使用 Artisan 命令

```bash
# 查看安裝說明
php artisan questionnaire:install

# 列出所有可用的題型
php artisan questionnaire:question-types
```

## 配置

### 功能開關

```php
// config/questionnaire.php
'features' => [
    'admin' => true,            // 管理後台
    'public_fill' => true,      // 公開填答頁面
    'results' => true,          // 結果查看
    'api' => false,             // RESTful API
    'frontend' => true,         // Inertia 前端
    'authorization' => true,    // 授權檢查
    'export_csv' => true,       // CSV 匯出
],
```

### 路由配置

```php
'routes' => [
    'prefix' => 'questionnaire',
    'admin_prefix' => 'admin',
    'public_prefix' => 'survey',
    'middleware' => ['web'],
    'admin_middleware' => ['web', 'auth'],
],
```

### 自訂 Controller

```php
'controllers' => [
    'web' => \App\Http\Controllers\CustomQuestionnaireController::class,
    'api' => \App\Http\Controllers\Api\CustomQuestionnaireApiController::class,
],
```

### 覆寫 Service

```php
'services' => [
    'questionnaire_repository' => \App\Repositories\CustomQuestionnaireRepository::class,
    'response_repository' => \App\Repositories\CustomResponseRepository::class,
    'validation_strategy' => \App\Validation\CustomValidationStrategy::class,
],
```

## 自訂題型

### 1. 創建題型類別

```php
<?php

namespace App\QuestionTypes;

use Liangjin0228\Questionnaire\QuestionTypes\AbstractQuestionType;

class RatingQuestionType extends AbstractQuestionType
{
    public function getIdentifier(): string
    {
        return 'rating';
    }

    public function getName(): string
    {
        return 'Rating';
    }

    public function getDescription(): string
    {
        return 'A star rating question';
    }

    public function getIcon(): string
    {
        return 'mdi-star';
    }

    public function getValidationRules(array $question): array
    {
        $rules = [];
        
        if ($question['required'] ?? false) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        
        $rules[] = 'integer';
        $rules[] = 'min:1';
        $rules[] = 'max:' . ($question['settings']['max'] ?? 5);
        
        return $rules;
    }

    public function getDefaultSettings(): array
    {
        return [
            'min' => 1,
            'max' => 5,
        ];
    }
}
```

### 2. 註冊題型

```php
// config/questionnaire.php
'question_types' => [
    // ... 內建題型
    'rating' => \App\QuestionTypes\RatingQuestionType::class,
],
```

### 3. 創建前端組件

```vue
<!-- resources/js/Components/QuestionTypes/RatingInput.vue -->
<template>
  <v-rating
    v-model="modelValue"
    :length="question.settings?.max ?? 5"
    hover
  />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps(['modelValue', 'question', 'error']);
const emit = defineEmits(['update:modelValue']);

const modelValue = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
});
</script>
```

## 事件

Package 在關鍵操作時會觸發事件：

| 事件 | 說明 |
|------|------|
| `QuestionnaireCreated` | 問卷創建後 |
| `QuestionnaireUpdated` | 問卷更新後 |
| `QuestionnairePublished` | 問卷發布後 |
| `QuestionnaireClosed` | 問卷關閉後 |
| `QuestionnaireDeleted` | 問卷刪除後 |
| `ResponseSubmitted` | 回覆提交後 |
| `ResponseDeleted` | 回覆刪除後 |

### 監聽事件

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    \Liangjin0228\Questionnaire\Events\ResponseSubmitted::class => [
        \App\Listeners\SendResponseNotification::class,
        \App\Listeners\CalculateScore::class,
    ],
];
```

## 重複提交防護

### 內建策略

| 策略 | 說明 |
|------|------|
| `allow_multiple` | 允許多次提交 |
| `one_per_user` | 每用戶只能提交一次 |
| `one_per_session` | 每 Session 只能提交一次 |
| `one_per_ip` | 每 IP 只能提交一次 |

### 自訂策略

```php
<?php

namespace App\Guards;

use Liangjin0228\Questionnaire\Contracts\DuplicateSubmissionGuardInterface;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class OnePerDeviceGuard implements DuplicateSubmissionGuardInterface
{
    public function check(Questionnaire $questionnaire): bool
    {
        $deviceId = request()->fingerprint();
        
        return !$questionnaire->responses()
            ->where('metadata->device_id', $deviceId)
            ->exists();
    }

    public function getMessage(): string
    {
        return 'You have already submitted from this device.';
    }
}
```

註冊：

```php
// config/questionnaire.php
'duplicate_guards' => [
    // ... 內建策略
    'one_per_device' => \App\Guards\OnePerDeviceGuard::class,
],
```

## 授權

### 自訂 Policy

```php
// 發布 Policy stub
php artisan vendor:publish --tag=questionnaire-stubs

// 修改 config/questionnaire.php
'policies' => [
    'questionnaire' => \App\Policies\CustomQuestionnairePolicy::class,
],
```

### 停用授權

```php
'features' => [
    'authorization' => false,
],
```

## API 使用

### 啟用 API

```php
'features' => [
    'api' => true,
],
```

### 端點

| 方法 | 端點 | 說明 |
|------|------|------|
| GET | `/api/questionnaire` | 列出問卷 |
| POST | `/api/questionnaire` | 創建問卷 |
| GET | `/api/questionnaire/{id}` | 取得問卷 |
| PUT | `/api/questionnaire/{id}` | 更新問卷 |
| DELETE | `/api/questionnaire/{id}` | 刪除問卷 |
| POST | `/api/questionnaire/{id}/publish` | 發布問卷 |
| POST | `/api/questionnaire/{id}/responses` | 提交回覆 |
| GET | `/api/questionnaire/{id}/responses` | 取得回覆 |

## 前端客製化

### 覆寫頁面組件

1. 發布前端資源：

```bash
php artisan vendor:publish --tag=questionnaire-frontend
```

2. 修改發布的 Vue 組件

3. 更新 Vite alias 指向你的組件目錄

### 覆寫佈局

創建自訂 Layout 並在頁面中使用：

```vue
<!-- resources/js/Layouts/CustomAdminLayout.vue -->
<template>
  <v-app>
    <!-- 你的自訂佈局 -->
    <slot />
  </v-app>
</template>
```

## 測試

```bash
# 運行測試
php artisan test --filter=Questionnaire

# 或使用 PHPUnit
./vendor/bin/phpunit packages/questionnaire
```

## 目錄結構

```
src/
├── Console/              # Artisan 命令
├── Contracts/            # 介面定義
├── Events/               # 事件
├── Exceptions/           # 例外
├── Export/               # 匯出功能
├── Guards/               # 重複提交防護
├── Http/
│   ├── Controllers/      # 控制器
│   └── Requests/         # 表單請求
├── Listeners/            # 事件監聽器
├── Models/               # Eloquent 模型
├── Policies/             # 授權策略
├── QuestionTypes/        # 題型
├── Repositories/         # 資料庫存取
├── Services/             # 業務邏輯
└── QuestionnaireServiceProvider.php
```

## Changelog

請參閱 [CHANGELOG.md](CHANGELOG.md)。

## License

MIT License. 請參閱 [LICENSE](LICENSE)。

## 貢獻

歡迎提交 Pull Request！請確保：

1. 遵循 PSR-12 代碼風格
2. 包含相關測試
3. 更新文檔

## 支援

如有問題，請在 GitHub Issues 提交。
