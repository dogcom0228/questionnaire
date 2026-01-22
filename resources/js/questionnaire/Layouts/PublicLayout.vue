<template>
    <div class="min-h-screen bg-muted/30 py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
    import { watch } from 'vue'

    const props = defineProps({
        title: {
            type: String,
            default: 'Survey',
        },
        flash: {
            type: Object,
            default: () => ({}),
        },
    })

    // We can implement a toast notification here later if needed
    // For now we just watch for flash messages
    watch(
        () => props.flash,
        (newFlash) => {
            if (newFlash?.success) {
                console.log('Success:', newFlash.success)
            }
            if (newFlash?.error) {
                console.error('Error:', newFlash.error)
            }
        },
        { deep: true, immediate: true }
    )
</script>
