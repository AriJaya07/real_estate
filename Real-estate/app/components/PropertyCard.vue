<script setup lang="ts">
import type { Property } from '~/types'

defineProps<{
  property: Property
}>()

const { formatPrice } = useFormat()
const imageFailed = ref(false)
</script>

<template>
  <NuxtLink
    :to="`/properties/${property.id}`"
    class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
  >
    <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
      <img
        v-if="property.image && !imageFailed"
        :src="property.image"
        :alt="property.title"
        loading="lazy"
        class="size-full object-cover transition duration-300 group-hover:scale-105"
        @error="imageFailed = true"
      >
      <div v-else class="flex size-full items-center justify-center text-gray-300">
        <svg class="size-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
        </svg>
      </div>
      <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm">
        {{ property.type }}
      </span>
    </div>
    <div class="flex flex-1 flex-col gap-2 p-5">
      <h3 class="text-base font-semibold text-gray-900 line-clamp-1">{{ property.title }}</h3>
      <p class="flex items-center gap-1.5 text-sm text-gray-500">
        <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
        </svg>
        {{ property.location }}
      </p>
      <p class="text-lg font-bold text-primary-600">{{ formatPrice(property.price) }}</p>
      <p class="text-sm text-gray-500 line-clamp-2">{{ property.description }}</p>
      <span class="mt-auto inline-flex w-fit items-center gap-1 pt-2 text-sm font-semibold text-primary-600 transition group-hover:gap-2">
        Select
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
        </svg>
      </span>
    </div>
  </NuxtLink>
</template>
