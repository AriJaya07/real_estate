import { defineStore } from 'pinia'
import { watchDebounced } from '@vueuse/core'
import { api } from '~/services/api'
import type { PropertySubmission, SubmissionPayload, SubmissionStatus, UpdateSubmissionPayload } from '~/types'

interface PaginationMeta {
  current_page: number
  last_page: number
  total: number
}

export const useSubmissionStore = defineStore('submission', () => {
  const submissions = ref<PropertySubmission[]>([])
  const meta = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0 })
  const search = ref('')
  const statusFilter = ref<SubmissionStatus | ''>('')
  const sort = ref<'newest' | 'oldest'>('newest')
  const page = ref(1)
  const loading = ref(false)
  const submitting = ref(false)
  const exporting = ref(false)

  function currentParams() {
    return {
      search: search.value.trim() || undefined,
      status: statusFilter.value || undefined,
      sort: sort.value,
      page: page.value,
    }
  }

  async function fetchSubmissions(): Promise<void> {
    loading.value = true
    try {
      const { data } = await api.get<{ data: PropertySubmission[]; meta: PaginationMeta }>(
        '/property-submissions',
        { params: currentParams() },
      )
      submissions.value = data.data
      meta.value = data.meta
    } finally {
      loading.value = false
    }
  }

  watchDebounced(search, () => {
    page.value = 1
    fetchSubmissions()
  }, { debounce: 300, maxWait: 1200 })

  watch([statusFilter, sort], () => {
    page.value = 1
    fetchSubmissions()
  })

  watch(page, () => {
    fetchSubmissions()
  })

  async function submit(payload: SubmissionPayload): Promise<PropertySubmission> {
    submitting.value = true
    try {
      const { data } = await api.post<{ data: PropertySubmission }>('/property-submissions', payload)
      return data.data
    } finally {
      submitting.value = false
    }
  }

  async function updateSubmission(id: number, payload: UpdateSubmissionPayload): Promise<PropertySubmission> {
    submitting.value = true
    try {
      const { data } = await api.put<{ data: PropertySubmission }>(`/property-submissions/${id}`, payload)
      const index = submissions.value.findIndex((submission) => submission.id === id)
      if (index !== -1) {
        submissions.value[index] = data.data
      }
      return data.data
    } finally {
      submitting.value = false
    }
  }

  async function deleteSubmission(id: number): Promise<void> {
    await api.delete(`/property-submissions/${id}`)
    await fetchSubmissions()
  }

  async function exportCsv(): Promise<void> {
    exporting.value = true
    try {
      const { data } = await api.get<Blob>('/property-submissions/export', {
        params: currentParams(),
        responseType: 'blob',
      })
      const url = URL.createObjectURL(data)
      const link = document.createElement('a')
      link.href = url
      link.download = 'property-submissions.csv'
      link.click()
      URL.revokeObjectURL(url)
    } finally {
      exporting.value = false
    }
  }

  return {
    submissions,
    meta,
    search,
    statusFilter,
    sort,
    page,
    loading,
    submitting,
    exporting,
    fetchSubmissions,
    submit,
    updateSubmission,
    deleteSubmission,
    exportCsv,
  }
})
