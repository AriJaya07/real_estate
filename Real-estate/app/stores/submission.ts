import { defineStore } from 'pinia'
import { api } from '~/services/api'
import type { PropertySubmission, SubmissionPayload } from '~/types'

export const useSubmissionStore = defineStore('submission', () => {
  const submissions = ref<PropertySubmission[]>([])
  const loading = ref(false)
  const submitting = ref(false)

  async function fetchSubmissions(): Promise<void> {
    loading.value = true
    try {
      const { data } = await api.get<{ data: PropertySubmission[] }>('/property-submissions')
      submissions.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function submit(payload: SubmissionPayload): Promise<PropertySubmission> {
    submitting.value = true
    try {
      const { data } = await api.post<{ data: PropertySubmission }>('/property-submissions', payload)
      submissions.value.unshift(data.data)
      return data.data
    } finally {
      submitting.value = false
    }
  }

  return { submissions, loading, submitting, fetchSubmissions, submit }
})
