<template>
    <v-app>
        <!-- App Bar -->
        <v-app-bar
            color="surface"
            elevation="1"
            density="default"
        >
            <v-app-bar-nav-icon
                variant="text"
                @click.stop="drawer = !drawer"
                v-if="!mdAndUp"
            ></v-app-bar-nav-icon>
            <v-toolbar-title class="text-h6 font-weight-bold">
                Questionnaire Admin
            </v-toolbar-title>
        </v-app-bar>

        <!-- Navigation Drawer -->
        <v-navigation-drawer
            v-model="drawer"
            :permanent="mdAndUp"
            :temporary="!mdAndUp"
            elevation="2"
        >
            <v-sheet
                color="grey-lighten-4"
                class="pa-4 d-flex align-center justify-center"
            >
                <v-avatar
                    color="primary"
                    size="64"
                    class="mb-2"
                >
                    <span class="text-h5">QA</span>
                </v-avatar>
                <!-- Add user name if available in props/page -->
            </v-sheet>

            <v-divider></v-divider>

            <v-list
                nav
                density="compact"
            >
                <v-list-item
                    prepend-icon="mdi-view-dashboard"
                    title="Dashboard"
                    :active="isRouteActive('questionnaire.admin.index')"
                    color="primary"
                    @click="navigate('questionnaire.admin.index')"
                ></v-list-item>

                <v-list-item
                    prepend-icon="mdi-plus-box"
                    title="Create New"
                    :active="isRouteActive('questionnaire.admin.create')"
                    color="primary"
                    @click="navigate('questionnaire.admin.create')"
                ></v-list-item>
            </v-list>
        </v-navigation-drawer>

        <!-- Main Content -->
        <v-main class="bg-grey-lighten-5">
            <v-container
                fluid
                class="pa-6 fill-height align-start"
            >
                <slot />
            </v-container>
        </v-main>

        <!-- Global Snackbar -->
        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            :timeout="4000"
            location="top right"
            variant="elevated"
        >
            <div class="d-flex align-center">
                <v-icon
                    :icon="snackbar.icon"
                    class="mr-2"
                ></v-icon>
                {{ snackbar.message }}
            </div>

            <template #actions>
                <v-btn
                    variant="text"
                    icon="mdi-close"
                    @click="snackbar.show = false"
                ></v-btn>
            </template>
        </v-snackbar>
    </v-app>
</template>

<script setup>
    /* global route */
    import { ref, watch } from 'vue'
    import { router, usePage } from '@inertiajs/vue3'
    import { useDisplay } from 'vuetify'

    const { mdAndUp } = useDisplay()
    const page = usePage()

    const drawer = ref(mdAndUp.value)
    const snackbar = ref({
        show: false,
        message: '',
        color: 'info',
        icon: 'mdi-information',
    })

    // Watch for flash messages
    watch(
        () => page.props.flash,
        (newFlash) => {
            if (newFlash?.success) {
                showSnackbar(newFlash.success, 'success', 'mdi-check-circle')
            } else if (newFlash?.error) {
                showSnackbar(newFlash.error, 'error', 'mdi-alert-circle')
            } else if (newFlash?.warning) {
                showSnackbar(newFlash.warning, 'warning', 'mdi-alert')
            } else if (newFlash?.info) {
                showSnackbar(newFlash.info, 'info', 'mdi-information')
            }
        },
        { deep: true, immediate: true }
    )

    const showSnackbar = (message, color, icon) => {
        snackbar.value = {
            show: true,
            message,
            color,
            icon,
        }
    }

    // Navigation helper
    const navigate = (name) => {
        router.visit(route(name))
    }

    const isRouteActive = (name) => {
        return route().current(name)
    }
</script>
