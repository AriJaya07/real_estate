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
    '/api/**': {
      proxy: `${process.env.NUXT_PUBLIC_API_BASE}/api/**`
    },
  },
  runtimeConfig: {
    public: {
      apiBase: '/api',
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
