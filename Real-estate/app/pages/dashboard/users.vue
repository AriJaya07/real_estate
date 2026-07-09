<script setup lang="ts">
import { api, extractErrorMessage } from '~/services/api'
import type { User } from '~/types'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const auth = useAuthStore()
const { formatDate } = useFormat()
const { push } = useToast()

const users = ref<User[]>([])
const loading = ref(true)
const deleteOpen = ref(false)
const deleting = ref<User | null>(null)
const deletingBusy = ref(false)
const error = ref('')

onMounted(async () => {
  try {
    const { data } = await api.get<{ data: User[] }>('/users')
    users.value = data.data
  } finally {
    loading.value = false
  }
})

function openDelete(user: User) {
  deleting.value = user
  error.value = ''
  deleteOpen.value = true
}

async function handleDelete() {
  if (!deleting.value) {
    return
  }
  deletingBusy.value = true
  error.value = ''
  try {
    await api.delete(`/users/${deleting.value.id}`)
    users.value = users.value.filter((user) => user.id !== deleting.value?.id)
    push('User deleted.')
    deleteOpen.value = false
  } catch (exception) {
    error.value = extractErrorMessage(exception)
  } finally {
    deletingBusy.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-gray-900">Users</h1>
      <p class="mt-1 text-sm text-gray-500">All registered accounts</p>
    </div>

    <Loading v-if="loading" message="Loading users..." />

    <EmptyState v-else-if="users.length === 0" title="No users yet" message="Registered users will appear here." />

    <div v-else class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3.5 text-left font-semibold text-gray-700">User</th>
              <th class="hidden px-6 py-3.5 text-left font-semibold text-gray-700 sm:table-cell">Joined</th>
              <th class="px-6 py-3.5 text-right font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="user in users" :key="user.id" class="transition hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <span class="flex size-9 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700">
                    {{ user.username.charAt(0).toUpperCase() }}
                  </span>
                  <div>
                    <p class="font-medium text-gray-900">{{ user.username }}</p>
                    <p v-if="user.id === auth.user?.id" class="text-xs text-gray-500">You</p>
                  </div>
                </div>
              </td>
              <td class="hidden px-6 py-4 text-gray-500 sm:table-cell">{{ formatDate(user.created_at) }}</td>
              <td class="px-6 py-4">
                <div class="flex justify-end">
                  <button
                    v-if="user.id !== auth.user?.id"
                    type="button"
                    class="rounded-lg p-2 text-gray-500 transition hover:bg-red-50 hover:text-red-600"
                    :aria-label="`Delete ${user.username}`"
                    @click="openDelete(user)"
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

    <Modal v-model="deleteOpen" title="Delete User">
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          Are you sure you want to delete
          <span class="font-semibold text-gray-900">{{ deleting?.username }}</span>?
          This action cannot be undone.
        </p>
        <p v-if="error" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">{{ error }}</p>
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
