<script setup lang="ts">
const propertyStore = usePropertyStore()
const auth = useAuthStore()

onMounted(() => {
  propertyStore.manageMode = false
  propertyStore.fetchProperties()
})
</script>

<template>
  <div class="space-y-8">
    <div class="rounded-3xl bg-gradient-to-br from-primary-600 to-primary-700 px-6 py-12 text-center sm:px-12 sm:py-16">
      <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
        Find your dream property
      </h1>
      <p class="mx-auto mt-3 max-w-xl text-sm text-primary-100 sm:text-base">
        Browse houses, villas, apartments, land, and offices across Bali. No account needed to explore.
      </p>
      <div class="mx-auto mt-8 max-w-2xl text-left">
        <SearchBar v-model="propertyStore.search" :loading="propertyStore.searching" />
      </div>

      <div v-if="!auth.isAuthenticated" class="mx-auto mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
        <p class="text-sm text-primary-100">Want to list your own property?</p>
        <NuxtLink
          to="/register"
          class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-primary-700 shadow-sm transition hover:bg-primary-50"
        >
          Create a free account
        </NuxtLink>
      </div>
      <div v-else class="mx-auto mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
        <p class="text-sm text-primary-100">Select any listing below to submit your property, or manage everything from your dashboard.</p>
        <NuxtLink
          to="/dashboard"
          class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-primary-700 shadow-sm transition hover:bg-primary-50"
        >
          Go to Dashboard
        </NuxtLink>
      </div>
    </div>

    <div class="flex items-center justify-between">
      <h2 class="text-lg font-semibold text-gray-900">
        {{ propertyStore.search ? 'Search results' : 'All listings' }}
      </h2>
      <p v-if="!propertyStore.loading" class="text-sm text-gray-500">
        {{ propertyStore.filtered.length }} {{ propertyStore.filtered.length === 1 ? 'property' : 'properties' }}
      </p>
    </div>

    <PropertyGrid :properties="propertyStore.filtered" :loading="propertyStore.loading" />
  </div>
</template>
