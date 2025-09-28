<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateActivityRequest extends FormRequest
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
            'title'             => 'required|string|max:255',
            'activity_type'     => 'required|string|max:100',
            'activity_category' => 'required|string|max:100',
            'images'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'location'          => 'required|string|max:255',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'max_participants'  => 'required|integer|min:1',
            'description'       => 'required|string',
            'requirements'      => 'required|string',
            'benefits'          => 'nullable|string',
        ];
    }
}
