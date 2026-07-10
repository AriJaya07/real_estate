<template>
  <div class="flex min-h-screen flex-col bg-gray-50">
    <div class="px-4 pt-6 sm:px-8">
      <NuxtLink
        to="/"
        class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
      >
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Back to Browse
      </NuxtLink>
    </div>
    <div class="flex flex-1 items-center justify-center px-4 py-12">
      <slot />
    </div>
  </div>
</template>
