import { createApp } from 'vue'
import { createVuetify } from 'vuetify'
import App from './App.vue'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({ components, directives })

const mountPoint = document.getElementById('questionnaire-app')

if (mountPoint) {
    const app = createApp(App, { ...mountPoint.dataset })
    app.use(vuetify)
    app.mount(mountPoint)
}
