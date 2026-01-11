/**
 * Questionnaire Package - Vuetify Plugin Configuration
 *
 * This file provides a pre-configured Vuetify plugin that can be
 * imported and used by the host application.
 */

import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import 'vuetify/styles'

/**
 * Default theme configuration.
 * Override these values in your app to customize the questionnaire appearance.
 */
export const defaultTheme = {
    dark: false,
    colors: {
        primary: '#1976D2',
        secondary: '#424242',
        accent: '#82B1FF',
        error: '#FF5252',
        info: '#2196F3',
        success: '#4CAF50',
        warning: '#FFC107',
        background: '#FFFFFF',
        surface: '#FFFFFF',
    },
}

/**
 * Default Vuetify configuration for the questionnaire package.
 */
export const defaultVuetifyConfig = {
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
            light: defaultTheme,
        },
    },
}

/**
 * Create a customized Vuetify instance.
 *
 * @param {Object} customConfig - Custom configuration to merge with defaults
 * @returns {Vuetify} Vuetify instance
 *
 * @example
 * // In your app.js
 * import { createQuestionnaireVuetify } from '@questionnaire/vuetify';
 *
 * const vuetify = createQuestionnaireVuetify({
 *   theme: {
 *     themes: {
 *       light: {
 *         colors: {
 *           primary: '#FF5722',
 *         },
 *       },
 *     },
 *   },
 * });
 *
 * createApp(App).use(vuetify).mount('#app');
 */
export function createQuestionnaireVuetify(customConfig = {}) {
    const config = {
        ...defaultVuetifyConfig,
        ...customConfig,
        theme: {
            ...defaultVuetifyConfig.theme,
            ...customConfig.theme,
            themes: {
                light: {
                    ...defaultTheme,
                    ...(customConfig.theme?.themes?.light || {}),
                },
                ...(customConfig.theme?.themes || {}),
            },
        },
    }

    return createVuetify(config)
}

export default createQuestionnaireVuetify
