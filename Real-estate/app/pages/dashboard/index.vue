<script setup lang="ts">
import { api } from '~/services/api'
import type { User } from '~/types'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const propertyStore = usePropertyStore()
const submissionStore = useSubmissionStore()

const users = ref<User[]>([])
const loading = ref(true)
const { formatPrice } = useFormat()

onMounted(async () => {
  try {
    const [usersResponse] = await Promise.all([
      api.get<{ data: User[] }>('/users'),
      propertyStore.fetchProperties(),
      submissionStore.fetchSubmissions(),
    ])
    users.value = usersResponse.data.data
  } finally {
    loading.value = false
  }
})

const stats = computed(() => [
  { label: 'Properties', value: propertyStore.properties.length },
  { label: 'Users', value: users.value.length },
  { label: 'My Submissions', value: submissionStore.submissions.length },
])
</script>

<template>
  <div class="space-y-8">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-gray-900">Dashboard</h1>
      <p class="mt-1 text-sm text-gray-500">Overview of your real estate system</p>
    </div>

    <Loading v-if="loading" message="Loading overview..." />

    <template v-else>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
        >
          <p class="text-sm font-medium text-gray-500">{{ stat.label }}</p>
          <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900">{{ stat.value }}</p>
        </div>
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
          <h2 class="text-base font-semibold text-gray-900">Recent properties</h2>
        </div>
        <EmptyState
          v-if="propertyStore.properties.length === 0"
          title="No properties yet"
          message="Create your first property from the Properties page."
          class="border-0"
        />
        <ul v-else class="divide-y divide-gray-100">
          <li
            v-for="property in propertyStore.properties.slice(0, 5)"
            :key="property.id"
            class="flex items-center gap-4 px-6 py-4"
          >
            <img
              v-if="property.image"
              :src="property.image"
              :alt="property.title"
              class="size-12 rounded-xl object-cover"
            >
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-semibold text-gray-900">{{ property.title }}</p>
              <p class="truncate text-sm text-gray-500">{{ property.location }}</p>
            </div>
            <p class="text-sm font-semibold text-primary-600">{{ formatPrice(property.price) }}</p>
          </li>
        </ul>
      </div>
    </template>
  </div>
</template>
