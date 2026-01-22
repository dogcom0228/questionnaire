<script setup>
    import { computed } from 'vue'
    import { Label } from 'radix-vue'
    import { cn } from '@/questionnaire/Utils/cn'

    const props = defineProps({
        asChild: {
            type: Boolean,
            default: false,
        },
        class: {
            type: String,
            default: '',
        },
    })

    const labelClass = computed(() => {
        return cn(
            'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
            props.class
        )
    })
</script>

<template>
    <Label
        :as-child="asChild"
        :class="labelClass"
    >
        <slot />
    </Label>
</template>
