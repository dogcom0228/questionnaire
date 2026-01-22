# Laravel Questionnaire Package - AI Instructions

## Project Overview

This is a **Laravel Package** for managing questionnaires, featuring a **Vue 3 + Vuetify 3 + Inertia.js** frontend.

- **Language**: PHP 8.2+, JavaScript (Vue 3).
- **Core Stack**: Laravel 10+, Inertia.js, Vuetify 3, Spatie Laravel Data.
- **Design Philosophy**: SOLID, Config-Driven, Contract-First.

## Architectural Patterns

### 1. Contract-First Development

- **Interfaces are Mandatory**: Every service, action, and repository MUST have a corresponding interface in `src/Contracts/`.
- **Dependency Injection**: Always type-hint interfaces, not concrete classes.
- **Bindings**: Register bindings in `src/QuestionnaireServiceProvider.php`.

### 2. Action-Driven Logic

- Business logic resides in **Actions** (`src/Services/` implementing `src/Contracts/Actions/`).
- **Controllers** should be thin and delegate to Actions.
- **DTOs**: Use `Spatie\LaravelData` DTOs (`src/DTOs/`) for passing data to Actions.
    - Example: `CreateQuestionnaireAction::execute(QuestionnaireData $data)`.

### 3. Question Type System

- **Extensibility**: New question types are plugins implementing `Liangjin0228\Questionnaire\Contracts\QuestionTypeInterface`.
- **Registration**: Register new types in `QuestionnaireServiceProvider` using the `QuestionTypeRegistry`.
- **Frontend**: Each question type requires a corresponding Vue component in `resources/js/questionnaire/Components/QuestionTypes/`.

### 4. Frontend Architecture (Inertia + Vuetify)

- **Path**: `resources/js/questionnaire/`.
- **Alias**: `@questionnaire` points to `resources/js/questionnaire`.
- **Components**: Use Vuetify 3 components (`v-card`, `v-btn`, etc.).
- **Build**: Uses Vite. Run `npm run dev` for development, `npm run build` for production.

## Coding Standards & Conventions

### PHP / Laravel

- **Strict Types**: Always use `declare(strict_types=1);`.
- **Enums**: Use PHP Enums (`src/Enums/`) instead of magic strings/constants.
- **Return Types**: Explicitly declare return types for all methods.
- **Config**: Use `config('questionnaire.key')` for configurable values. Avoid hardcoding.

### Testing

- **Framework**: PHPUnit (`vendor/bin/phpunit`).
- **Structure**: Tests located in `tests/Feature` and `tests/Unit`.
- **Orchestra Testbench**: Used for testing the package in a Laravel environment.

## Critical Workflows

- **Adding a new Question Type**:
    1. Create class in `src/QuestionTypes/` implementing `QuestionTypeInterface`.
    2. Register in `QuestionnaireServiceProvider`.
    3. Create Vue component in `resources/js/questionnaire/Components/QuestionTypes/`.
    4. Add to `src/Enums/QuestionType.php` if applicable.

- **Making API Changes**:
    1. Update `src/DTOs` if data structure changes.
    2. Update Contract interface.
    3. Update Implementation.
    4. Update Controller/API resource.

## Common Paths

- **Contracts**: `src/Contracts/`
- **Actions**: `src/Services/` (implementing `src/Contracts/Actions/`)
- **DTOs**: `src/DTOs/`
- **Models**: `src/Models/`
- **Frontend Source**: `resources/js/questionnaire/`
- **Config**: `config/questionnaire.php`
