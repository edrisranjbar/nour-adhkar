import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  base: '/instagram/',
  publicDir: false,
  resolve: { alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) } },
  build: {
    outDir: 'dist/instagram-panel',
    emptyOutDir: true,
    manifest: true,
    cssCodeSplit: true,
    rollupOptions: { input: 'src/panel-instagram.js' },
  },
});
