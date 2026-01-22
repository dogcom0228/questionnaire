<template>
    <div class="h-full bg-gray-50 overflow-y-auto p-8">
        <div class="max-w-3xl mx-auto">
            <draggable
                v-model="form.questions"
                item-key="id"
                class="space-y-4 min-h-[200px]"
                group="questions"
            >
                <template #item="{ element, index }">
                    <div @click="selectQuestion(element.id)">
                        <QuestionCard
                            :question="element"
                            :index="index"
                        />
                    </div>
                </template>
                <template #footer>
                    <div
                        v-if="form.questions.length === 0"
                        class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center text-gray-500"
                    >
                        Drag or Click to add questions
                    </div>
                </template>
            </draggable>
        </div>
    </div>
</template>

<script setup>
    import { useBuilder } from '@questionnaire/Composables/useBuilder'
    import draggable from 'vuedraggable'
    import QuestionCard from './QuestionCard.vue'

    const { form, selectQuestion } = useBuilder()
</script>
