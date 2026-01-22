<template>
    <Card
        :class="[
            'relative bg-white shadow-sm transition-all',
            isSelected ? 'ring-2 ring-primary' : '',
        ]"
    >
        <CardHeader>
            <CardTitle>{{ question.label }}</CardTitle>
        </CardHeader>
        <CardContent>
            <Input
                v-if="question.type === 'text'"
                disabled
                placeholder="Text input preview"
            />
            <div
                v-else
                class="text-gray-400 italic"
            >
                Input type '{{ question.type }}' preview
            </div>
        </CardContent>
    </Card>
</template>

<script setup>
    import { computed } from 'vue'
    import { useBuilder } from '@questionnaire/Composables/useBuilder'
    import Card from '@questionnaire/Components/ui/card/Card.vue'
    import CardHeader from '@questionnaire/Components/ui/card/CardHeader.vue'
    import CardTitle from '@questionnaire/Components/ui/card/CardTitle.vue'
    import CardContent from '@questionnaire/Components/ui/card/CardContent.vue'
    import Input from '@questionnaire/Components/ui/input/Input.vue'

    const props = defineProps({
        question: {
            type: Object,
            required: true,
        },
        index: {
            type: Number,
            required: true,
        },
    })

    const { selectedQuestionId } = useBuilder()

    const isSelected = computed(
        () => selectedQuestionId.value === props.question.id
    )
</script>
