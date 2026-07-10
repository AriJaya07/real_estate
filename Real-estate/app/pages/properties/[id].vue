<script setup lang="ts">
import { api, extractErrorMessage } from '~/services/api'
import type { PropertySubmission, SubmissionStatus } from '~/types'

const auth = useAuthStore()
const route = useRoute()
const propertyStore = usePropertyStore()
const submissionStore = useSubmissionStore()
const { formatPrice } = useFormat()
const { statusMeta, isEditable } = useSubmissionStatus()

const form = reactive({
  owner_name: '',
  phone: '',
  email: '',
  address: '',
  listing_price: '' as number | '',
  description: '',
  notes: '',
  publish_ready: false,
})

const error = ref('')
const successOpen = ref(false)
const successStatus = ref<SubmissionStatus>('pending')
const savingDraft = ref(false)

const propertyId = Number(route.params.id)
const relatedSubmissions = ref<PropertySubmission[]>([])

const publishedFromSubmission = computed(() =>
  relatedSubmissions.value.find((submission) => submission.published_property_id === propertyId) ?? null,
)
// Newest submission the user made for this property; the form edits it in place.
const existingSubmission = computed(() =>
  relatedSubmissions.value.find((submission) => submission.property?.id === propertyId) ?? null,
)
const formLocked = computed(() =>
  existingSubmission.value !== null && !isEditable(existingSubmission.value.status),
)

function fillFormFrom(submission: PropertySubmission) {
  form.owner_name = submission.owner_name
  form.phone = submission.phone
  form.email = submission.email
  form.address = submission.address
  form.listing_price = submission.listing_price
  form.description = submission.description
  form.notes = submission.notes ?? ''
  form.publish_ready = submission.publish_ready
}

async function fetchRelatedSubmissions() {
  const { data } = await api.get<{ data: PropertySubmission[] }>('/property-submissions', {
    params: { related_property_id: propertyId },
  })
  relatedSubmissions.value = data.data
}

onMounted(async () => {
  try {
    await propertyStore.fetchProperty(propertyId)
  } catch {
    // 404 or network failure — template falls back to the empty state below.
  }
  if (auth.isAuthenticated) {
    try {
      await fetchRelatedSubmissions()
      if (existingSubmission.value) {
        fillFormFrom(existingSubmission.value)
      }
    } catch {
      // Prefill is informational only; the page works without it.
    }
  }
})

async function saveSubmission(status: SubmissionStatus) {
  if (!propertyStore.selected) {
    return
  }
  error.value = ''
  try {
    if (existingSubmission.value) {
      await submissionStore.updateSubmission(existingSubmission.value.id, {
        ...form,
        listing_price: Number(form.listing_price),
        status,
      })
      await fetchRelatedSubmissions()
    } else {
      await submissionStore.submit({
        ...form,
        listing_price: Number(form.listing_price),
        status,
        property_id: propertyStore.selected.id,
      })
    }
    successStatus.value = status
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
  saveSubmission('pending')
}

async function closeSuccess() {
  successOpen.value = false
  await navigateTo('/dashboard/submissions')
}
</script>

<template>
  <div class="mx-auto max-w-4xl space-y-8">
    <Loading v-if="propertyStore.loading" message="Loading property..." />

    <EmptyState
      v-else-if="!propertyStore.selected"
      title="Listing not available"
      message="This listing does not exist or has been removed from the website. If it was your published submission, its details are still saved on your submissions page."
    >
      <SecondaryButton @click="navigateTo('/')">Back to Listings</SecondaryButton>
      <PrimaryButton v-if="auth.isAuthenticated" @click="navigateTo('/dashboard/submissions')">My Submissions</PrimaryButton>
    </EmptyState>

    <template v-else>
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="aspect-[21/9] bg-gray-100">
          <PropertyImage :src="propertyStore.selected.image" :alt="propertyStore.selected.title" class="size-full" />
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
        v-if="publishedFromSubmission"
        class="flex flex-col gap-4 rounded-2xl border border-green-200 bg-green-50 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8"
      >
        <div class="flex items-start gap-3">
          <svg class="mt-0.5 size-6 shrink-0 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <h2 class="text-base font-semibold text-green-900">This is your published listing</h2>
            <p class="mt-1 text-sm text-green-800">
              It was created from your submission and is
              {{ propertyStore.selected.is_published ? 'live on the website' : 'currently taken off the website' }}.
              Track it on your submissions page, or edit and unpublish it from Dashboard → Properties.
            </p>
          </div>
        </div>
        <div class="flex shrink-0 gap-3">
          <SecondaryButton @click="navigateTo('/dashboard/submissions')">My Submissions</SecondaryButton>
          <PrimaryButton @click="navigateTo('/dashboard/properties')">Manage Listing</PrimaryButton>
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

      <div v-else-if="!publishedFromSubmission" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="flex flex-wrap items-center gap-3">
          <h2 class="text-lg font-semibold text-gray-900">
            {{ existingSubmission ? 'Your Submission' : 'Property Submission' }}
          </h2>
          <span
            v-if="existingSubmission"
            class="rounded-full px-3 py-1 text-xs font-semibold"
            :class="statusMeta(existingSubmission.status).badge"
          >
            {{ statusMeta(existingSubmission.status).label }}
          </span>
        </div>
        <p class="mt-1 text-sm text-gray-500">
          {{ existingSubmission
            ? statusMeta(existingSubmission.status).next
            : 'Fill in the owner and listing details. Submitted listings go through review before they are published.' }}
        </p>

        <hr class="my-6 border-gray-200">

        <form class="space-y-4" @submit.prevent="handleSubmit">
          <fieldset :disabled="formLocked" class="space-y-4" :class="formLocked ? 'opacity-60' : ''">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <Input v-model="form.owner_name" label="Owner Name" placeholder="John Doe" required />
              <Input v-model="form.phone" label="Phone Number" type="tel" placeholder="+62 812 3456 7890" required />
              <Input v-model="form.email" label="Email" type="email" placeholder="owner@example.com" required />
              <Input v-model="form.address" label="Address" placeholder="Street, city, region" required />
              <div class="sm:col-span-2">
                <CurrencyInput v-model="form.listing_price" label="Listing Price" placeholder="1,250,000.00" required />
              </div>
            </div>
            <Textarea v-model="form.description" label="Description" placeholder="Describe the listing..." required />
            <Textarea v-model="form.notes" label="Notes" placeholder="Internal notes (optional)" :rows="3" />
          </fieldset>

          <p v-if="error" role="alert" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">{{ error }}</p>

          <div v-if="formLocked" class="flex flex-col gap-3 rounded-xl bg-gray-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-600">
              These details are locked while your submission moves through the pipeline.
            </p>
            <NuxtLink to="/dashboard/submissions" class="shrink-0 text-sm font-semibold text-primary-600 hover:text-primary-700">
              Track progress →
            </NuxtLink>
          </div>
          <div v-else class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
            <SecondaryButton :loading="savingDraft" :disabled="submissionStore.submitting" @click="handleDraft">
              Save Draft
            </SecondaryButton>
            <PrimaryButton type="submit" :loading="submissionStore.submitting && !savingDraft" :disabled="savingDraft">
              {{ existingSubmission?.status === 'rejected' ? 'Resubmit for Review' : 'Submit for Review' }}
            </PrimaryButton>
          </div>
        </form>
      </div>
    </template>

    <Modal v-model="successOpen" :title="successStatus === 'draft' ? 'Draft saved' : 'Submitted for review'">
      <div class="space-y-4">
        <div class="flex items-center gap-3 rounded-xl bg-green-50 px-4 py-3">
          <svg class="size-6 shrink-0 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-green-800">
            <template v-if="successStatus === 'draft'">
              Your draft has been saved. Edit it and submit for review from your submissions page.
            </template>
            <template v-else>
              Your submission is now pending review. It will be published to the website once approved.
            </template>
          </p>
        </div>
        <div class="flex justify-end">
          <PrimaryButton @click="closeSuccess">
            View My Submissions
          </PrimaryButton>
        </div>
      </div>
    </Modal>
  </div>
</template>
