<template>
    <div class="w-64 border-r bg-white p-4">
        <h3 class="mb-4 font-semibold">Toolbox</h3>
        <div class="space-y-2">
            <div
                class="cursor-move rounded border bg-gray-50 p-2 text-sm hover:bg-gray-100"
                draggable="true"
                @dragstart="onDragStart($event, 'text')"
            >
                Text Input
            </div>
            <div
                class="cursor-move rounded border bg-gray-50 p-2 text-sm hover:bg-gray-100"
                draggable="true"
                @dragstart="onDragStart($event, 'textarea')"
            >
                Text Area
            </div>
            <div
                class="cursor-move rounded border bg-gray-50 p-2 text-sm hover:bg-gray-100"
                draggable="true"
                @dragstart="onDragStart($event, 'radio')"
            >
                Radio Group
            </div>
            <div
                class="cursor-move rounded border bg-gray-50 p-2 text-sm hover:bg-gray-100"
                draggable="true"
                @dragstart="onDragStart($event, 'checkbox')"
            >
                Checkbox Group
            </div>
            <div
                class="cursor-move rounded border bg-gray-50 p-2 text-sm hover:bg-gray-100"
                draggable="true"
                @dragstart="onDragStart($event, 'select')"
            >
                Select Dropdown
            </div>
        </div>
    </div>
</template>

<script setup>
    const onDragStart = (event, type) => {
        event.dataTransfer.effectAllowed = 'copy'
        event.dataTransfer.setData('application/json', JSON.stringify({ type }))
    }
</script>
