import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/home.css',
                'resources/js/home.js',
                'resources/css/owner.css',
                'resources/js/owner.js',
                'resources/css/teacher.css',
                'resources/js/teacher.js',
                'resources/css/student.css',
                'resources/js/student.js',
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
