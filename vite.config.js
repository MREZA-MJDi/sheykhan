import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/panels/shared/enterprise.css',
                'resources/js/app.js',
                'resources/css/home.css',
                'resources/js/home.js',
                'resources/css/public-experience.css',
                'resources/css/student.css',
                'resources/js/student.js',
                'resources/css/teacher.css',
                'resources/js/teacher.js',
                'resources/css/parent.css',
                'resources/js/parent.js',
                'resources/views/owner/admin/assets/admin.css',
                'resources/views/owner/admin/assets/admin.js',
                'resources/js/owner-people-directory.js',
                'resources/css/seo-dashboard.css',
                'resources/js/seo-dashboard.js',
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
