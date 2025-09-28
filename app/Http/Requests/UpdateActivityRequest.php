<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'             => 'sometimes|string|max:255',
            'activity_type'     => 'sometimes|string|max:100',
            'activity_category' => 'sometimes|string|max:100',
            'images'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'location'          => 'sometimes|string|max:255',
            'start_date'        => 'sometimes|date',
            'end_date'          => 'sometimes|date|after_or_equal:start_date',
            'max_participants'  => 'sometimes|integer|min:1',
            'description'       => 'sometimes|string',
            'requirements'      => 'sometimes|string',
            'benefits'          => 'nullable|string',
        ];
    }
}
