import axios from 'axios'
import { defineStore } from 'pinia'
import { watchDebounced } from '@vueuse/core'
import { api } from '~/services/api'
import type { ImportResult, Property, PropertyPayload } from '~/types'

export const usePropertyStore = defineStore('property', () => {
  const properties = ref<Property[]>([])
  const selected = ref<Property | null>(null)
  const search = ref('')
  const loading = ref(false)
  const searching = ref(false)
  const saving = ref(false)

  let abortController: AbortController | null = null

  const filtered = computed(() => {
    const query = search.value.trim().toLowerCase()
    if (!query) {
      return properties.value
    }
    return properties.value.filter((property) =>
      [property.title, property.location, property.type]
        .some((field) => field.toLowerCase().includes(query)),
    )
  })

  async function fetchProperties(): Promise<void> {
    loading.value = true
    try {
      const { data } = await api.get<{ data: Property[] }>('/properties', {
        params: { search: search.value.trim() || undefined },
      })
      properties.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function searchProperties(): Promise<void> {
    abortController?.abort()
    const controller = new AbortController()
    abortController = controller
    searching.value = true
    try {
      const { data } = await api.get<{ data: Property[] }>('/properties', {
        params: { search: search.value.trim() || undefined },
        signal: controller.signal,
      })
      properties.value = data.data
    } catch (error) {
      if (!axios.isCancel(error)) {
        throw error
      }
    } finally {
      if (abortController === controller) {
        searching.value = false
        abortController = null
      }
    }
  }

  watchDebounced(search, searchProperties, { debounce: 300, maxWait: 1200 })

  async function fetchProperty(id: number): Promise<void> {
    loading.value = true
    selected.value = null
    try {
      const { data } = await api.get<{ data: Property }>(`/properties/${id}`)
      selected.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function createProperty(payload: PropertyPayload): Promise<void> {
    saving.value = true
    try {
      const { data } = await api.post<{ data: Property }>('/properties', payload)
      properties.value.unshift(data.data)
    } finally {
      saving.value = false
    }
  }

  async function updateProperty(id: number, payload: PropertyPayload): Promise<void> {
    saving.value = true
    try {
      const { data } = await api.put<{ data: Property }>(`/properties/${id}`, payload)
      const index = properties.value.findIndex((property) => property.id === id)
      if (index !== -1) {
        properties.value[index] = data.data
      }
    } finally {
      saving.value = false
    }
  }

  async function importProperties(file: File): Promise<ImportResult> {
    saving.value = true
    const payload = new FormData()
    payload.append('file', file)
    try {
      const { data } = await api.post<ImportResult>('/properties/import', payload)
      await fetchProperties()
      return data
    } finally {
      saving.value = false
    }
  }

  async function deleteProperty(id: number): Promise<void> {
    await api.delete(`/properties/${id}`)
    properties.value = properties.value.filter((property) => property.id !== id)
  }

  return {
    properties,
    selected,
    search,
    loading,
    searching,
    saving,
    filtered,
    fetchProperties,
    fetchProperty,
    createProperty,
    updateProperty,
    importProperties,
    deleteProperty,
  }
})
