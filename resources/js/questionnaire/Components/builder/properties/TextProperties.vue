<template>
    <div class="text-properties">
        <v-text-field
            v-model="question.data.placeholder"
            label="Placeholder"
            variant="outlined"
            density="comfortable"
            hide-details="auto"
        ></v-text-field>
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
