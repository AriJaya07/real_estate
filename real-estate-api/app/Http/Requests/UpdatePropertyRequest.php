<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'type' => ['required', 'string', 'in:House,Apartment,Villa,Land,Office'],
            'image' => ['nullable', 'url:http,https', 'max:2048'],
            'description' => ['required', 'string', 'max:5000'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.url' => 'The image must be a valid URL starting with http or https.',
            'price.max' => 'The price may not exceed $999,999,999,999.99.',
        ];
    }
}
