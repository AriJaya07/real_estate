<script setup lang="ts">
import { extractErrorMessage } from '~/services/api'
import type { ImportResult, Property, PropertyPayload } from '~/types'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const propertyStore = usePropertyStore()
const { formatPrice } = useFormat()
const { push } = useToast()

const formOpen = ref(false)
const deleteOpen = ref(false)
const importOpen = ref(false)
const editing = ref<Property | null>(null)
const deleting = ref<Property | null>(null)
const deletingBusy = ref(false)
const formError = ref('')
const importError = ref('')
const importFile = ref<File | null>(null)
const importResult = ref<ImportResult | null>(null)

onMounted(() => {
  propertyStore.manageMode = true
  propertyStore.fetchProperties()
})

async function handleTogglePublish(property: Property) {
  try {
    await propertyStore.togglePublish(property.id, !property.is_published)
    push(property.is_published ? 'Property unpublished.' : 'Property published to website.')
  } catch (exception) {
    push(extractErrorMessage(exception), 'error')
  }
}

function openCreate() {
  editing.value = null
  formError.value = ''
  formOpen.value = true
}

function openEdit(property: Property) {
  editing.value = property
  formError.value = ''
  formOpen.value = true
}

function openDelete(property: Property) {
  deleting.value = property
  deleteOpen.value = true
}

function openImport() {
  importFile.value = null
  importResult.value = null
  importError.value = ''
  importOpen.value = true
}

async function handleSave(payload: PropertyPayload) {
  formError.value = ''
  try {
    if (editing.value) {
      await propertyStore.updateProperty(editing.value.id, payload)
      push('Property updated.')
    } else {
      await propertyStore.createProperty(payload)
      push('Property created.')
    }
    formOpen.value = false
  } catch (exception) {
    formError.value = extractErrorMessage(exception)
  }
}

async function handleDelete() {
  if (!deleting.value) {
    return
  }
  deletingBusy.value = true
  try {
    await propertyStore.deleteProperty(deleting.value.id)
    push('Property deleted.')
    deleteOpen.value = false
  } catch (exception) {
    push(extractErrorMessage(exception), 'error')
  } finally {
    deletingBusy.value = false
  }
}

function handleImportFile(event: Event) {
  importFile.value = (event.target as HTMLInputElement).files?.[0] ?? null
  importResult.value = null
  importError.value = ''
}

async function handleImport() {
  if (!importFile.value) {
    return
  }
  importError.value = ''
  try {
    importResult.value = await propertyStore.importProperties(importFile.value)
    push(`${importResult.value.imported} properties imported.`)
  } catch (exception) {
    importError.value = extractErrorMessage(exception)
  }
}

function downloadTemplate() {
  const template = [
    'title,location,price,type,image,description',
    'Modern Family House,"Canggu, Bali",450000,House,https://example.com/house.jpg,Spacious family house with pool.',
  ].join('\n')
  const blob = new Blob([template], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = 'properties-template.csv'
  link.click()
  URL.revokeObjectURL(url)
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Properties</h1>
        <p class="mt-1 text-sm text-gray-500">Manage all property listings</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <SecondaryButton @click="openImport">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
          </svg>
          Import CSV
        </SecondaryButton>
        <PrimaryButton @click="openCreate">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Create Property
        </PrimaryButton>
      </div>
    </div>

    <SearchBar v-model="propertyStore.search" placeholder="Search properties..." :loading="propertyStore.searching" />

    <Loading v-if="propertyStore.loading" message="Loading properties..." />

    <EmptyState
      v-else-if="propertyStore.filtered.length === 0"
      title="No properties found"
      message="Create a new property, import a CSV, or adjust your search."
    >
      <PrimaryButton @click="openCreate">Create Property</PrimaryButton>
    </EmptyState>

    <div v-else class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3.5 text-left font-semibold text-gray-700">Property</th>
              <th class="hidden px-6 py-3.5 text-left font-semibold text-gray-700 md:table-cell">Location</th>
              <th class="hidden px-6 py-3.5 text-left font-semibold text-gray-700 sm:table-cell">Type</th>
              <th class="px-6 py-3.5 text-left font-semibold text-gray-700">Price</th>
              <th class="px-6 py-3.5 text-left font-semibold text-gray-700">Status</th>
              <th class="px-6 py-3.5 text-right font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="property in propertyStore.filtered" :key="property.id" class="transition hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <img
                    v-if="property.image"
                    :src="property.image"
                    :alt="property.title"
                    class="size-10 shrink-0 rounded-lg object-cover"
                  >
                  <span
                    v-else
                    class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-300"
                  >
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                    </svg>
                  </span>
                  <span class="font-medium text-gray-900">{{ property.title }}</span>
                </div>
              </td>
              <td class="hidden px-6 py-4 text-gray-500 md:table-cell">{{ property.location }}</td>
              <td class="hidden px-6 py-4 sm:table-cell">
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                  {{ property.type }}
                </span>
              </td>
              <td class="px-6 py-4 font-semibold text-primary-600">{{ formatPrice(property.price) }}</td>
              <td class="px-6 py-4">
                <span
                  class="rounded-full px-3 py-1 text-xs font-semibold"
                  :class="property.is_published ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                >
                  {{ property.is_published ? 'Published' : 'Pending Review' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex justify-end gap-2">
                  <button
                    type="button"
                    class="rounded-lg p-2 transition"
                    :class="property.is_published
                      ? 'text-green-600 hover:bg-amber-50 hover:text-amber-600'
                      : 'text-amber-500 hover:bg-green-50 hover:text-green-600'"
                    :aria-label="property.is_published ? `Unpublish ${property.title}` : `Publish ${property.title}`"
                    :title="property.is_published ? 'Unpublish from website' : 'Publish to website'"
                    @click="handleTogglePublish(property)"
                  >
                    <svg v-if="property.is_published" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg v-else class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                  </button>
                  <button
                    type="button"
                    class="rounded-lg p-2 text-gray-500 transition hover:bg-primary-50 hover:text-primary-600"
                    :aria-label="`Edit ${property.title}`"
                    @click="openEdit(property)"
                  >
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                    </svg>
                  </button>
                  <button
                    type="button"
                    class="rounded-lg p-2 text-gray-500 transition hover:bg-red-50 hover:text-red-600"
                    :aria-label="`Delete ${property.title}`"
                    @click="openDelete(property)"
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

    <Modal v-model="formOpen" :title="editing ? 'Edit Property' : 'Create Property'" size="lg">
      <PropertyForm
        :key="editing?.id ?? 'create'"
        :property="editing"
        :saving="propertyStore.saving"
        :error="formError"
        @save="handleSave"
      />
    </Modal>

    <Modal v-model="importOpen" title="Import Properties from CSV" size="lg">
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          Upload a CSV file with columns
          <span class="font-mono text-xs font-semibold text-gray-800">title, location, price, type, image, description</span>.
          The image column takes an image URL and may be empty. Imported listings arrive as
          <span class="font-semibold text-amber-700">Pending Review</span> — edit and publish them from this table.
        </p>

        <label
          class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center transition hover:border-primary-400 hover:bg-primary-50"
        >
          <svg class="size-8 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
          </svg>
          <span class="text-sm font-medium text-gray-700">
            {{ importFile ? importFile.name : 'Click to choose a CSV file' }}
          </span>
          <span class="text-xs text-gray-500">Maximum 2 MB, up to 500 rows</span>
          <input type="file" accept=".csv,text/csv" class="sr-only" @change="handleImportFile">
        </label>

        <p v-if="importError" role="alert" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">{{ importError }}</p>

        <div v-if="importResult" class="space-y-3">
          <p class="rounded-xl bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ importResult.imported }} properties imported successfully.
          </p>
          <div v-if="importResult.skipped.length" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
            <p class="text-sm font-semibold text-amber-800">{{ importResult.skipped.length }} rows skipped:</p>
            <ul class="mt-2 space-y-1 text-sm text-amber-700">
              <li v-for="skipped in importResult.skipped" :key="skipped.row">
                Row {{ skipped.row }}: {{ skipped.errors.join(' ') }}
              </li>
            </ul>
          </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
          <button
            type="button"
            class="text-sm font-semibold text-primary-600 transition hover:text-primary-700"
            @click="downloadTemplate"
          >
            Download CSV template
          </button>
          <div class="flex justify-end gap-3">
            <SecondaryButton @click="importOpen = false">
              {{ importResult ? 'Close' : 'Cancel' }}
            </SecondaryButton>
            <PrimaryButton :loading="propertyStore.saving" :disabled="!importFile" @click="handleImport">
              Import
            </PrimaryButton>
          </div>
        </div>
      </div>
    </Modal>

    <Modal v-model="deleteOpen" title="Delete Property">
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          Are you sure you want to delete
          <span class="font-semibold text-gray-900">{{ deleting?.title }}</span>?
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
