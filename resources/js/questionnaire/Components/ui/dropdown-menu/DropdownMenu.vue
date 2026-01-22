<script setup>
    import {
        DropdownMenuRoot,
        DropdownMenuTrigger,
        DropdownMenuPortal,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuSeparator,
        DropdownMenuLabel,
    } from 'radix-vue'
    import { cn } from '@/lib/utils'

    const props = defineProps({
        class: { type: String, default: '' },
    })
</script>

<template>
    <DropdownMenuRoot>
        <slot />
    </DropdownMenuRoot>
</template>
