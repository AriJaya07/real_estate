<script setup lang="ts">
withDefaults(defineProps<{
  label: string
  placeholder?: string
  required?: boolean
  error?: string
}>(), {
  placeholder: '0.00',
  required: false,
  error: '',
})

const model = defineModel<number | ''>({ default: '' })
const id = useId()
const inputRef = ref<HTMLInputElement | null>(null)
const focused = ref(false)
const raw = ref(model.value === '' ? '' : String(model.value))

const fullFormatter = new Intl.NumberFormat('en-US', {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
})

function formatLive(value: string): string {
  if (value === '') {
    return ''
  }
  const [integerPart = '', decimalPart] = value.split('.')
  const formattedInteger = integerPart === '' ? '' : Number(integerPart).toLocaleString('en-US')
  return decimalPart !== undefined ? `${formattedInteger}.${decimalPart}` : formattedInteger
}

const display = computed(() => {
  if (raw.value === '' || Number.isNaN(Number(raw.value))) {
    return ''
  }
  return focused.value ? formatLive(raw.value) : fullFormatter.format(Number(raw.value))
})

watch(model, (value) => {
  if (!focused.value) {
    raw.value = value === '' ? '' : String(value)
  }
})

function sanitize(value: string): string {
  let cleaned = value.replace(/[^0-9.]/g, '')
  const firstDot = cleaned.indexOf('.')
  if (firstDot !== -1) {
    cleaned = cleaned.slice(0, firstDot + 1) + cleaned.slice(firstDot + 1).replace(/\./g, '')
  }
  const [integerPart = '', decimalPart] = cleaned.split('.')
  return integerPart.slice(0, 12) + (decimalPart !== undefined ? '.' + decimalPart.slice(0, 2) : '')
}

async function handleInput(event: Event) {
  const input = event.target as HTMLInputElement
  const caret = input.selectionStart ?? input.value.length
  const significantBeforeCaret = input.value.slice(0, caret).replace(/[^0-9.]/g, '').length

  raw.value = sanitize(input.value)
  model.value = raw.value === '' ? '' : Number(raw.value)

  await nextTick()

  const formatted = display.value
  input.value = formatted

  let position = 0
  let seen = 0
  while (position < formatted.length && seen < significantBeforeCaret) {
    if (/[0-9.]/.test(formatted[position] as string)) {
      seen++
    }
    position++
  }
  input.setSelectionRange(position, position)
}

function handleBlur() {
  focused.value = false
  if (raw.value !== '' && !Number.isNaN(Number(raw.value))) {
    raw.value = String(Number(raw.value))
  }
}
</script>

<template>
  <div class="space-y-1.5">
    <label :for="id" class="block text-sm font-medium text-gray-700">{{ label }}</label>
    <div class="relative">
      <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400">$</span>
      <input
        :id="id"
        ref="inputRef"
        :value="display"
        type="text"
        inputmode="decimal"
        autocomplete="off"
        :placeholder="placeholder"
        :required="required"
        :aria-invalid="Boolean(error)"
        class="block w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-8 pr-4 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-primary-500 focus:outline-2 focus:outline-primary-500/30"
        :class="error ? 'border-red-400 focus:border-red-500 focus:outline-red-500/30' : ''"
        @input="handleInput"
        @focus="focused = true"
        @blur="handleBlur"
      >
    </div>
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>
