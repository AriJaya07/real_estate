<script setup lang="ts">
const propertyStore = usePropertyStore()

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
