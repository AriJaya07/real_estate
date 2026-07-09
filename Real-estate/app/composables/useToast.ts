export interface ToastItem {
  id: number
  message: string
  variant: 'success' | 'error'
}

const toasts = ref<ToastItem[]>([])
let nextId = 0

export function useToast() {
  function push(message: string, variant: ToastItem['variant'] = 'success') {
    const id = ++nextId
    toasts.value.push({ id, message, variant })
    setTimeout(() => dismiss(id), 3500)
  }

  function dismiss(id: number) {
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
  }

  return { toasts, push, dismiss }
}
