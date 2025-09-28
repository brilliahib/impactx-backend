<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserProfileRequest extends FormRequest
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
            'about_description' => 'nullable|string',
            'profile_images' => 'nullable|file|image|max:2048',
            'role' => 'required|string',
            'university' => 'nullable|string',
            'major' => 'nullable|string',
            'contact_info' => 'nullable|array',
            'contact_info.*' => 'string',
            'skills' => 'nullable|array',
            'skills.*' => 'string',
        ];
    }
}
