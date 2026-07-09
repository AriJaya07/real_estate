<script setup lang="ts">
import { extractErrorMessage } from '~/services/api'

definePageMeta({ layout: 'auth', middleware: 'guest' })

const auth = useAuthStore()

const form = reactive({
  username: '',
  password: '',
})

const loading = ref(false)
const error = ref('')

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    await auth.login(form)
    await navigateTo('/dashboard')
  } catch (exception) {
    error.value = extractErrorMessage(exception)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-md">
    <div class="mb-8 text-center">
      <span class="mx-auto mb-4 flex size-12 items-center justify-center rounded-2xl bg-primary-600 text-white">
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
        </svg>
      </span>
      <h1 class="text-2xl font-bold tracking-tight text-gray-900">Welcome back</h1>
      <p class="mt-1 text-sm text-gray-500">Sign in to your DreamRue account</p>
    </div>

    <form class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8" @submit.prevent="handleLogin">
      <Input v-model="form.username" label="Username" placeholder="yourname" required />
      <Input v-model="form.password" label="Password" type="password" placeholder="Your password" required />

      <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">{{ error }}</p>

      <PrimaryButton type="submit" :loading="loading" class="w-full">Login</PrimaryButton>

      <p class="text-center text-sm text-gray-500">
        No account yet?
        <NuxtLink to="/register" class="font-semibold text-primary-600 transition hover:text-primary-700">Register</NuxtLink>
      </p>
    </form>
  </div>
</template>
