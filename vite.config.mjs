// The theme's own build: one stylesheet and one small script, into the theme's committed
// public/build, which core reads through the manifest named in teme-theme.json.
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    root: import.meta.dirname,
    base: '/themes/temecms/build/',
    plugins: [
        laravel({
            input: ['assets/css/theme.css', 'assets/js/theme.js'],
            publicDirectory: 'public',
            buildDirectory: 'build',
            hotFile: 'public/hot',
        }),
    ],
});
