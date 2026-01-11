<template>
    <div>
        <v-radio-group
            v-model="modelValue"
            :error-messages="error"
        >
            <v-radio
                v-for="(option, index) in question.options"
                :key="index"
                :label="option"
                :value="option"
            />
        </v-radio-group>
        <div
            v-if="question.description"
            class="text-caption text-grey mt-1"
        >
            {{ question.description }}
        </div>
    </div>
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
