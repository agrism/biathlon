import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
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
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    const extType = assetInfo.name.split('.').pop();
                    if (/css/i.test(extType)) {
                        return 'css/[name]-[hash][extname]'; // CSS gets hash
                    }
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico|webp/i.test(extType)) {
                        return 'images/[name][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                },
            },
        },
    },
});
