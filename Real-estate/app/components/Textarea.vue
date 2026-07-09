<script setup lang="ts">
withDefaults(defineProps<{
  label: string
  placeholder?: string
  required?: boolean
  rows?: number
  error?: string
}>(), {
  placeholder: '',
  required: false,
  rows: 4,
  error: '',
})

const model = defineModel<string>({ default: '' })
const id = useId()
</script>

<template>
  <div class="space-y-1.5">
    <label :for="id" class="block text-sm font-medium text-gray-700">{{ label }}</label>
    <textarea
      :id="id"
      v-model="model"
      :placeholder="placeholder"
      :required="required"
      :rows="rows"
      :aria-invalid="Boolean(error)"
      class="block w-full resize-y rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-primary-500 focus:outline-2 focus:outline-primary-500/30"
      :class="error ? 'border-red-400 focus:border-red-500 focus:outline-red-500/30' : ''"
    />
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>
