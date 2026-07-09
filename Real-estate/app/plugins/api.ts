import { api, TOKEN_KEY } from '~/services/api'

export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()

  api.defaults.baseURL = config.public.apiBase

  api.interceptors.request.use((request) => {
    const token = localStorage.getItem(TOKEN_KEY)
    if (token) {
      request.headers.Authorization = `Bearer ${token}`
    }
    return request
  })

  api.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response?.status === 401) {
        localStorage.removeItem(TOKEN_KEY)
        const route = useRoute()
        if (route.path !== '/login' && route.path !== '/register') {
          navigateTo('/login')
        }
      }
      return Promise.reject(error)
    },
  )
})
