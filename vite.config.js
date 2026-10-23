import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/home.css',
                'resources/js/home.js',
                'resources/css/student.css',
                'resources/js/student.js',
                'resources/css/teacher.css',
                'resources/js/teacher.js',
                'resources/css/admin.css',
                'resources/js/admin.js',
                'resources/css/seo.css',
                'resources/js/seo.js',
                'resources/css/parent.css',
                'resources/js/parent.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
