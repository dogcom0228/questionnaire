/**
 * Questionnaire Package - Main App Entry Point
 * 
 * This file initializes the Inertia + Vue 3 + Vuetify application
 * for the questionnaire package.
 */

import { createInertiaApp } from '@inertiajs/vue3';
import '@mdi/font/css/materialdesignicons.css';
import 'roboto-fontface/css/roboto/roboto-fontface.css';
import { createApp, h } from 'vue';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { aliases, mdi } from 'vuetify/iconsets/mdi';
import 'vuetify/styles';

// Import questionnaire pages
const pages = import.meta.glob('./questionnaire/Pages/**/*.vue', { eager: true });

// Create Vuetify instance with customizable theme
const vuetify = createVuetify({
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
            light: {
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
            },
        },
    },
});

// Mount the app on the appropriate element
const appElement = document.getElementById('questionnaire-app') || document.getElementById('app');

if (appElement) {
    createInertiaApp({
        id: appElement.id || 'app',
        title: (title) => title ? `${title} - Questionnaire` : 'Questionnaire',
        resolve: (name) => {
            // Normalize Inertia page name, e.g. "Questionnaire/Admin/Index" -> "Admin/Index"
            const normalizedName = name.replace(/^Questionnaire\//, '');

            // Try to find the page in the questionnaire package
            const pagePath = `./questionnaire/Pages/${normalizedName}.vue`;
            const page = pages[pagePath];
            
            if (!page) {
                console.error(`Page not found: ${normalizedName}`);
                console.log('Available pages:', Object.keys(pages));
                throw new Error(`Page ${normalizedName} not found in questionnaire package.`);
            }
            
            return page;
        },
        setup({ el, App, props, plugin }) {
            const app = createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(vuetify);

            // Global error handler
            app.config.errorHandler = (err, instance, info) => {
                console.error('Vue Error:', err);
                console.error('Component:', instance);
                console.error('Error Info:', info);
                
                // You can send to error tracking service (e.g., Sentry) here
                // if (window.Sentry) {
                //     window.Sentry.captureException(err, { extra: { info } });
                // }
            };

            // Global warning handler
            app.config.warnHandler = (msg, instance, trace) => {
                console.warn('Vue Warning:', msg);
                console.warn('Trace:', trace);
            };

            app.mount(el);
            
            return app;
        },
        progress: {
            color: '#1976D2',
            showSpinner: true,
        },
    }).catch((error) => {
        console.error('Failed to mount Inertia app:', error);
    });
}

// Export for potential custom usage
export { vuetify };

