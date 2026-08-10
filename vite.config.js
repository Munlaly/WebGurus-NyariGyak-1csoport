/* global process */
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
      fonts: [
        bunny('Instrument Sans', {
          weights: [400, 500, 600],
        }),
      ],
    }),
    tailwindcss(),
    vue(),
  ],
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,

    // safe fallbacks for CI/CD environments where DDEV env vars don't exist
    origin: process.env.DDEV_PRIMARY_URL_WITHOUT_PORT
      ? `${process.env.DDEV_PRIMARY_URL_WITHOUT_PORT}:5173`
      : 'http://localhost:5173',

    cors: {
      origin: process.env.DDEV_PRIMARY_URL || 'http://localhost',
      credentials: true,
    },

    hmr: {
      // Split the comma-separated list and take the first domain
      host: process.env.DDEV_HOSTNAME
        ? process.env.DDEV_HOSTNAME.split(',')[0]
        : 'localhost',
      protocol: 'wss',
    },
    watch: {
      ignored: ['**/storage/framework/views/**'],
    },
  },
});
