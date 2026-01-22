<template>
    <Card class="properties-panel border-l-0 rounded-none h-full">
        <CardHeader>
            <CardTitle>Properties</CardTitle>
        </CardHeader>
        <CardContent v-if="question">
            <common-properties
                :question="question"
                class="mb-6 border-b pb-6"
            />

            <text-properties
                v-if="isTextType"
                :question="question"
            />

            <choice-properties
                v-if="isChoiceType"
                :question="question"
            />
        </CardContent>
        <CardContent
            v-else
            class="text-center text-muted-foreground mt-4"
        >
            Select a question to edit properties
        </CardContent>
    </Card>
</template>

<script setup>
    import { computed } from 'vue'
    import CommonProperties from './properties/CommonProperties.vue'
    import TextProperties from './properties/TextProperties.vue'
    import ChoiceProperties from './properties/ChoiceProperties.vue'
    import {
        Card,
        CardHeader,
        CardTitle,
        CardContent,
    } from '@/questionnaire/Components/ui/card'

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
