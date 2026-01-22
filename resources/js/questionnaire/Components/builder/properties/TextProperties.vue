<template>
    <div class="text-properties space-y-4">
        <div class="space-y-2">
            <Label>Placeholder</Label>
            <Input
                v-model="question.data.placeholder"
                placeholder="Placeholder text"
            />
        </div>
    </div>
</template>

<script setup>
    import Input from '@/questionnaire/Components/ui/input/Input.vue'
    import Label from '@/questionnaire/Components/ui/label/Label.vue'

    defineProps({
        question: {
            type: Object,
            required: true,
        },
    })
</script>
