import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import path from 'path'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
  ],
  resolve: {
    alias: {
      '@wordpress/block-editor/build-style': path.resolve(
        __dirname,
        'node_modules/@wordpress/block-editor/build-style'
      ),
      '@wordpress/block-library/build-style': path.resolve(
        __dirname,
        'node_modules/@wordpress/block-library/build-style'
      ),
      // …other aliases…
    },
  },
  
})
