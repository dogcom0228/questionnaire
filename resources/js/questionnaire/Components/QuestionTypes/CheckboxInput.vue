<template>
    <div>
        <div
            v-for="(option, index) in question.options"
            :key="index"
        >
            <v-checkbox
                v-model="selectedOptions"
                :label="option"
                :value="option"
                density="compact"
                hide-details
            />
        </div>
        <div
            v-if="error"
            class="text-error text-caption mt-1"
        >
            {{ error }}
        </div>
        <div
            v-if="question.description"
            class="text-caption text-grey mt-2"
        >
            {{ question.description }}
        </div>
    </div>
</template>

<script setup>
    import { computed } from 'vue'

    const props = defineProps({
        modelValue: {
            type: Array,
            default: () => [],
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

    const selectedOptions = computed({
        get: () => props.modelValue,
        set: (value) => emit('update:modelValue', value),
    })
</script>
