import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import { defineConfig } from 'vite';

export default defineConfig(() => {
  return {
    plugins: [vue(), tailwindcss()],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, '.'),
      },
    },
    build: {
      rollupOptions: {
        input: {
          main:  path.resolve(__dirname, 'index.html'),
          admin: path.resolve(__dirname, 'index-admin.html'),
          driver: path.resolve(__dirname, 'index-driver.html'),
        },
      },
    },
    server: {
      hmr: process.env.DISABLE_HMR !== 'true',
      watch: process.env.DISABLE_HMR === 'true' ? null : {},
      historyApiFallback: {
        rewrites: [
          { from: /^\/index-admin\.html/, to: '/index-admin.html' },
          { from: /^\/index-driver\.html/, to: '/index-driver.html' },
          { from: /^\/(?!index-).*/, to: '/index.html' },
        ],
      },
    },
  };
});