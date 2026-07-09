<script setup lang="ts">
import type { Property } from '~/types'

defineProps<{
  properties: Property[]
  loading: boolean
}>()
</script>

<template>
  <div v-if="loading" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    <div v-for="index in 8" :key="index" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div class="aspect-[4/3] animate-pulse bg-gray-200" />
      <div class="space-y-3 p-5">
        <div class="h-4 w-3/4 animate-pulse rounded bg-gray-200" />
        <div class="h-3 w-1/2 animate-pulse rounded bg-gray-200" />
        <div class="h-5 w-1/3 animate-pulse rounded bg-gray-200" />
        <div class="h-3 w-full animate-pulse rounded bg-gray-200" />
      </div>
    </div>
  </div>

  <EmptyState
    v-else-if="properties.length === 0"
    title="No properties found"
    message="Try adjusting your search or check back later for new listings."
  />

  <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    <PropertyCard v-for="property in properties" :key="property.id" :property="property" />
  </div>
</template>
