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
                'resources/css/owner-dashboard-v2.css',
                'resources/css/owner-admin-pages.css',
                'resources/css/owner-seo-pages.css',
                'resources/js/owner-dashboard.js',
                'resources/js/owner-admin-pages.js',
                'resources/css/seo-dashboard.css',
                'resources/css/seo-admin-pages.css',
                'resources/css/seo-pages.css',
                'resources/js/seo-dashboard.js',
                'resources/js/seo-admin-pages.js',
                'resources/css/parent.css',
                'resources/js/parent.js',
                'resources/css/teacher-foundation.css',
                'resources/css/teacher-panel-design-system.css',
                'resources/css/teacher-role-dashboard-design-system.css',
                'resources/css/teacher-communication-pages.css',
                'resources/js/teacher-foundation.js',
                'resources/css/student-foundation.css',
                'resources/css/student-panel-design-system.css',
                'resources/css/student-role-dashboard-design-system.css',
                'resources/js/student-foundation.js',
                'resources/css/parent-foundation.css',
                'resources/css/parent-panel-design-system.css',
                'resources/css/parent-communication-pages.css',
                'resources/css/parent-homeroom-dashboard-theme.css',
                'resources/js/parent-foundation.js',
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
