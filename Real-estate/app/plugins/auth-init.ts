export default defineNuxtPlugin(async () => {
  const auth = useAuthStore()

  if (auth.isAuthenticated) {
    try {
      await auth.fetchUser()
    } catch {
      auth.token = ''
      auth.user = null
    }
  }
})
