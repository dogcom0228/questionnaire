import { createInertiaApp } from '@inertiajs/vue3'
import { createApp, h } from 'vue'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({ components, directives })

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })

        // Remove 'Questionnaire/' prefix if it exists, as the Pages directory
        // structure already starts inside resources/js/questionnaire/Pages
        const cleanName = name.replace(/^Questionnaire\//, '')

        const pagePath = `./Pages/${cleanName}.vue`
        const page = pages[pagePath]

        if (!page) {
            console.error(`Page not found: ${pagePath}`)
            throw new Error(`Page not found: ${pagePath}`)
        }

        return page
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(vuetify)
            .mount(el)
    },
})
