<script setup lang="ts">
withDefaults(defineProps<{
  label: string
  type?: string
  placeholder?: string
  required?: boolean
  error?: string
}>(), {
  type: 'text',
  placeholder: '',
  required: false,
  error: '',
})

const model = defineModel<string | number>({ default: '' })
const id = useId()
</script>

<template>
  <div class="space-y-1.5">
    <label :for="id" class="block text-sm font-medium text-gray-700">{{ label }}</label>
    <input
      :id="id"
      v-model="model"
      :type="type"
      :placeholder="placeholder"
      :required="required"
      :aria-invalid="Boolean(error)"
      class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-primary-500 focus:outline-2 focus:outline-primary-500/30"
      :class="error ? 'border-red-400 focus:border-red-500 focus:outline-red-500/30' : ''"
    >
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>
