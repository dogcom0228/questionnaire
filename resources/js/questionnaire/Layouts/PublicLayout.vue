<template>
    <v-app>
        <v-app-bar
            color="primary"
            density="compact"
        >
            <v-toolbar-title>{{ title }}</v-toolbar-title>
        </v-app-bar>

        <v-main>
            <slot />
        </v-main>

        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            :timeout="3000"
        >
            {{ snackbar.message }}
            <template #actions>
                <v-btn
                    variant="text"
                    @click="snackbar.show = false"
                >
                    Close
                </v-btn>
            </template>
        </v-snackbar>
    </v-app>
</template>

<script setup>
    import { ref, watch } from 'vue'

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

    const snackbar = ref({
        show: false,
        message: '',
        color: 'success',
    })

    watch(() => props.flash, (newFlash) => {
        if (newFlash?.success) {
            snackbar.value = {
                show: true,
                message: newFlash.success,
                color: 'success',
            }
        }
        if (newFlash?.error) {
            snackbar.value = {
                show: true,
                message: newFlash.error,
                color: 'error',
            }
        }
    }, { deep: true, immediate: true })
</script>
