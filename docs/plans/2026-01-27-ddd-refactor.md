# DDD Refactoring Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Reorganize the current Laravel structure into a Domain Driven Design (DDD) structure.

**Architecture:** Move Models, Enums, Events, Controllers, and Repositories into Domain and Infrastructure layers. Update namespaces for moved files.

**Tech Stack:** PHP, Laravel

### Task 1: Create Directory Structure

**Files:**

- Create directories:
  - `src/Domain/Questionnaire/Models`
  - `src/Domain/Questionnaire/Enums`
  - `src/Domain/Questionnaire/Events`
  - `src/Domain/Question/Models`
  - `src/Domain/Response/Models`
  - `src/Infrastructure/Http/Controllers`
  - `src/Infrastructure/Persistence/Repositories`

**Step 1: Create Directories**

Run:

```bash
mkdir -p src/Domain/Questionnaire/Models
mkdir -p src/Domain/Questionnaire/Enums
mkdir -p src/Domain/Questionnaire/Events
mkdir -p src/Domain/Question/Models
mkdir -p src/Domain/Response/Models
mkdir -p src/Infrastructure/Http/Controllers
mkdir -p src/Infrastructure/Persistence/Repositories
```

**Step 2: Verify Creation**

Run: `ls -R src/Domain src/Infrastructure`
Expected: List of created directories.

### Task 2: Move Questionnaire Domain Files

**Files:**

- Move: `src/Models/Questionnaire.php` -> `src/Domain/Questionnaire/Models/Questionnaire.php`
- Move: `src/Enums/QuestionnaireStatus.php` -> `src/Domain/Questionnaire/Enums/QuestionnaireStatus.php`
- Move: `src/Events/Questionnaire*.php` -> `src/Domain/Questionnaire/Events/`

**Step 1: Move Files**

Run:

```bash
mv src/Models/Questionnaire.php src/Domain/Questionnaire/Models/
mv src/Enums/QuestionnaireStatus.php src/Domain/Questionnaire/Enums/
mv src/Events/Questionnaire*.php src/Domain/Questionnaire/Events/
```

**Step 2: Update Namespaces (Questionnaire Model)**

Modify `src/Domain/Questionnaire/Models/Questionnaire.php`:
Change `namespace Liangjin0228\Questionnaire\Models;`
to `namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Models;`

**Step 3: Update Namespaces (QuestionnaireStatus Enum)**

Modify `src/Domain/Questionnaire/Enums/QuestionnaireStatus.php`:
Change `namespace Liangjin0228\Questionnaire\Enums;`
to `namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Enums;`

**Step 4: Update Namespaces (Questionnaire Events)**

Modify all moved event files in `src/Domain/Questionnaire/Events/`:
Change `namespace Liangjin0228\Questionnaire\Events;`
to `namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Events;`

**Step 5: Commit**

```bash
git add src/Domain/Questionnaire
git rm src/Models/Questionnaire.php src/Enums/QuestionnaireStatus.php src/Events/Questionnaire*.php
git commit -m "refactor: move Questionnaire domain files"
```

### Task 3: Move Question Domain Files

**Files:**

- Move: `src/Models/Question.php` -> `src/Domain/Question/Models/Question.php`

**Step 1: Move Files**

Run:

```bash
mv src/Models/Question.php src/Domain/Question/Models/
```

**Step 2: Update Namespace**

Modify `src/Domain/Question/Models/Question.php`:
Change `namespace Liangjin0228\Questionnaire\Models;`
to `namespace Liangjin0228\Questionnaire\Domain\Question\Models;`

**Step 3: Commit**

```bash
git add src/Domain/Question
git rm src/Models/Question.php
git commit -m "refactor: move Question domain files"
```

### Task 4: Move Response Domain Files

**Files:**

- Move: `src/Models/Response.php` -> `src/Domain/Response/Models/Response.php`
- Move: `src/Models/Answer.php` -> `src/Domain/Response/Models/Answer.php`

**Step 1: Move Files**

Run:

```bash
mv src/Models/Response.php src/Domain/Response/Models/
mv src/Models/Answer.php src/Domain/Response/Models/
```

**Step 2: Update Namespaces**

Modify `src/Domain/Response/Models/Response.php` and `src/Domain/Response/Models/Answer.php`:
Change `namespace Liangjin0228\Questionnaire\Models;`
to `namespace Liangjin0228\Questionnaire\Domain\Response\Models;`

**Step 3: Commit**

```bash
git add src/Domain/Response
git rm src/Models/Response.php src/Models/Answer.php
git commit -m "refactor: move Response domain files"
```

### Task 5: Move Infrastructure Files

**Files:**

- Move: `src/Http/Controllers/*` -> `src/Infrastructure/Http/Controllers/`
- Move: `src/Repositories/*` -> `src/Infrastructure/Persistence/Repositories/`

**Step 1: Move Files**

Run:

```bash
mv src/Http/Controllers/* src/Infrastructure/Http/Controllers/
mv src/Repositories/* src/Infrastructure/Persistence/Repositories/
```

**Step 2: Update Namespaces (Controllers)**

Modify files in `src/Infrastructure/Http/Controllers/`:
Change `namespace Liangjin0228\Questionnaire\Http\Controllers;`
to `namespace Liangjin0228\Questionnaire\Infrastructure\Http\Controllers;`

**Step 3: Update Namespaces (Repositories)**

Modify files in `src/Infrastructure/Persistence/Repositories/`:
Change `namespace Liangjin0228\Questionnaire\Repositories;`
to `namespace Liangjin0228\Questionnaire\Infrastructure\Persistence\Repositories;`

**Step 4: Commit**

```bash
git add src/Infrastructure
git rm -r src/Http/Controllers src/Repositories
git commit -m "refactor: move Infrastructure files"
```

### Task 6: Final Verification & List Output

**Step 1: Verify Directory Structure**

Run: `ls -R src/Domain src/Infrastructure`

**Step 2: Remove Empty Directories (Optional)**

Run:

```bash
rmdir src/Models src/Enums src/Events src/Http/Controllers src/Repositories 2>/dev/null || true
```

(Note: Directories might not be empty if other files exist, so ignore errors)
