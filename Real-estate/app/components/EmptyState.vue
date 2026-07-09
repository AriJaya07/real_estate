<script setup lang="ts">
withDefaults(defineProps<{
  title: string
  message?: string
}>(), {
  message: '',
})
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center">
    <svg class="size-10 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
    </svg>
    <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
    <p v-if="message" class="max-w-sm text-sm text-gray-500">{{ message }}</p>
    <div class="mt-2">
      <slot />
    </div>
  </div>
</template>
