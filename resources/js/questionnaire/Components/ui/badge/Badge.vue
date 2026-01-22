<script setup>
    import { cn } from '@/lib/utils'
    import { cva } from 'class-variance-authority'

    const props = defineProps({
        variant: { type: String, default: 'default' },
        class: { type: String, default: '' },
    })

    const badgeVariants = cva(
        'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
        {
            variants: {
                variant: {
                    default:
                        'border-transparent bg-primary text-primary-foreground hover:bg-primary/80',
                    secondary:
                        'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
                    destructive:
                        'border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80',
                    outline: 'text-foreground',
                    success:
                        'border-transparent bg-green-100 text-green-800 hover:bg-green-200',
                    warning:
                        'border-transparent bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
                    error: 'border-transparent bg-red-100 text-red-800 hover:bg-red-200',
                    draft: 'border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200',
                },
            },
            defaultVariants: {
                variant: 'default',
            },
        }
    )
</script>

<template>
    <div :class="cn(badgeVariants({ variant }), props.class)">
        <slot />
    </div>
</template>
