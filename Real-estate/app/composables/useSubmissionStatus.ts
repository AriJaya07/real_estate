import type { SubmissionStatus } from '~/types'

export interface StatusMeta {
  label: string
  badge: string
  dot: string
  next: string
}

const STATUS_META: Record<SubmissionStatus, StatusMeta> = {
  draft: {
    label: 'Draft',
    badge: 'bg-gray-100 text-gray-700',
    dot: 'bg-gray-400',
    next: 'Edit and submit for review when ready.',
  },
  pending: {
    label: 'Pending Review',
    badge: 'bg-amber-100 text-amber-700',
    dot: 'bg-amber-500',
    next: 'Queued for AI processing.',
  },
  ai_processing: {
    label: 'AI Processing',
    badge: 'bg-blue-100 text-blue-700',
    dot: 'bg-blue-500',
    next: 'AI is enriching the listing, then it moves to ClickUp review.',
  },
  clickup_review: {
    label: 'In ClickUp Review',
    badge: 'bg-purple-100 text-purple-700',
    dot: 'bg-purple-500',
    next: 'Awaiting team approval in ClickUp.',
  },
  ready: {
    label: 'Ready to Publish',
    badge: 'bg-teal-100 text-teal-700',
    dot: 'bg-teal-500',
    next: 'Approved. Publishing to the website shortly.',
  },
  published: {
    label: 'Published',
    badge: 'bg-green-100 text-green-700',
    dot: 'bg-green-500',
    next: 'Live on the website.',
  },
  rejected: {
    label: 'Rejected',
    badge: 'bg-red-100 text-red-700',
    dot: 'bg-red-500',
    next: 'Edit the submission and resubmit for review.',
  },
}

export const PIPELINE_STEPS: SubmissionStatus[] = [
  'pending',
  'ai_processing',
  'clickup_review',
  'ready',
  'published',
]

export const EDITABLE_STATUSES: SubmissionStatus[] = ['draft', 'rejected']

export function useSubmissionStatus() {
  function statusMeta(status: SubmissionStatus): StatusMeta {
    return STATUS_META[status] ?? STATUS_META.draft
  }

  function isEditable(status: SubmissionStatus): boolean {
    return EDITABLE_STATUSES.includes(status)
  }

  function pipelineIndex(status: SubmissionStatus): number {
    return PIPELINE_STEPS.indexOf(status)
  }

  return { statusMeta, isEditable, pipelineIndex }
}
