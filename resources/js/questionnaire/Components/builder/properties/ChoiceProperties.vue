<template>
    <div class="choice-properties space-y-4">
        <div class="text-sm font-medium">Options</div>

        <div class="space-y-2">
            <div
                v-for="(option, index) in question.data.options"
                :key="index"
                class="flex items-center space-x-2"
            >
                <Input
                    v-model="option.text"
                    @update:model-value="(val) => (option.value = val)"
                    :placeholder="`Option ${index + 1}`"
                    class="flex-1"
                />

                <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-destructive hover:text-destructive/90"
                    @click="removeOption(index)"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-trash-2"
                    >
                        <path d="M3 6h18" />
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                        <line
                            x1="10"
                            x2="10"
                            y1="11"
                            y2="17"
                        />
                        <line
                            x1="14"
                            x2="14"
                            y1="11"
                            y2="17"
                        />
                    </svg>
                    <span class="sr-only">Remove option</span>
                </Button>
            </div>
        </div>

        <Button
            variant="outline"
            class="w-full"
            @click="addOption"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="mr-2 lucide lucide-plus"
            >
                <path d="M5 12h14" />
                <path d="M12 5v14" />
            </svg>
            Add Option
        </Button>
    </div>
</template>

<script setup>
    import { onMounted, watch } from 'vue'
    import Input from '@/questionnaire/Components/ui/input/Input.vue'
    import Button from '@/questionnaire/Components/ui/button/Button.vue'

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
