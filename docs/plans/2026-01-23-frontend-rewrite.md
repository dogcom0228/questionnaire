# Frontend Re-architecture & Modernization Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Completely modernize the frontend by replacing Vuetify with Tailwind CSS + Shadcn-vue, and implement a sophisticated "Builder" layout for the admin interface.

**Architecture:**

- **Stack:** Vue 3, Inertia.js, Tailwind CSS, Shadcn-vue (Radix UI), Lucide Icons.
- **Design Pattern:** Micro-components. Separation of concerns between "Visual Canvas" (preview) and "Property Editor" (configuration).
- **Styling:** "Zinc" neutral palette with a primary accent color. Modern, clean, spacious.

**Tech Stack:** Vue 3, Tailwind CSS, PostCSS, Radix Vue, clsx, tailwind-merge.

## Phase 1: Foundation & Cleanup

### Task 1: Uninstall Vuetify & Install Tailwind

**Files:**

- Modify: `package.json`
- Modify: `resources/js/questionnaire/main.js`
- Delete: `resources/js/questionnaire/vuetify.js`
- Create: `postcss.config.js`
- Create: `tailwind.config.js`
- Create: `resources/css/app.css`

**Step 1: Remove Vuetify dependencies**
Run:

```bash
npm uninstall vuetify vite-plugin-vuetify @mdi/font roboto-fontface
```

**Step 2: Install Tailwind and utilities**
Run:

```bash
npm install -D tailwindcss postcss autoprefixer
npm install clsx tailwind-merge class-variance-authority lucide-vue-next radix-vue
npx tailwindcss init -p
```

**Step 3: Configure Tailwind**
Modify `tailwind.config.js`:

```javascript
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        container: {
            center: true,
            padding: '2rem',
            screens: {
                '2xl': '1400px',
            },
        },
        extend: {
            colors: {
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                popover: {
                    DEFAULT: 'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))',
                },
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },
            },
            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },
        },
    },
    plugins: [],
}
```

**Step 4: Create CSS Variables (Shadcn Style)**
Create `resources/css/app.css`:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
    :root {
        --background: 0 0% 100%;
        --foreground: 240 10% 3.9%;
        --card: 0 0% 100%;
        --card-foreground: 240 10% 3.9%;
        --popover: 0 0% 100%;
        --popover-foreground: 240 10% 3.9%;
        --primary: 240 5.9% 10%;
        --primary-foreground: 0 0% 98%;
        --secondary: 240 4.8% 95.9%;
        --secondary-foreground: 240 5.9% 10%;
        --muted: 240 4.8% 95.9%;
        --muted-foreground: 240 3.8% 46.1%;
        --accent: 240 4.8% 95.9%;
        --accent-foreground: 240 5.9% 10%;
        --destructive: 0 84.2% 60.2%;
        --destructive-foreground: 0 0% 98%;
        --border: 240 5.9% 90%;
        --input: 240 5.9% 90%;
        --ring: 240 10% 3.9%;
        --radius: 0.5rem;
    }
}

@layer base {
    * {
        @apply border-border;
    }
    body {
        @apply bg-background text-foreground;
    }
}
```

**Step 5: Clean Main.js**
Modify `resources/js/questionnaire/main.js` to remove Vuetify plugin registration and import the new CSS.

**Step 6: Setup Utils**
Create `resources/js/questionnaire/Utils/cn.js`:

```javascript
import { clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs) {
    return twMerge(clsx(inputs))
}
```

## Phase 2: Core Components (Shadcn UI)

### Task 2: Implement UI Primitives

**Files:**

- Create: `resources/js/questionnaire/Components/ui/button/Button.vue`
- Create: `resources/js/questionnaire/Components/ui/input/Input.vue`
- Create: `resources/js/questionnaire/Components/ui/card/Card.vue`
- Create: `resources/js/questionnaire/Components/ui/label/Label.vue`
- Create: `resources/js/questionnaire/Components/ui/switch/Switch.vue`

**Step 1: Create Button Component**
(Standard Shadcn Button implementation using `cva`)

**Step 2: Create Input Component**
(Standard Shadcn Input)

**Step 3: Create Card Components**
(Card, CardHeader, CardTitle, CardContent)

**Step 4: Create Label & Switch**
(For form toggles)

## Phase 3: Layout Redesign

### Task 3: New Admin Layout

**Files:**

- Modify: `resources/js/questionnaire/Layouts/AdminLayout.vue`
- Create: `resources/js/questionnaire/Components/app/Sidebar.vue`
- Create: `resources/js/questionnaire/Components/app/TopHeader.vue`

**Step 1: Create Sidebar**

- Collapsible vertical navigation.
- Links: Dashboard (Home icon), My Questionnaires (FileText icon), Settings (Settings icon).
- Styling: `w-64 border-r bg-card h-screen`.

**Step 2: Create TopHeader**

- Breadcrumbs.
- User profile dropdown.
- Styling: `h-14 border-b flex items-center px-4`.

**Step 3: Assemble AdminLayout**

- Flex row container.
- Sidebar fixed left.
- Main content area `flex-1 flex flex-col`.
- Slot for page content.

## Phase 4: Builder Page (The "Jewel")

### Task 4: Builder State Management

**Files:**

- Create: `resources/js/questionnaire/Composables/useBuilder.js`

**Step 1: Define Builder Store**

- Reactive state: `selectedQuestionId`, `form`, `isDragging`.
- Actions: `selectQuestion(id)`, `addQuestion(type)`, `updateQuestion(id, data)`, `removeQuestion(id)`.

### Task 5: Builder Layout Structure

**Files:**

- Modify: `resources/js/questionnaire/Pages/Admin/Edit.vue`
- Create: `resources/js/questionnaire/Components/builder/BuilderCanvas.vue`
- Create: `resources/js/questionnaire/Components/builder/PropertiesPanel.vue`
- Create: `resources/js/questionnaire/Components/builder/Toolbox.vue`

**Step 1: Edit Page Scaffold**

- Remove all existing code.
- Layout: 3 Columns.
    - Col 1 (Toolbox): `w-64 border-r`.
    - Col 2 (Canvas): `flex-1 bg-muted/50 p-8 overflow-y-auto`.
    - Col 3 (Properties): `w-80 border-l bg-card`.

**Step 2: Toolbox**

- List of draggable question types (Text, Multiple Choice, Date, etc.).
- Use `vuedraggable` (already installed) or simple click-to-add.

### Task 6: Canvas & Question Cards

**Files:**

- Create: `resources/js/questionnaire/Components/builder/QuestionCard.vue`
- Modify: `resources/js/questionnaire/Components/builder/BuilderCanvas.vue`

**Step 1: Question Card**

- Props: `question`, `isSelected`.
- Visuals: White card, shadow-sm.
- Selected state: `ring-2 ring-primary`.
- Content: Read-only preview of the question (Label + Input).

**Step 2: Canvas Integration**

- Render list of `QuestionCard` using `vuedraggable`.
- Bind click to `selectQuestion`.

### Task 7: Properties Panel

**Files:**

- Modify: `resources/js/questionnaire/Components/builder/PropertiesPanel.vue`
- Create: `resources/js/questionnaire/Components/builder/properties/CommonProperties.vue`
- Create: `resources/js/questionnaire/Components/builder/properties/TextProperties.vue`
- Create: `resources/js/questionnaire/Components/builder/properties/ChoiceProperties.vue`

**Step 1: Panel Logic**

- If `!selectedQuestion`: Show Form Settings (Title, Desc).
- If `selectedQuestion`: Show specific properties based on `question.type`.

**Step 2: Common Properties**

- Inputs for `label`, `description`, `required` switch.

**Step 3: Choice Properties**

- Dynamic list for adding/removing options (for Radio/Checkbox).

## Phase 5: Dashboard & List Views

### Task 8: Dashboard & Questionnaire List

**Files:**

- Modify: `resources/js/questionnaire/Pages/Admin/Index.vue`
- Create: `resources/js/questionnaire/Components/ui/table/Table.vue` (Shadcn)

**Step 1: List View**

- Replace Vuetify data table.
- Use Shadcn Table component.
- Columns: Status (Badge), Title, Responses (Count), Created At.
- Actions: Edit, Preview, Delete (DropdownMenu).

## Phase 6: Public View (Filling)

### Task 9: Modern Public Layout

**Files:**

- Modify: `resources/js/questionnaire/Layouts/PublicLayout.vue`
- Modify: `resources/js/questionnaire/Pages/Public/Fill.vue`

**Step 1: Public Layout**

- clean, centered single-column layout.
- `max-w-3xl mx-auto`.

**Step 2: Fill Page**

- Render questions vertically with generous spacing (`space-y-8`).
- Smooth scrolling.
- Modern "Submit" button at the bottom.
