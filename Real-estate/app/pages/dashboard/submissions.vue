<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: 'auth' })

const submissionStore = useSubmissionStore()
const { formatPrice, formatDate } = useFormat()

onMounted(() => {
  submissionStore.fetchSubmissions()
})

const statusStyles: Record<string, string> = {
  draft: 'bg-gray-100 text-gray-700',
  available: 'bg-green-100 text-green-700',
  pending: 'bg-amber-100 text-amber-700',
  sold: 'bg-blue-100 text-blue-700',
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-gray-900">Submissions</h1>
      <p class="mt-1 text-sm text-gray-500">Your property submissions and their publishing status</p>
    </div>

    <Loading v-if="submissionStore.loading" message="Loading submissions..." />

    <EmptyState
      v-else-if="submissionStore.submissions.length === 0"
      title="No submissions yet"
      message="Select a property from the public listings and fill the submission form."
    >
      <PrimaryButton @click="navigateTo('/')">Browse Properties</PrimaryButton>
    </EmptyState>

    <div v-else class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3.5 text-left font-semibold text-gray-700">Property</th>
              <th class="hidden px-6 py-3.5 text-left font-semibold text-gray-700 md:table-cell">Owner</th>
              <th class="px-6 py-3.5 text-left font-semibold text-gray-700">Listing Price</th>
              <th class="hidden px-6 py-3.5 text-left font-semibold text-gray-700 sm:table-cell">Status</th>
              <th class="px-6 py-3.5 text-left font-semibold text-gray-700">Published</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="submission in submissionStore.submissions" :key="submission.id" class="transition hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <img
                    v-if="submission.property?.image"
                    :src="submission.property.image"
                    :alt="submission.property.title"
                    class="size-10 shrink-0 rounded-lg object-cover"
                  >
                  <div class="min-w-0">
                    <p class="truncate font-medium text-gray-900">{{ submission.property?.title }}</p>
                    <p class="truncate text-xs text-gray-500">{{ formatDate(submission.created_at) }}</p>
                  </div>
                </div>
              </td>
              <td class="hidden px-6 py-4 md:table-cell">
                <p class="text-gray-900">{{ submission.owner_name }}</p>
                <p class="text-xs text-gray-500">{{ submission.email }}</p>
              </td>
              <td class="px-6 py-4 font-semibold text-primary-600">{{ formatPrice(submission.listing_price) }}</td>
              <td class="hidden px-6 py-4 sm:table-cell">
                <span
                  class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                  :class="statusStyles[submission.status] ?? 'bg-gray-100 text-gray-700'"
                >
                  {{ submission.status }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span
                  v-if="submission.published_at"
                  class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700"
                >
                  <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                  </svg>
                  {{ formatDate(submission.published_at) }}
                </span>
                <span
                  v-else-if="submission.publish_ready && submission.status !== 'draft'"
                  class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700"
                >
                  Awaiting publish
                </span>
                <span v-else class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                  Not ready
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
