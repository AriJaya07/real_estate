export interface User {
  id: number
  username: string
  created_at: string
}

export interface Property {
  id: number
  title: string
  location: string
  price: number
  type: PropertyType
  image: string | null
  description: string
  is_published: boolean
  published_at: string | null
  created_at: string
}

export type PropertyType = 'House' | 'Apartment' | 'Villa' | 'Land' | 'Office'

export type SubmissionStatus =
  | 'draft'
  | 'pending'
  | 'ai_processing'
  | 'clickup_review'
  | 'ready'
  | 'published'
  | 'rejected'

export interface PropertySubmission {
  id: number
  property: Property
  owner_name: string
  phone: string
  email: string
  address: string
  listing_price: number
  status: SubmissionStatus
  description: string
  notes: string | null
  publish_ready: boolean
  published_at: string | null
  published_property_id: number | null
  published_property?: Property | null
  clickup_status: string | null
  created_at: string
}

export interface PropertyPayload {
  title: string
  location: string
  price: number
  type: PropertyType
  image: string | null
  description: string
  is_published: boolean
}

export interface ImportResult {
  imported: number
  skipped: { row: number; errors: string[] }[]
}

export interface AuthCredentials {
  username: string
  password: string
}

export interface SubmissionPayload {
  property_id: number
  owner_name: string
  phone: string
  email: string
  address: string
  listing_price: number
  status: SubmissionStatus
  description: string
  notes: string
  publish_ready: boolean
}

export type UpdateSubmissionPayload = Omit<SubmissionPayload, 'property_id'>
