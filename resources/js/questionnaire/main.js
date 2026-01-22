import { createInertiaApp } from '@inertiajs/vue3'
import { createApp, h } from 'vue'
import { ZiggyVue } from 'ziggy-js'
import '../../css/app.css'

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })

        console.log('Resolving page:', name)
        // Remove 'Questionnaire/' prefix if it exists
        const cleanName = name.replace(/^Questionnaire\//, '')
        console.log('Clean name:', cleanName)

        const pagePath = `./Pages/${cleanName}.vue`
        console.log('Looking for path:', pagePath)

        const page = pages[pagePath]

        if (!page) {
            console.error(`Page not found: ${pagePath}`)
            console.log('Available pages:', Object.keys(pages))
            throw new Error(`Page not found: ${pagePath}`)
        }

        const component = page.default || page
        console.log('Found component:', component)
        return component
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el)
    },
    id: 'questionnaire-app',
})
