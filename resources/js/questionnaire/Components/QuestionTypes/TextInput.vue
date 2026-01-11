<template>
    <v-text-field
        v-model="modelValue"
        :label="question.content"
        :placeholder="question.settings?.placeholder || ''"
        :hint="question.description"
        :error-messages="error"
        :counter="question.settings?.max_length"
        :maxlength="question.settings?.max_length"
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
