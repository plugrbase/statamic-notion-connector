import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/cp.js',
                'resources/css/cp.css'
            ],
            publicDirectory: 'public',
            buildDirectory: 'build',
        }),
        vue(),
    ],
    build: {
        manifest: true,
        outDir: 'public/build',
        rollupOptions: {
            input: {
                cp: path.resolve(__dirname, 'resources/js/cp.js'),
                style: path.resolve(__dirname, 'resources/css/cp.css')
            }
        }
    }
}); 