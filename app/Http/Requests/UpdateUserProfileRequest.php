<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
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
            'about_description' => 'sometimes|string',
            'profile_images' => 'sometimes|file|image|max:2048',
            'role' => 'sometimes|string',
            'university' => 'sometimes|string',
            'major' => 'sometimes|string',
            'contact_info' => 'sometimes|array',
            'contact_info.*' => 'string',
            'skills' => 'sometimes|array',
            'skills.*' => 'string',
        ];
    }
}
