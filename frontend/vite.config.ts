import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import { defineConfig, Plugin } from 'vite';

// Custom middleware: only rewrite SPA routes to /index.html.
// Requests that already target a real HTML file (contain a dot) pass through.
const spaFallback: Plugin = {
  name: 'spa-fallback',
  configureServer(server) {
    server.middlewares.use((req, _res, next) => {
      const url = req.url?.split('?')[0] ?? '/'
      const isAsset = url.includes('.')          // .html, .js, .css, .png …
      const isViteInternal = url.startsWith('/@') // /@vite, /@fs …
      if (!isAsset && !isViteInternal) {
        req.url = '/index.html'
      }
      next()
    })
  },
}

export default defineConfig(() => {
  return {
    plugins: [vue(), tailwindcss(), spaFallback],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, '.'),
      },
    },
    build: {
      rollupOptions: {
        input: {
          main:   path.resolve(__dirname, 'index.html'),
          admin:  path.resolve(__dirname, 'index-admin.html'),
          driver: path.resolve(__dirname, 'index-driver.html'),
        },
      },
    },
    server: {
      hmr:   process.env.DISABLE_HMR !== 'true',
      watch: process.env.DISABLE_HMR === 'true' ? null : {},
    },
  };
});
