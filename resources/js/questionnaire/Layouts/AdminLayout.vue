<template>
    <v-app>
        <v-app-bar
            color="primary"
            density="compact"
        >
            <v-app-bar-nav-icon @click="drawer = !drawer" />
            <v-toolbar-title>Questionnaire Admin</v-toolbar-title>
            <v-spacer />
            <v-btn icon="mdi-account" />
        </v-app-bar>

        <v-navigation-drawer
            v-model="drawer"
            temporary
        >
            <v-list nav>
                <v-list-item
                    prepend-icon="mdi-view-dashboard"
                    title="Dashboard"
                    href="#"
                    @click.prevent="navigate('questionnaire.admin.index')"
                />
                <v-list-item
                    prepend-icon="mdi-plus-circle"
                    title="Create Questionnaire"
                    href="#"
                    @click.prevent="navigate('questionnaire.admin.create')"
                />
            </v-list>
        </v-navigation-drawer>

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

    const props = defineProps(['flash'])
    const emit = defineEmits(['navigate'])

    const drawer = ref(false)
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

    const navigate = (name) => {
        emit('navigate', name)
    }
</script>
