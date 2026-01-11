<template>
    <v-text-field
        v-model.number="modelValue"
        :label="question.content"
        :hint="question.description"
        :error-messages="error"
        :min="question.settings?.min"
        :max="question.settings?.max"
        :step="question.settings?.step || 1"
        type="number"
        variant="outlined"
        persistent-hint
    />
</template>

<script setup>
    import { computed } from 'vue'

    const props = defineProps({
        modelValue: {
            type: [Number, String],
            default: null,
        },
        question: {
            type: Object,
            required: true,
        },
        error: {
            type: String,
            default: null,
        },
    })

    const emit = defineEmits(['update:modelValue'])

    const modelValue = computed({
        get: () => props.modelValue,
        set: (value) => emit('update:modelValue', value),
    })
</script>
