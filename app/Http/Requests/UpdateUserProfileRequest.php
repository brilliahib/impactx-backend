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
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],
            'username'   => ['nullable', 'string', 'max:100', 'unique:users,username,' . $this->user()->id],

            'about_description' => ['nullable', 'string'],
            'profile_images'    => ['nullable', 'image', 'max:2048'],
            'role'              => ['nullable', 'string', 'max:100'],
            'university'        => ['nullable', 'string', 'max:150'],
            'major'             => ['nullable', 'string', 'max:150'],
            'contact_info'      => ['nullable', 'array'],
            'skills'            => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Username sudah dipakai, silakan gunakan yang lain.',
            'profile_images.image' => 'File harus berupa gambar.',
            'profile_images.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
