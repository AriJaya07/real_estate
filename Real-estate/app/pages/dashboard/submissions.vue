<script setup lang="ts">
import { extractErrorMessage } from '~/services/api'
import { PIPELINE_STEPS } from '~/composables/useSubmissionStatus'
import type { PropertySubmission, SubmissionStatus } from '~/types'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const submissionStore = useSubmissionStore()
const { formatPrice, formatDate } = useFormat()
const { statusMeta, isEditable, pipelineIndex } = useSubmissionStatus()
const { push } = useToast()

const detailsOpen = ref(false)
const editOpen = ref(false)
const deleteOpen = ref(false)
const selected = ref<PropertySubmission | null>(null)
const deletingBusy = ref(false)
const savingDraft = ref(false)
const editError = ref('')

const editForm = reactive({
  owner_name: '',
  phone: '',
  email: '',
  address: '',
  listing_price: '' as number | '',
  description: '',
  notes: '',
  publish_ready: false,
})

const statusOptions = [
  { label: 'All statuses', value: '' },
  { label: 'Draft', value: 'draft' },
  { label: 'Pending Review', value: 'pending' },
  { label: 'AI Processing', value: 'ai_processing' },
  { label: 'In ClickUp Review', value: 'clickup_review' },
  { label: 'Ready to Publish', value: 'ready' },
  { label: 'Published', value: 'published' },
  { label: 'Rejected', value: 'rejected' },
]

const sortOptions = [
  { label: 'Newest first', value: 'newest' },
  { label: 'Oldest first', value: 'oldest' },
]

const syncing = ref(false)
const publishingId = ref<number | null>(null)

onMounted(async () => {
  await submissionStore.fetchSubmissions()
  try {
    await submissionStore.syncClickUp()
  } catch {
  }
})

async function handleSync() {
  syncing.value = true
  try {
    const result = await submissionStore.syncClickUp()
    push(result.updated > 0
      ? `${result.updated} submission${result.updated === 1 ? '' : 's'} updated from ClickUp.`
      : `Up to date — ${result.checked} submission${result.checked === 1 ? '' : 's'} checked.`)
  } catch (exception) {
    push(extractErrorMessage(exception), 'error')
  } finally {
    syncing.value = false
  }
}

async function handlePublish(submission: PropertySubmission) {
  publishingId.value = submission.id
  try {
    await submissionStore.publishSubmission(submission.id)
    push('Listing published to the website.')
  } catch (exception) {
    push(extractErrorMessage(exception), 'error')
  } finally {
    publishingId.value = null
  }
}

function openDetails(submission: PropertySubmission) {
  selected.value = submission
  detailsOpen.value = true
}

function openEdit(submission: PropertySubmission) {
  selected.value = submission
  editForm.owner_name = submission.owner_name
  editForm.phone = submission.phone
  editForm.email = submission.email
  editForm.address = submission.address
  editForm.listing_price = submission.listing_price
  editForm.description = submission.description
  editForm.notes = submission.notes ?? ''
  editForm.publish_ready = submission.publish_ready
  editError.value = ''
  editOpen.value = true
}

function openDelete(submission: PropertySubmission) {
  selected.value = submission
  deleteOpen.value = true
}

async function saveEdit(status: SubmissionStatus) {
  if (!selected.value) {
    return
  }
  editError.value = ''
  try {
    await submissionStore.updateSubmission(selected.value.id, {
      ...editForm,
      listing_price: Number(editForm.listing_price),
      status,
    })
    push(status === 'draft' ? 'Draft updated.' : 'Submission sent for review.')
    editOpen.value = false
    await submissionStore.fetchSubmissions()
  } catch (exception) {
    editError.value = extractErrorMessage(exception)
  }
}

async function handleEditDraft() {
  savingDraft.value = true
  try {
    await saveEdit('draft')
  } finally {
    savingDraft.value = false
  }
}

async function handleDelete() {
  if (!selected.value) {
    return
  }
  deletingBusy.value = true
  try {
    await submissionStore.deleteSubmission(selected.value.id)
    push('Submission deleted.')
    deleteOpen.value = false
  } catch (exception) {
    push(extractErrorMessage(exception), 'error')
  } finally {
    deletingBusy.value = false
  }
}

async function handleExport() {
  try {
    await submissionStore.exportCsv()
    push('CSV exported.')
  } catch (exception) {
    push(extractErrorMessage(exception), 'error')
  }
}

function clickUpLabel(submission: PropertySubmission): string | null {
  if (!submission.clickup_status) {
    return null
  }
  return submission.clickup_status.replace(/\b\w/g, (character) => character.toUpperCase())
}

const detailFields = computed(() => {
  if (!selected.value) {
    return []
  }
  return [
    { label: 'Owner', value: selected.value.owner_name },
    { label: 'Phone', value: selected.value.phone },
    { label: 'Email', value: selected.value.email },
    { label: 'Address', value: selected.value.address },
    { label: 'Listing Price', value: formatPrice(selected.value.listing_price) },
    { label: 'ClickUp Status', value: clickUpLabel(selected.value) ?? '—' },
    { label: 'Submitted', value: formatDate(selected.value.created_at) },
    { label: 'Published', value: selected.value.published_at ? formatDate(selected.value.published_at) : '—' },
  ]
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Submissions</h1>
        <p class="mt-1 text-sm text-gray-500">Track every submission through the publishing pipeline</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <SecondaryButton :loading="syncing" @click="handleSync">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          Sync ClickUp
        </SecondaryButton>
        <SecondaryButton :loading="submissionStore.exporting" @click="handleExport">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
          </svg>
          Export CSV
        </SecondaryButton>
      </div>
    </div>

    <div class="rounded-2xl border border-primary-100 bg-primary-50 px-5 py-4 text-sm text-primary-900">
      <p class="font-semibold">How publishing works</p>
      <p class="mt-1 text-primary-800">
        Pick a listing on the website and submit your property details. Your submission is enriched by AI,
        then reviewed by our team in ClickUp — press <span class="font-semibold">Sync ClickUp</span> to pull the
        latest review status. Once it reaches <span class="font-semibold">Ready to Publish</span>, press
        <span class="font-semibold">Publish</span> and your property goes live on the website as its own listing,
        which you can then open here or manage from Dashboard → Properties.
      </p>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
      <Input v-model="submissionStore.search" label="Search" placeholder="Owner, email, property..." />
      <Select v-model="submissionStore.statusFilter" label="Status" :options="statusOptions" />
      <Select v-model="submissionStore.sort" label="Sort" :options="sortOptions" />
    </div>

    <Loading v-if="submissionStore.loading" message="Loading submissions..." />

    <EmptyState
      v-else-if="submissionStore.submissions.length === 0"
      title="No submissions found"
      message="Adjust your filters, or select a property from the public listings to create a submission."
    >
      <PrimaryButton @click="navigateTo('/')">Browse Properties</PrimaryButton>
    </EmptyState>

    <template v-else>
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3.5 text-left font-semibold text-gray-700">Property</th>
                <th class="hidden px-6 py-3.5 text-left font-semibold text-gray-700 lg:table-cell">Owner</th>
                <th class="hidden px-6 py-3.5 text-left font-semibold text-gray-700 sm:table-cell">Listing Price</th>
                <th class="px-6 py-3.5 text-left font-semibold text-gray-700">Status</th>
                <th class="hidden px-6 py-3.5 text-left font-semibold text-gray-700 md:table-cell">Published</th>
                <th class="px-6 py-3.5 text-right font-semibold text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="submission in submissionStore.submissions" :key="submission.id" class="transition hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <PropertyImage
                      :src="submission.property?.image ?? null"
                      :alt="submission.property?.title ?? 'Property'"
                      class="size-10 shrink-0 rounded-lg"
                    />
                    <div class="min-w-0">
                      <p class="truncate font-medium text-gray-900">{{ submission.property?.title }}</p>
                      <p class="truncate text-xs text-gray-500">{{ formatDate(submission.created_at) }}</p>
                    </div>
                  </div>
                </td>
                <td class="hidden px-6 py-4 lg:table-cell">
                  <p class="text-gray-900">{{ submission.owner_name }}</p>
                  <p class="text-xs text-gray-500">{{ submission.email }}</p>
                </td>
                <td class="hidden px-6 py-4 font-semibold text-primary-600 sm:table-cell">
                  {{ formatPrice(submission.listing_price) }}
                </td>
                <td class="px-6 py-4">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusMeta(submission.status).badge">
                    {{ statusMeta(submission.status).label }}
                  </span>
                  <p
                    v-if="clickUpLabel(submission) && (submission.status === 'clickup_review' || submission.status === 'ai_processing')"
                    class="mt-1 text-xs text-gray-500"
                  >
                    ClickUp: {{ clickUpLabel(submission) }}
                  </p>
                  <p v-if="submission.status === 'published'" class="mt-1 text-xs" :class="submission.published_property?.is_published ? 'text-green-600' : 'text-amber-600'">
                    {{ submission.published_property
                      ? (submission.published_property.is_published ? 'Live on the website' : 'Taken off the website')
                      : 'Listing was deleted' }}
                  </p>
                </td>
                <td class="hidden px-6 py-4 md:table-cell">
                  <span v-if="submission.published_at" class="text-gray-700">{{ formatDate(submission.published_at) }}</span>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex justify-end gap-1">
                    <button
                      v-if="submission.status === 'ready'"
                      type="button"
                      :disabled="publishingId === submission.id"
                      class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-primary-700 disabled:opacity-60"
                      @click="handlePublish(submission)"
                    >
                      <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75" />
                      </svg>
                      Publish
                    </button>
                    <NuxtLink
                      v-if="submission.status === 'published' && submission.published_property"
                      :to="`/properties/${submission.published_property.id}`"
                      class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-green-700"
                    >
                      <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                      </svg>
                      View listing
                    </NuxtLink>
                    <button
                      type="button"
                      class="rounded-lg p-2 text-gray-500 transition hover:bg-primary-50 hover:text-primary-600"
                      aria-label="View details"
                      title="View details"
                      @click="openDetails(submission)"
                    >
                      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </button>
                    <button
                      v-if="isEditable(submission.status)"
                      type="button"
                      class="rounded-lg p-2 text-gray-500 transition hover:bg-primary-50 hover:text-primary-600"
                      aria-label="Edit submission"
                      title="Edit submission"
                      @click="openEdit(submission)"
                    >
                      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                      </svg>
                    </button>
                    <button
                      v-if="isEditable(submission.status)"
                      type="button"
                      class="rounded-lg p-2 text-gray-500 transition hover:bg-red-50 hover:text-red-600"
                      aria-label="Delete submission"
                      title="Delete submission"
                      @click="openDelete(submission)"
                    >
                      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">
          Page {{ submissionStore.meta.current_page }} of {{ submissionStore.meta.last_page }}
          · {{ submissionStore.meta.total }} total
        </p>
        <div class="flex gap-2">
          <SecondaryButton
            :disabled="submissionStore.meta.current_page <= 1"
            @click="submissionStore.page = submissionStore.meta.current_page - 1"
          >
            Previous
          </SecondaryButton>
          <SecondaryButton
            :disabled="submissionStore.meta.current_page >= submissionStore.meta.last_page"
            @click="submissionStore.page = submissionStore.meta.current_page + 1"
          >
            Next
          </SecondaryButton>
        </div>
      </div>
    </template>

    <Modal v-model="detailsOpen" title="Submission Details" size="lg">
      <div v-if="selected" class="space-y-6">
        <div class="flex items-center gap-4">
          <PropertyImage
            :src="selected.property?.image ?? null"
            :alt="selected.property?.title ?? 'Property'"
            class="size-16 shrink-0 rounded-xl"
          />
          <div>
            <h3 class="text-base font-semibold text-gray-900">{{ selected.property?.title }}</h3>
            <p class="text-sm text-gray-500">{{ selected.property?.location }}</p>
          </div>
        </div>

        <div>
          <p class="mb-3 text-sm font-semibold text-gray-700">Publishing pipeline</p>
          <template v-if="selected.status === 'draft' || selected.status === 'rejected'">
            <div class="flex items-center gap-3 rounded-xl px-4 py-3" :class="selected.status === 'draft' ? 'bg-gray-50' : 'bg-red-50'">
              <span class="size-2.5 rounded-full" :class="statusMeta(selected.status).dot" />
              <p class="text-sm" :class="selected.status === 'draft' ? 'text-gray-700' : 'text-red-700'">
                <span class="font-semibold">{{ statusMeta(selected.status).label }}.</span>
                {{ statusMeta(selected.status).next }}
              </p>
            </div>
          </template>
          <ol v-else class="space-y-2">
            <li
              v-for="(step, index) in PIPELINE_STEPS"
              :key="step"
              class="flex items-center gap-3 rounded-xl px-4 py-2.5"
              :class="index === pipelineIndex(selected.status) ? 'bg-primary-50' : ''"
            >
              <span
                class="flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                :class="index < pipelineIndex(selected.status)
                  ? 'bg-green-100 text-green-700'
                  : index === pipelineIndex(selected.status)
                    ? 'bg-primary-600 text-white'
                    : 'bg-gray-100 text-gray-400'"
              >
                <svg v-if="index < pipelineIndex(selected.status)" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <template v-else>{{ index + 1 }}</template>
              </span>
              <div>
                <p class="text-sm font-medium" :class="index <= pipelineIndex(selected.status) ? 'text-gray-900' : 'text-gray-400'">
                  {{ statusMeta(step).label }}
                </p>
                <p v-if="index === pipelineIndex(selected.status)" class="text-xs text-gray-500">
                  {{ statusMeta(step).next }}
                </p>
              </div>
            </li>
          </ol>
        </div>

        <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
          <div v-for="field in detailFields" :key="field.label">
            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ field.label }}</dt>
            <dd class="mt-0.5 text-sm text-gray-900">{{ field.value }}</dd>
          </div>
        </dl>

        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Description</p>
          <p class="mt-1 text-sm text-gray-700">{{ selected.description }}</p>
        </div>
        <div v-if="selected.notes">
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Notes</p>
          <p class="mt-1 text-sm text-gray-700">{{ selected.notes }}</p>
        </div>

        <div v-if="selected.status === 'published' && selected.published_property" class="flex justify-end border-t border-gray-100 pt-4">
          <NuxtLink
            :to="`/properties/${selected.published_property.id}`"
            class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700"
          >
            View live listing
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </NuxtLink>
        </div>
      </div>
    </Modal>

    <Modal v-model="editOpen" title="Edit Submission" size="lg">
      <form class="space-y-4" @submit.prevent="saveEdit('pending')">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <Input v-model="editForm.owner_name" label="Owner Name" required />
          <Input v-model="editForm.phone" label="Phone Number" type="tel" required />
          <Input v-model="editForm.email" label="Email" type="email" required />
          <Input v-model="editForm.address" label="Address" required />
          <div class="sm:col-span-2">
            <CurrencyInput v-model="editForm.listing_price" label="Listing Price" required />
          </div>
        </div>
        <Textarea v-model="editForm.description" label="Description" required />
        <Textarea v-model="editForm.notes" label="Notes" :rows="3" />

        <p v-if="editError" role="alert" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">{{ editError }}</p>

        <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
          <SecondaryButton :loading="savingDraft" :disabled="submissionStore.submitting" @click="handleEditDraft">
            Save Draft
          </SecondaryButton>
          <PrimaryButton type="submit" :loading="submissionStore.submitting && !savingDraft" :disabled="savingDraft">
            Submit for Review
          </PrimaryButton>
        </div>
      </form>
    </Modal>

    <Modal v-model="deleteOpen" title="Delete Submission">
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          Are you sure you want to delete the submission for
          <span class="font-semibold text-gray-900">{{ selected?.property?.title }}</span>?
          This action cannot be undone.
        </p>
        <div class="flex justify-end gap-3">
          <SecondaryButton @click="deleteOpen = false">Cancel</SecondaryButton>
          <button
            type="button"
            :disabled="deletingBusy"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
            @click="handleDelete"
          >
            Delete
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>
