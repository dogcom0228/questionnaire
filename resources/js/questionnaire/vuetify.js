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
        primary: '#4F46E5',
        secondary: '#10B981',
        accent: '#8B5CF6',
        error: '#EF4444',
        info: '#3B82F6',
        success: '#22C55E',
        warning: '#F59E0B',
        background: '#F3F4F6',
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
    defaults: {
        VBtn: {
            variant: 'flat',
            rounded: 'lg',
            height: '44',
            class: 'text-capitalize font-weight-bold letter-spacing-0',
        },
        VCard: {
            elevation: 2,
            rounded: 'lg',
        },
        VTextField: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
            hideDetails: 'auto',
            class: 'mb-2',
        },
        VTextarea: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
            hideDetails: 'auto',
            class: 'mb-2',
        },
        VSelect: {
            variant: 'outlined',
            density: 'comfortable',
            color: 'primary',
            hideDetails: 'auto',
            class: 'mb-2',
        },
        VCheckbox: {
            color: 'primary',
            density: 'comfortable',
            hideDetails: 'auto',
            class: 'mb-2',
        },
        VRadioGroup: {
            color: 'primary',
            density: 'comfortable',
            hideDetails: 'auto',
            class: 'mb-2',
        },
        VRadio: {
            color: 'primary',
        },
        VSwitch: {
            color: 'primary',
            inset: true,
            hideDetails: 'auto',
            class: 'mb-2',
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
        defaults: {
            ...defaultVuetifyConfig.defaults,
            ...(customConfig.defaults || {}),
        },
    }

    return createVuetify(config)
}

export default createQuestionnaireVuetify
