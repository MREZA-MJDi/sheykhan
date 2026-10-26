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
                'resources/css/owner-design-system.css',
                'resources/css/panel-design-system.css',
                'resources/css/role-dashboard-design-system.css',
                'resources/js/admin.js',
                'resources/css/owner-dashboard-v2.css',
                'resources/css/owner-admin-pages.css',
                'resources/css/owner-seo-pages.css',
                'resources/js/owner-dashboard.js',
                'resources/js/owner-admin-pages.js',
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
