/**
 * Questionnaire Package - Vue App Entry Point
 *
 * This file can be imported by the host application to register
 * the questionnaire components and Vuetify configuration.
 */

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'

// Vuetify
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import 'vuetify/styles'

/**
 * Create default Vuetify instance for the questionnaire package.
 * This can be customized by the host application.
 */
export function createQuestionnaireVuetify(options = {}) {
    const defaultTheme = {
        dark: false,
        colors: {
            primary: '#1976D2',
            secondary: '#424242',
            accent: '#82B1FF',
            error: '#FF5252',
            info: '#2196F3',
            success: '#4CAF50',
            warning: '#FFC107',
        },
    }

    return createVuetify({
        components,
        directives,
        icons: {
            defaultSet: 'mdi',
            aliases,
            sets: { mdi },
        },
        theme: {
            defaultTheme: 'light',
            themes: {
                light: { ...defaultTheme, ...options.theme?.light },
                dark: { ...(options.theme?.dark || {}) },
            },
        },
        ...options,
    })
}

/**
 * Initialize the questionnaire Inertia app.
 * Call this from your main app.js if you want to use the package's
 * pages directly.
 */
export function initQuestionnaireApp(options = {}) {
    const vuetify =
        options.vuetify || createQuestionnaireVuetify(options.vuetifyOptions)

    createInertiaApp({
        // Ensure we target the questionnaire-specific root element
        id: options.id || 'questionnaire-app',
        title: (title) =>
            title ? `${title} - Questionnaire` : 'Questionnaire',
        resolve: (name) => {
            // First try to resolve from custom pages (host app override)
            if (options.customPages && options.customPages[name]) {
                return options.customPages[name]
            }

            // Then try to resolve from package pages
            return resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob('./Pages/**/*.vue')
            )
        },
        setup({ el, App, props, plugin }) {
            const app = createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(vuetify)

            // Register additional plugins from options
            if (options.plugins) {
                options.plugins.forEach((p) => app.use(p))
            }

            // Mount the app
            app.mount(el)

            return app
        },
        progress: {
            color: options.progressColor || '#4B5563',
        },
    })
}

// Export components for individual use
export { default as QuestionEditor } from './Components/QuestionEditor.vue'
export { default as QuestionRenderer } from './Components/QuestionRenderer.vue'
export { default as AdminLayout } from './Layouts/AdminLayout.vue'
export { default as PublicLayout } from './Layouts/PublicLayout.vue'

// Export question type components
export { default as CheckboxInput } from './Components/QuestionTypes/CheckboxInput.vue'
export { default as DateInput } from './Components/QuestionTypes/DateInput.vue'
export { default as NumberInput } from './Components/QuestionTypes/NumberInput.vue'
export { default as RadioInput } from './Components/QuestionTypes/RadioInput.vue'
export { default as SelectInput } from './Components/QuestionTypes/SelectInput.vue'
export { default as TextareaInput } from './Components/QuestionTypes/TextareaInput.vue'
export { default as TextInput } from './Components/QuestionTypes/TextInput.vue'

// Export pages
export { default as AdminCreate } from './Pages/Admin/Create.vue'
export { default as AdminEdit } from './Pages/Admin/Edit.vue'
export { default as AdminIndex } from './Pages/Admin/Index.vue'
export { default as AdminResponses } from './Pages/Admin/Responses.vue'
export { default as AdminShow } from './Pages/Admin/Show.vue'
export { default as PublicClosed } from './Pages/Public/Closed.vue'
export { default as PublicDuplicate } from './Pages/Public/Duplicate.vue'
export { default as PublicFill } from './Pages/Public/Fill.vue'
export { default as PublicThankYou } from './Pages/Public/ThankYou.vue'
