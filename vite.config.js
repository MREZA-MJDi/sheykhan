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
                'resources/css/teacher-dashboard-polish.css',
                'resources/js/teacher.js',
                'resources/css/admin.css',
                'resources/css/owner-design-system.css',
                'resources/css/owner-dashboard-polish.css',
                'resources/js/admin.js',
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
