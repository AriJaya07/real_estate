<script setup lang="ts">
const auth = useAuthStore()
const mobileOpen = ref(false)

async function handleLogout() {
  mobileOpen.value = false
  await auth.logout()
}
</script>

<template>
  <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/90 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
      <NuxtLink to="/" class="flex items-center gap-2">
        <span class="flex size-9 items-center justify-center rounded-xl bg-primary-600 text-white">
          <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
          </svg>
        </span>
        <span class="text-lg font-bold tracking-tight text-gray-900">DreamRue</span>
      </NuxtLink>

      <div class="hidden items-center gap-2 md:flex">
        <NuxtLink
          to="/"
          class="rounded-xl px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          exact-active-class="bg-primary-50 text-primary-700"
        >
          Browse
        </NuxtLink>

        <template v-if="auth.isAuthenticated">
          <NuxtLink
            to="/dashboard"
            class="rounded-xl px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
            active-class="bg-primary-50 text-primary-700"
          >
            Dashboard
          </NuxtLink>
          <button
            type="button"
            class="rounded-xl px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-red-50 hover:text-red-600"
            @click="handleLogout"
          >
            Logout
          </button>
        </template>

        <template v-else>
          <NuxtLink
            to="/login"
            class="rounded-xl px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
          >
            Login
          </NuxtLink>
          <NuxtLink
            to="/register"
            class="rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700"
          >
            Register
          </NuxtLink>
        </template>
      </div>

      <button
        type="button"
        class="rounded-xl p-2 text-gray-600 transition hover:bg-gray-100 md:hidden"
        :aria-expanded="mobileOpen"
        aria-label="Toggle menu"
        @click="mobileOpen = !mobileOpen"
      >
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </nav>

    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="-translate-y-2 opacity-0"
      leave-active-class="transition duration-100 ease-in"
      leave-to-class="-translate-y-2 opacity-0"
    >
      <div v-if="mobileOpen" class="border-t border-gray-200 bg-white px-4 py-3 md:hidden">
        <NuxtLink
          to="/"
          class="block rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100"
          exact-active-class="bg-primary-50 text-primary-700"
          @click="mobileOpen = false"
        >
          Browse
        </NuxtLink>

        <template v-if="auth.isAuthenticated">
          <NuxtLink
            to="/dashboard"
            class="block rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100"
            active-class="bg-primary-50 text-primary-700"
            @click="mobileOpen = false"
          >
            Dashboard
          </NuxtLink>
          <button
            type="button"
            class="block w-full rounded-xl px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50"
            @click="handleLogout"
          >
            Logout
          </button>
        </template>

        <template v-else>
          <NuxtLink
            to="/login"
            class="block rounded-xl px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100"
            @click="mobileOpen = false"
          >
            Login
          </NuxtLink>
          <NuxtLink
            to="/register"
            class="block rounded-xl px-4 py-2.5 text-sm font-semibold text-primary-600 transition hover:bg-primary-50"
            @click="mobileOpen = false"
          >
            Register
          </NuxtLink>
        </template>
      </div>
    </Transition>
  </header>
</template>
