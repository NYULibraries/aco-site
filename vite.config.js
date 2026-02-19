import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [ 'resources/sass/style.scss'],
            refresh: true,
        }),
    ],
    server: {
        cors: true,
    },
    resolve: {
        alias: {
            '@assets': path.resolve(__dirname, 'resources/images'),
        },
    },
});
