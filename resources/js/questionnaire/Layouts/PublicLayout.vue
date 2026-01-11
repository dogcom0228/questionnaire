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
    import { ref, onMounted } from 'vue'
    import { usePage } from '@inertiajs/vue3'

    defineProps({
        title: {
            type: String,
            default: 'Survey',
        },
    })

    const snackbar = ref({
        show: false,
        message: '',
        color: 'success',
    })

    const page = usePage()

    onMounted(() => {
        if (page.props.flash?.success) {
            snackbar.value = {
                show: true,
                message: page.props.flash.success,
                color: 'success',
            }
        }
        if (page.props.flash?.error) {
            snackbar.value = {
                show: true,
                message: page.props.flash.error,
                color: 'error',
            }
        }
    })
</script>
