<template>
    <div class="choice-properties">
        <div class="text-subtitle-2 mb-2">Options</div>

        <div
            v-for="(option, index) in question.data.options"
            :key="index"
            class="d-flex align-center mb-2"
        >
            <v-text-field
                v-model="option.text"
                @update:model-value="(val) => (option.value = val)"
                density="compact"
                hide-details
                variant="outlined"
                class="flex-grow-1 mr-2"
                :label="`Option ${index + 1}`"
            ></v-text-field>

            <v-btn
                icon
                variant="text"
                color="error"
                size="small"
                @click="removeOption(index)"
            >
                <v-icon>mdi-delete</v-icon>
            </v-btn>
        </div>

        <v-btn
            block
            variant="tonal"
            color="primary"
            prepend-icon="mdi-plus"
            @click="addOption"
        >
            Add Option
        </v-btn>
    </div>
</template>

<script setup>
    import { onMounted, watch } from 'vue'

    const props = defineProps({
        question: {
            type: Object,
            required: true,
        },
    })

    // Initialize options if missing
    const initOptions = () => {
        if (!props.question.data.options) {
            props.question.data.options = []
        }
    }

    onMounted(initOptions)
    watch(() => props.question, initOptions) // Watch in case question prop changes

    const addOption = () => {
        props.question.data.options.push({
            text: 'New Option',
            value: 'New Option',
        })
    }

    const removeOption = (index) => {
        props.question.data.options.splice(index, 1)
    }
</script>
