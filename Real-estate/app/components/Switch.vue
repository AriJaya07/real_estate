<script setup lang="ts">
defineProps<{
  label: string
  description?: string
}>()

const model = defineModel<boolean>({ default: false })
const id = useId()
</script>

<template>
  <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white px-4 py-3">
    <div>
      <label :for="id" class="block text-sm font-medium text-gray-700">{{ label }}</label>
      <p v-if="description" class="text-sm text-gray-500">{{ description }}</p>
    </div>
    <button
      :id="id"
      type="button"
      role="switch"
      :aria-checked="model"
      class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
      :class="model ? 'bg-primary-600' : 'bg-gray-200'"
      @click="model = !model"
    >
      <span
        class="pointer-events-none m-0.5 inline-block size-5 transform rounded-full bg-white shadow transition-transform"
        :class="model ? 'translate-x-5' : 'translate-x-0'"
      />
    </button>
  </div>
</template>
