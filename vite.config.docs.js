import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    root: 'docs-site',
    build: {
        outDir: '../dist-docs',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                main: resolve(__dirname, 'docs-site/index.html'),
            }
        }
    },
    server: {
        port: 3000,
        open: true,
        fs: {
            // Allow serving files from parent directories
            allow: ['..']
        }
    },
    preview: {
        port: 4000
    }
});
