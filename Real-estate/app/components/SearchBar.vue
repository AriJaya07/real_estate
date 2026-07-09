<script setup lang="ts">
withDefaults(defineProps<{
  placeholder?: string
  loading?: boolean
}>(), {
  placeholder: 'Search by title, location, or type...',
  loading: false,
})

const model = defineModel<string>({ default: '' })
</script>

<template>
  <div class="relative">
    <svg
      class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-gray-400"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      aria-hidden="true"
    >
      <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
    </svg>
    <input
      v-model="model"
      type="search"
      :placeholder="placeholder"
      aria-label="Search properties"
      class="block w-full rounded-2xl border border-gray-300 bg-white py-3.5 pl-12 pr-12 text-base text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-primary-500 focus:outline-2 focus:outline-primary-500/30"
    >
    <svg
      v-if="loading"
      class="absolute right-4 top-1/2 size-5 -translate-y-1/2 animate-spin text-primary-600"
      viewBox="0 0 24 24"
      fill="none"
      aria-hidden="true"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
    </svg>
  </div>
</template>
