<template>
    <v-card
        class="properties-panel"
        flat
    >
        <v-card-title>Properties</v-card-title>
        <v-card-text v-if="question">
            <common-properties
                :question="question"
                class="mb-4"
            />

            <text-properties
                v-if="isTextType"
                :question="question"
            />

            <choice-properties
                v-if="isChoiceType"
                :question="question"
            />
        </v-card-text>
        <v-card-text
            v-else
            class="text-center text-medium-emphasis mt-4"
        >
            Select a question to edit properties
        </v-card-text>
    </v-card>
</template>

<script setup>
    import { computed } from 'vue'
    import CommonProperties from './properties/CommonProperties.vue'
    import TextProperties from './properties/TextProperties.vue'
    import ChoiceProperties from './properties/ChoiceProperties.vue'

    const props = defineProps({
        question: {
            type: Object,
            default: null,
        },
    })

    const isTextType = computed(() => {
        if (!props.question) return false
        return ['text', 'email', 'date', 'number'].includes(props.question.type)
    })

    const isChoiceType = computed(() => {
        if (!props.question) return false
        return ['select', 'radio', 'checkbox'].includes(props.question.type)
    })
</script>
