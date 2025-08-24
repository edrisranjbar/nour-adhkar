import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: [
        'icons/favicon.ico',
        'icons/apple-touch-icon.png',
        'icons/mstile-150x150.png',
        'icons/favicon-32x32.png',
        'icons/favicon-16x16.png'
      ],
      manifest: {
        name: 'اذکار نور',
        short_name: 'اذکار نور',
        description: 'دسترسی آسان به اذکار صبحگاهی، شامگاهی و ادعیه مختلف در یک اپلیکیشن سبک و کاربرپسند',
        start_url: '/',
        scope: '/',
        lang: 'fa',
        dir: 'rtl',
        display: 'standalone',
        background_color: '#ffffff',
        theme_color: '#0ea5e9',
        icons: [
          {
            src: '/icons/android-chrome-192x192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: '/icons/favicon-32x32.png',
            sizes: '32x32',
            type: 'image/png'
          },
          {
            src: '/icons/favicon-16x16.png',
            sizes: '16x16',
            type: 'image/png'
          },
          {
            src: '/icons/apple-touch-icon.png',
            sizes: '180x180',
            type: 'image/png'
          }
        ]
      }
    })
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    cors: true,
    allowedHosts: ['adhkar.ir', 'www.adhkar.ir', 'localhost', '127.0.0.1', '85.198.10.144'],
  },
  build: {
    assetsDir: 'assets',
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      output: {
        manualChunks: undefined,
        assetFileNames: 'assets/[name].[hash][extname]'
      }
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `@import "@/assets/css/variables.css";`
      }
    },
    modules: {
      localsConvention: 'camelCaseOnly'
    }
  },
  base: '/'
})
