<script setup>
    import { cn } from '@/questionnaire/Utils/cn'

    const props = defineProps({
        class: {
            type: String,
            default: '',
        },
    })
</script>

<template>
    <p :class="cn('text-sm text-muted-foreground', props.class)">
        <slot />
    </p>
</template>
