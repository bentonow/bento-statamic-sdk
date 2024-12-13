import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        manifest: true,
        outDir: 'public/vendor/bento-statamic/build',
        rollupOptions: {
            input: 'resources/js/cp.js'
        }
    },
    plugins: [
        laravel({
            input: [
                'resources/js/cp.js'
            ],
            publicDirectory: 'public',
            buildDirectory: 'vendor/bento-statamic/build'
        })
    ]
});
