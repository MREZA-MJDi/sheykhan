import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/public.css',
                'resources/js/public.js',
                'resources/css/home.css',
                'resources/css/home-meraki-hero.css',
                'resources/css/tiamir-intro.css',
                'resources/js/home.js',
                'resources/js/tiamir-intro.js',
                'resources/css/owner.css',
                'resources/js/owner.js',
                'resources/css/teacher.css',
                'resources/css/teacher-workspace.css',
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
