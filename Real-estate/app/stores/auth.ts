import { defineStore } from 'pinia'
import { useLocalStorage } from '@vueuse/core'
import { api, TOKEN_KEY } from '~/services/api'
import type { AuthCredentials, User } from '~/types'

export const useAuthStore = defineStore('auth', () => {
  const token = useLocalStorage(TOKEN_KEY, '')
  const user = ref<User | null>(null)

  const isAuthenticated = computed(() => token.value !== '')

  async function register(credentials: AuthCredentials): Promise<void> {
    const { data } = await api.post<{ user: User; token: string }>('/register', credentials)
    token.value = data.token
    user.value = data.user
  }

  async function login(credentials: AuthCredentials): Promise<void> {
    const { data } = await api.post<{ user: User; token: string }>('/login', credentials)
    token.value = data.token
    user.value = data.user
  }

  async function fetchUser(): Promise<void> {
    const { data } = await api.get<{ data: User }>('/me')
    user.value = data.data
  }

  async function logout(): Promise<void> {
    try {
      await api.post('/logout')
    } finally {
      token.value = ''
      user.value = null
      await navigateTo('/login')
    }
  }

  return { token, user, isAuthenticated, register, login, fetchUser, logout }
})
