import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import globals from 'globals'
import eslintConfigPrettier from 'eslint-config-prettier'

/**
 * ESLint flat config for Vue 3 + Vite with Prettier compatibility.
 */
export default [
    {
        ignores: [
            'node_modules/**',
            'vendor/**',
            'public/**',
            'public/build/**',
            'storage/**',
            'bootstrap/**',
        ],
    },
    js.configs.recommended,
    ...pluginVue.configs['flat/essential'],
    eslintConfigPrettier,
    {
        files: ['**/*.{js,mjs,cjs,jsx,ts,tsx,vue}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                ...globals.browser,
                ...globals.es2021,
                ...globals.node,
            },
        },
        rules: {
            'no-console': 'warn',
            'no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
            'vue/multi-word-component-names': 'off',
            'vue/valid-v-slot': ['error', { allowModifiers: true }],
        },
    },
]
