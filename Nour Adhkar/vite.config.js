import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    vue(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    cors: true,
    allowedHosts: ['adhkar.ir', 'www.adhkar.ir', 'localhost', '127.0.0.1', '85.198.10.144'],
    hmr: {
      host: 'adhkar.ir',
      port: 8888,
    },
  },
})
