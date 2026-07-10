<script setup lang="ts">
const props = defineProps<{
  src: string | null
  alt: string
}>()

const failed = ref(false)

watch(() => props.src, () => {
  failed.value = false
})
</script>

<template>
  <img
    v-if="src && !failed"
    :src="src"
    :alt="alt"
    class="object-cover"
    @error="failed = true"
  >
  <span v-else class="flex items-center justify-center bg-gray-100 text-gray-300" :aria-label="alt">
    <svg class="size-1/2 max-h-12 max-w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
    </svg>
  </span>
</template>
