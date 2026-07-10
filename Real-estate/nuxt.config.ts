import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  ssr: false,
  devtools: { enabled: true },
  modules: ['@pinia/nuxt', '@vueuse/nuxt'],
  css: ['~/assets/css/main.css'],
  vite: {
    plugins: [tailwindcss()],
  },
  routeRules: {
    '/api/**': { proxy: 'http://62.171.156.55:8081/api/**' },
  },
  runtimeConfig: {
    public: {
      apiBase: 'http://62.171.156.55:8081/api',
    },
  },
  app: {
    head: {
      title: 'DreamRue Estate',
      htmlAttrs: { lang: 'en' },
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Internal real estate management system' },
      ],
    },
  },
})
