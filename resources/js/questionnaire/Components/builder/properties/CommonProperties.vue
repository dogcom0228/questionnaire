<template>
    <div class="common-properties space-y-4">
        <div class="space-y-2">
            <Label>Label</Label>
            <Input
                v-model="question.data.label"
                placeholder="Label"
            />
        </div>

        <div class="space-y-2">
            <Label>Description</Label>
            <Textarea
                v-model="question.data.description"
                placeholder="Description"
                class="resize-none"
            />
        </div>

        <div class="flex items-center space-x-2">
            <Switch
                id="required-switch"
                :checked="question.data.required"
                @update:checked="(val) => (question.data.required = val)"
            />
            <Label htmlFor="required-switch">Required</Label>
        </div>
    </div>
</template>

<script setup>
    import Input from '@/questionnaire/Components/ui/input/Input.vue'
    import { Textarea } from '@/questionnaire/Components/ui/textarea'
    import Switch from '@/questionnaire/Components/ui/switch/Switch.vue'
    import Label from '@/questionnaire/Components/ui/label/Label.vue'

    defineProps({
        question: {
            type: Object,
            required: true,
        },
    })
</script>
