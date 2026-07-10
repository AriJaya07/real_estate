<script setup lang="ts">
import type { Property, PropertyPayload, PropertyType } from '~/types'

const props = withDefaults(defineProps<{
  property?: Property | null
  saving: boolean
  error?: string
}>(), {
  property: null,
  error: '',
})

const emit = defineEmits<{
  save: [payload: PropertyPayload]
}>()

const form = reactive({
  title: props.property?.title ?? '',
  location: props.property?.location ?? '',
  price: (props.property?.price ?? '') as number | '',
  type: (props.property?.type ?? 'House') as PropertyType,
  image: props.property?.image ?? '',
  description: props.property?.description ?? '',
  is_published: props.property?.is_published ?? true,
})

const previewFailed = ref(false)

const previewUrl = computed(() => {
  const url = form.image.trim()
  return url.startsWith('http://') || url.startsWith('https://') ? url : ''
})

watch(previewUrl, () => {
  previewFailed.value = false
})

const typeOptions = [
  { label: 'House', value: 'House' },
  { label: 'Apartment', value: 'Apartment' },
  { label: 'Villa', value: 'Villa' },
  { label: 'Land', value: 'Land' },
  { label: 'Office', value: 'Office' },
]

function handleSubmit() {
  emit('save', {
    title: form.title,
    location: form.location,
    price: Number(form.price),
    type: form.type,
    image: form.image.trim() || null,
    description: form.description,
    is_published: form.is_published,
  })
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="handleSubmit">
    <Input
      v-model="form.image"
      label="Image URL"
      type="url"
      placeholder="https://example.com/property.jpg"
    />

    <div v-if="previewUrl" class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
      <img
        v-if="!previewFailed"
        :src="previewUrl"
        alt="Property preview"
        class="aspect-[21/9] w-full object-cover"
        @error="previewFailed = true"
      >
      <p v-else class="px-4 py-6 text-center text-sm text-amber-700">
        This image can't be displayed — the URL may be wrong, point to a web page instead of an image,
        or the site may block embedding. The listing will show a placeholder unless you use a direct image link.
      </p>
    </div>

    <Input v-model="form.title" label="Title" placeholder="Modern Family House" required />
    <Input v-model="form.location" label="Location" placeholder="Canggu, Bali" required />
    <CurrencyInput v-model="form.price" label="Price" placeholder="450,000.00" required />
    <Select v-model="form.type" label="Property Type" :options="typeOptions" required />
    <Textarea v-model="form.description" label="Description" placeholder="Describe the property..." required />
    <Switch
      v-model="form.is_published"
      label="Published"
      description="Visible on the public website"
    />

    <p v-if="error" role="alert" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">{{ error }}</p>

    <div class="flex justify-end gap-3 pt-2">
      <PrimaryButton type="submit" :loading="saving">
        {{ property ? 'Update Property' : 'Save Property' }}
      </PrimaryButton>
    </div>
  </form>
</template>
