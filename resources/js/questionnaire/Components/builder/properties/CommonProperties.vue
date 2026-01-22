<template>
    <div class="common-properties">
        <v-text-field
            v-model="question.data.label"
            label="Label"
            variant="outlined"
            density="comfortable"
            hide-details="auto"
            class="mb-3"
        ></v-text-field>

        <v-textarea
            v-model="question.data.description"
            label="Description"
            variant="outlined"
            density="comfortable"
            rows="2"
            auto-grow
            hide-details="auto"
            class="mb-3"
        ></v-textarea>

        <v-switch
            v-model="question.data.required"
            label="Required"
            color="primary"
            hide-details
            density="compact"
        ></v-switch>
    </div>
</template>

<script setup>
    defineProps({
        question: {
            type: Object,
            required: true,
        },
    })
</script>
