<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertySubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'listing_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'status' => ['required', 'string', 'in:draft,pending'],
            'description' => ['required', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'publish_ready' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'A submission can only be saved as a draft or submitted for review.',
            'listing_price.max' => 'The listing price may not exceed $999,999,999,999.99.',
        ];
    }
}
