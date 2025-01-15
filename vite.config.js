import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // 'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    build: {
        // Specify output directory for processed assets
        outDir: 'public/build',
        // Configure asset handling
        rollupOptions: {
            input: {
                app: '/resources/js/app.js',
            },
            output: {
                assetFileNames: (assetInfo) => {
                    let extType = assetInfo.name.split('.').at(1);
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico|webp/i.test(extType)) {
                        extType = 'images';
                    }
                    return `${extType}/[name][extname]`;
                },
            },
        },
    },
});
