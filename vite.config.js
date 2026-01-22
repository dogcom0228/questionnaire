import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { resolve } from 'path'
import { defineConfig } from 'vite'
import vuetify from 'vite-plugin-vuetify'

export default defineConfig({
    // Ensure built asset URLs point to the published path under the host app
    base: '/vendor/questionnaire/',
    plugins: [
        laravel({
            input: ['resources/js/questionnaire/main.js'],
            refresh: true,
            buildDirectory: 'build',
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vuetify({
            autoImport: true,
        }),
    ],
    resolve: {
        alias: {
            '@questionnaire': resolve(
                __dirname,
                './resources/js/questionnaire'
            ),
        },
    },
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                main: 'resources/js/questionnaire/main.js',
            },
            output: {
                entryFileNames: 'assets/[name].js', // Stable name for zero-config
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]',
            },
        },
    },
})
