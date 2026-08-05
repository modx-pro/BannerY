/**
 * Сборка админки BannerPro (Vue 3 + PrimeVue 4).
 * Запуск: npm run build:mgr
 */
import { defineConfig } from 'vite'
import prefixSelector from 'postcss-prefix-selector'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const mgrRoot = path.join(__dirname, 'assets/components/bannerpro/js/mgr')

export default defineConfig({
  root: mgrRoot,
  build: {
    outDir: 'vue-dist',
    emptyOutDir: true,
    rollupOptions: {
      input: path.join(mgrRoot, 'src/entry.js'),
      external: (id) =>
        id === 'vue' ||
        id.startsWith('vue/') ||
        id === 'pinia' ||
        id.startsWith('pinia/') ||
        id.startsWith('@vuetools/'),
      output: {
        format: 'es',
        entryFileNames: 'bannerpro-admin.min.js',
        assetFileNames: 'bannerpro-admin.min.[ext]',
      },
    },
    cssCodeSplit: true,
    minify: 'esbuild',
  },
  css: {
    postcss: {
      plugins: [
        prefixSelector({
          prefix: '.vueApp',
          // PrimeFlex и утилиты без префикса: Dialog/Overlay рендерятся в body вне .vueApp
          exclude: [
            /^:root/,
            /^\.p-/,
            /^\.pi/,
            /^\[data-p-/,
            /^\.flex/,
            /^\.grid/,
            /^\.col/,
            /^\.gap-/,
            /^\.field$/,
            /^\.align-/,
            /^\.justify-/,
            /^\.inline-/,
            /^\.hidden$/,
            /^\.block$/,
            /^\.w-/,
            /^\.h-/,
            /^\.m-/,
            /^\.mx-/,
            /^\.my-/,
            /^\.mt-/,
            /^\.mb-/,
            /^\.ml-/,
            /^\.mr-/,
            /^\.px-/,
            /^\.py-/,
            /^\.pt-/,
            /^\.pb-/,
            /^\.pl-/,
            /^\.pr-/,
          ],
        }),
      ],
    },
  },
})
