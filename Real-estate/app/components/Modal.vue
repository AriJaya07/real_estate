<script setup lang="ts">
const props = withDefaults(defineProps<{
  title?: string
  size?: 'md' | 'lg'
}>(), {
  title: '',
  size: 'md',
})

const open = defineModel<boolean>({ default: false })
const panel = ref<HTMLElement | null>(null)

onClickOutside(panel, () => {
  open.value = false
})

onKeyStroke('Escape', () => {
  open.value = false
})

const sizeClass = computed(() => (props.size === 'lg' ? 'max-w-2xl' : 'max-w-md'))
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-100 ease-in"
      leave-to-class="opacity-0"
    >
      <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4">
        <div
          ref="panel"
          role="dialog"
          aria-modal="true"
          class="w-full rounded-2xl bg-white p-6 shadow-xl max-h-[90vh] overflow-y-auto"
          :class="sizeClass"
        >
          <div v-if="title" class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">{{ title }}</h2>
            <button
              type="button"
              aria-label="Close"
              class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
              @click="open = false"
            >
              <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
              </svg>
            </button>
          </div>
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
