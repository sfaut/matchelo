import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { globSync } from 'node:fs';
import { sep } from 'node:path';
import { defineConfig, lazyPlugins } from 'vite-plus';

// Un point d'entrée par fichier de thème : le layout charge celui de l'utilisateur
const themes = globSync('resources/css/themes/*.css').map((path) => path.replaceAll(sep, '/'));

export default defineConfig({
    plugins: lazyPlugins(() => [
        laravel({
            input: ['resources/css/app.css', ...themes, 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Space Grotesk', {
                    weights: [500, 700],
                }),
            ],
        }),
        tailwindcss(),
    ]),
    server: {
        cors: true,
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/storage/framework/views/**',
                '**/vendor/**',
            ],
        },
    },
});
