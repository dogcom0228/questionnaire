<template>
    <component
        :is="componentLoader"
        v-model="modelValue"
        :question="question"
        :error="error"
    />
</template>

<script setup>
    import { computed, defineAsyncComponent, h } from 'vue'

    const props = defineProps({
        modelValue: {
            type: [String, Number, Array, Boolean],
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

    // Internal registry of default components
    const internalRegistry = {
        text: () => import('./QuestionTypes/TextInput.vue'),
        textarea: () => import('./QuestionTypes/TextareaInput.vue'),
        radio: () => import('./QuestionTypes/RadioInput.vue'),
        checkbox: () => import('./QuestionTypes/CheckboxInput.vue'),
        select: () => import('./QuestionTypes/SelectInput.vue'),
        number: () => import('./QuestionTypes/NumberInput.vue'),
        date: () => import('./QuestionTypes/DateInput.vue'),
    }

    /**
     * Dynamic Component Loader
     *
     * Priority:
     * 1. Global Registry (window.Questionnaire.registry) - Allows host app overrides (Shadowing)
     * 2. Internal Registry - Package defaults
     */
    const componentLoader = computed(() => {
        const type = props.question.type

        return defineAsyncComponent({
            loader: () => {
                // Check for host app override
                if (
                    typeof window !== 'undefined' &&
                    window.Questionnaire &&
                    window.Questionnaire.registry &&
                    window.Questionnaire.registry[type]
                ) {
                    return window.Questionnaire.registry[type]()
                }

                // Fallback to internal registry
                if (internalRegistry[type]) {
                    return internalRegistry[type]()
                }

                return Promise.reject(
                    new Error(`Unknown question type: ${type}`)
                )
            },
            loadingComponent: {
                render() {
                    return h(
                        'div',
                        { class: 'p-4 text-gray-500 animate-pulse' },
                        'Loading question...'
                    )
                },
            },
            errorComponent: {
                render() {
                    return h(
                        'div',
                        { class: 'p-4 text-red-500' },
                        `Error: Unsupported question type '${type}'`
                    )
                },
            },
            delay: 200, // Delay before showing loading component
        })
    })
</script>
