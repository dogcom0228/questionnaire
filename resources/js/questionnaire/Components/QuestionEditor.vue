<template>
    <div
        role="region"
        :aria-label="`Question ${index + 1} editor`"
    >
        <v-row>
            <v-col
                cols="12"
                md="8"
            >
                <v-text-field
                    v-model="question.content"
                    label="Question Text"
                    variant="outlined"
                    density="compact"
                    :disabled="disabled"
                    :aria-required="question.required"
                    :aria-describedby="
                        question.description
                            ? `question-${index}-description`
                            : undefined
                    "
                    aria-label="Question text input"
                />
            </v-col>
            <v-col
                cols="12"
                md="4"
            >
                <v-select
                    v-model="question.type"
                    :items="questionTypeItems"
                    label="Question Type"
                    variant="outlined"
                    density="compact"
                    :disabled="disabled"
                    aria-label="Select question type"
                />
            </v-col>
        </v-row>

        <v-text-field
            v-model="question.description"
            :id="`question-${index}-description`"
            label="Description (optional)"
            variant="outlined"
            density="compact"
            class="mb-2"
            :disabled="disabled"
            aria-label="Question description (optional)"
        />

        <!-- Options for radio, checkbox, select -->
        <div
            v-if="hasOptions"
            class="mb-4"
            role="group"
            aria-label="Question options"
        >
            <div class="text-subtitle-2 mb-2">Options</div>
            <div
                v-for="(option, optIndex) in question.options"
                :key="optIndex"
                class="d-flex align-center mb-2"
            >
                <v-text-field
                    v-model="question.options[optIndex]"
                    :label="`Option ${optIndex + 1}`"
                    variant="outlined"
                    density="compact"
                    hide-details
                    class="flex-grow-1"
                    :disabled="disabled"
                    :aria-label="`Option ${optIndex + 1} text`"
                />
                <v-btn
                    icon="mdi-close"
                    size="small"
                    variant="text"
                    class="ml-2"
                    :disabled="disabled"
                    :aria-label="`Remove option ${optIndex + 1}`"
                    @click="removeOption(optIndex)"
                />
            </div>
            <v-btn
                size="small"
                variant="text"
                prepend-icon="mdi-plus"
                :disabled="disabled"
                aria-label="Add new option"
                @click="addOption"
            >
                Add Option
            </v-btn>
        </div>

        <!-- Settings specific to question type -->
        <div
            v-if="question.type === 'number'"
            class="mb-4"
            role="group"
            aria-label="Number field settings"
        >
            <v-row>
                <v-col cols="6">
                    <v-text-field
                        v-model.number="question.settings.min"
                        label="Minimum"
                        type="number"
                        variant="outlined"
                        density="compact"
                        :disabled="disabled"
                        aria-label="Minimum value"
                    />
                </v-col>
                <v-col cols="6">
                    <v-text-field
                        v-model.number="question.settings.max"
                        label="Maximum"
                        type="number"
                        variant="outlined"
                        density="compact"
                        :disabled="disabled"
                        aria-label="Maximum value"
                    />
                </v-col>
            </v-row>
        </div>

        <div
            v-if="question.type === 'text'"
            class="mb-4"
            role="group"
            aria-label="Text field settings"
        >
            <v-row>
                <v-col cols="6">
                    <v-text-field
                        v-model.number="question.settings.min_length"
                        label="Min Length"
                        type="number"
                        variant="outlined"
                        density="compact"
                        :disabled="disabled"
                        aria-label="Minimum character length"
                    />
                </v-col>
                <v-col cols="6">
                    <v-text-field
                        v-model.number="question.settings.max_length"
                        label="Max Length"
                        type="number"
                        variant="outlined"
                        density="compact"
                        :disabled="disabled"
                        aria-label="Maximum character length"
                    />
                </v-col>
            </v-row>
        </div>

        <v-row>
            <v-col cols="12">
                <v-switch
                    v-model="question.required"
                    label="Required"
                    color="primary"
                    density="compact"
                    hide-details
                    :disabled="disabled"
                    aria-label="Mark this question as required"
                />
            </v-col>
        </v-row>

        <v-divider class="my-4" />

        <div class="d-flex justify-end">
            <v-btn
                color="error"
                variant="text"
                prepend-icon="mdi-delete"
                :disabled="disabled"
                :aria-label="`Remove question ${index + 1}`"
                @click="$emit('remove')"
            >
                Remove Question
            </v-btn>
        </div>
    </div>
</template>

<script setup>
    import { computed } from 'vue'

    const props = defineProps({
        modelValue: {
            type: Object,
            required: true,
        },
        questionTypes: {
            type: Array,
            default: () => [],
        },
        index: {
            type: Number,
            required: true,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    })

    const emit = defineEmits(['update:modelValue', 'remove'])

    const question = computed({
        get: () => props.modelValue,
        set: (value) => emit('update:modelValue', value),
    })

    const questionTypeItems = computed(() =>
        props.questionTypes.map((t) => ({
            value: t.identifier,
            title: t.name,
        }))
    )

    const hasOptions = computed(() =>
        ['radio', 'checkbox', 'select'].includes(question.value.type)
    )

    const addOption = () => {
        if (!question.value.options) {
            question.value.options = []
        }
        question.value.options.push('')
    }

    const removeOption = (index) => {
        question.value.options.splice(index, 1)
    }
</script>
