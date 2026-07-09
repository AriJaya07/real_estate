<script setup lang="ts">
import { extractErrorMessage } from '~/services/api'
import type { SubmissionStatus } from '~/types'

const auth = useAuthStore()
const route = useRoute()
const propertyStore = usePropertyStore()
const submissionStore = useSubmissionStore()
const { formatPrice } = useFormat()

const form = reactive({
  owner_name: '',
  phone: '',
  email: '',
  address: '',
  listing_price: '' as number | string,
  status: 'available' as SubmissionStatus,
  description: '',
  notes: '',
  publish_ready: false,
})

const error = ref('')
const successOpen = ref(false)
const savingDraft = ref(false)

const statusOptions = [
  { label: 'Available', value: 'available' },
  { label: 'Pending', value: 'pending' },
  { label: 'Sold', value: 'sold' },
]

onMounted(() => {
  propertyStore.fetchProperty(Number(route.params.id))
})

async function saveSubmission(status: SubmissionStatus) {
  if (!propertyStore.selected) {
    return
  }
  error.value = ''
  try {
    await submissionStore.submit({
      ...form,
      listing_price: Number(form.listing_price),
      status,
      property_id: propertyStore.selected.id,
    })
    successOpen.value = true
  } catch (exception) {
    error.value = extractErrorMessage(exception)
  }
}

async function handleDraft() {
  savingDraft.value = true
  try {
    await saveSubmission('draft')
  } finally {
    savingDraft.value = false
  }
}

function handleSubmit() {
  saveSubmission(form.status)
}

async function closeSuccess() {
  successOpen.value = false
  await navigateTo('/dashboard')
}
</script>

<template>
  <div class="mx-auto max-w-4xl space-y-8">
    <Loading v-if="propertyStore.loading" message="Loading property..." />

    <EmptyState
      v-else-if="!propertyStore.selected"
      title="Property not found"
      message="The property you are looking for does not exist or has been removed."
    >
      <SecondaryButton @click="navigateTo('/dashboard')">Back to Dashboard</SecondaryButton>
    </EmptyState>

    <template v-else>
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="aspect-[21/9] bg-gray-100">
          <img
            v-if="propertyStore.selected.image"
            :src="propertyStore.selected.image"
            :alt="propertyStore.selected.title"
            class="size-full object-cover"
          >
        </div>
        <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-start sm:justify-between sm:p-8">
          <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ propertyStore.selected.title }}</h1>
            <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-500">
              <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
              </svg>
              {{ propertyStore.selected.location }}
            </p>
          </div>
          <div class="text-left sm:text-right">
            <p class="text-2xl font-bold text-primary-600">{{ formatPrice(propertyStore.selected.price) }}</p>
            <span class="mt-1 inline-block rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">
              {{ propertyStore.selected.type }}
            </span>
          </div>
        </div>
      </div>

      <div
        v-if="!auth.isAuthenticated"
        class="flex flex-col items-center gap-4 rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm sm:p-10"
      >
        <span class="flex size-12 items-center justify-center rounded-2xl bg-primary-50 text-primary-600">
          <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
          </svg>
        </span>
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Login to submit this property</h2>
          <p class="mt-1 text-sm text-gray-500">
            Create an account or sign in to fill the property submission form.
          </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
          <SecondaryButton @click="navigateTo('/register')">Register</SecondaryButton>
          <PrimaryButton @click="navigateTo('/login')">Login</PrimaryButton>
        </div>
      </div>

      <div v-else class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
        <h2 class="text-lg font-semibold text-gray-900">Property Submission</h2>
        <p class="mt-1 text-sm text-gray-500">Fill in the owner and listing details for this property</p>

        <hr class="my-6 border-gray-200">

        <form class="space-y-4" @submit.prevent="handleSubmit">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <Input v-model="form.owner_name" label="Owner Name" placeholder="John Doe" required />
            <Input v-model="form.phone" label="Phone Number" type="tel" placeholder="+62 812 3456 7890" required />
            <Input v-model="form.email" label="Email" type="email" placeholder="owner@example.com" required />
            <Input v-model="form.address" label="Address" placeholder="Street, city, region" required />
            <Input v-model="form.listing_price" label="Listing Price" type="number" placeholder="450000" required />
            <Select v-model="form.status" label="Property Status" :options="statusOptions" required />
          </div>
          <Textarea v-model="form.description" label="Description" placeholder="Describe the listing..." required />
          <Textarea v-model="form.notes" label="Notes" placeholder="Internal notes (optional)" :rows="3" />
          <Switch
            v-model="form.publish_ready"
            label="Publish Ready"
            description="Mark this submission as ready for website publishing"
          />

          <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">{{ error }}</p>

          <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
            <SecondaryButton :loading="savingDraft" :disabled="submissionStore.submitting" @click="handleDraft">
              Save Draft
            </SecondaryButton>
            <PrimaryButton type="submit" :loading="submissionStore.submitting && !savingDraft" :disabled="savingDraft">
              Submit
            </PrimaryButton>
          </div>
        </form>
      </div>
    </template>

    <Modal v-model="successOpen" title="Submission successful">
      <div class="space-y-4">
        <div class="flex items-center gap-3 rounded-xl bg-green-50 px-4 py-3">
          <svg class="size-6 shrink-0 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-green-800">
            Your property submission has been saved and sent for processing.
          </p>
        </div>
        <div class="flex justify-end">
          <PrimaryButton @click="closeSuccess">Back to Dashboard</PrimaryButton>
        </div>
      </div>
    </Modal>
  </div>
</template>
