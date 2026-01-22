# Builder Layout Structure Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task.

**Goal:** Completely replace the Vuetify-based `Edit.vue` with a Tailwind CSS 3-column layout using `AdminLayout`.

**Architecture:**

- **Layout:** `AdminLayout` wrapper -> Flex container (3 columns)
- **State Management:** `useBuilder` composable (Pinia store) for initializing and managing state.
- **Components:**
    - `Toolbox`: Left sidebar (draggable components)
    - `BuilderCanvas`: Center area (droppable canvas)
    - `PropertiesPanel`: Right sidebar (configuration)

**Tech Stack:** Vue 3, Tailwind CSS, Pinia, Inertia.js (for props), vuedraggable (for DnD).

### Task 1: Create Placeholder Components

Create the three main structural components with basic Tailwind styling and placeholder content.

**Files:**

- Create: `resources/js/questionnaire/Components/Builder/Toolbox.vue`
- Create: `resources/js/questionnaire/Components/Builder/BuilderCanvas.vue`
- Create: `resources/js/questionnaire/Components/Builder/PropertiesPanel.vue`

**Step 1: Create Toolbox.vue**

```vue
<template>
    <div class="w-64 border-r bg-white p-4 flex flex-col h-full">
        <h3 class="font-bold mb-4">Toolbox</h3>
        <div class="space-y-2">
            <!-- Placeholder items -->
            <div class="p-2 border rounded bg-gray-50 cursor-move">
                Text Input
            </div>
            <div class="p-2 border rounded bg-gray-50 cursor-move">
                Multiple Choice
            </div>
        </div>
    </div>
</template>

<script setup></script>
```

**Step 2: Create BuilderCanvas.vue**

```vue
<template>
    <div class="flex-1 bg-gray-100 p-8 overflow-y-auto h-full">
        <div
            class="max-w-3xl mx-auto bg-white min-h-[500px] shadow-sm rounded-lg p-8"
        >
            <div class="text-center text-gray-400 mt-10">
                Drag components here
            </div>
        </div>
    </div>
</template>

<script setup></script>
```

**Step 3: Create PropertiesPanel.vue**

```vue
<template>
    <div class="w-80 border-l bg-white p-4 flex flex-col h-full">
        <h3 class="font-bold mb-4">Properties</h3>
        <div class="text-sm text-gray-500">
            Select an element to edit properties
        </div>
    </div>
</template>

<script setup></script>
```

### Task 2: Rewrite Edit.vue Layout

Replace the entire content of `Edit.vue` with the new structure. Initialize the builder store.

**Files:**

- Modify: `resources/js/questionnaire/Pages/Admin/Edit.vue`
- Read: `resources/js/questionnaire/Stores/useBuilder.js` (to verify initialization method)

**Step 1: Verify Store Initialization**
Read `useBuilder.js` to confirm `initialize` method signature.

**Step 2: Rewrite Edit.vue**

```vue
<template>
    <AdminLayout>
        <!-- Builder Layout: Fixed height minus header/padding -->
        <div class="flex h-[calc(100vh-64px)] overflow-hidden">
            <Toolbox />
            <BuilderCanvas />
            <PropertiesPanel />
        </div>
    </AdminLayout>
</template>

<script setup>
    import { onMounted } from 'vue'
    import AdminLayout from '../../Layouts/AdminLayout.vue'
    import Toolbox from '../../Components/Builder/Toolbox.vue'
    import BuilderCanvas from '../../Components/Builder/BuilderCanvas.vue'
    import PropertiesPanel from '../../Components/Builder/PropertiesPanel.vue'
    import { useBuilder } from '../../Stores/useBuilder'

    const props = defineProps({
        questionnaire: {
            type: Object,
            required: true,
        },
        questionTypes: {
            type: Array,
            default: () => [],
        },
    })

    const builder = useBuilder()

    onMounted(() => {
        // Initialize store with questionnaire data
        builder.initialize(props.questionnaire)
    })
</script>
```

### Task 3: Build Verification

Run the build process to ensure all imports and paths are correct and Tailwind is processing the new classes.

**Step 1: Run Build**
`npm run build`

**Step 2: Verify Output**
Check for build errors.
