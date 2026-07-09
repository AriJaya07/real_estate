import axios from 'axios'

export const TOKEN_KEY = 'dreamrue_token'

export const api = axios.create({
  headers: {
    Accept: 'application/json',
  },
})

export function extractErrorMessage(error: unknown): string {
  if (axios.isAxiosError(error)) {
    const data = error.response?.data as { message?: string; errors?: Record<string, string[]> } | undefined
    const firstError = data?.errors ? Object.values(data.errors)[0]?.[0] : undefined
    return firstError ?? data?.message ?? 'Something went wrong. Please try again.'
  }
  return 'Something went wrong. Please try again.'
}
