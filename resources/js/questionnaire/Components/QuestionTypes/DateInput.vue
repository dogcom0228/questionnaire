<template>
    <v-text-field
        v-model="modelValue"
        :label="question.content"
        :hint="question.description"
        :error-messages="error"
        :min="question.settings?.min_date"
        :max="question.settings?.max_date"
        type="date"
        variant="outlined"
        persistent-hint
    />
</template>

<script setup>
    import { computed } from 'vue'

    const props = defineProps({
        modelValue: {
            type: String,
            default: '',
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
