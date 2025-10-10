<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pengguna harus terotentikasi DAN memiliki salah satu peran: admin atau mentor
        if (!$this->user()) {
            return false;
        }

        // Menggunakan Spatie hasAnyRole()
        return $this->user()->hasAnyRole(['admin', 'mentor']); 
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:courses,name'],
            'thumbnail' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // Wajib File Upload
            'about' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'is_popular' => ['required', 'boolean'],
        ];
    }
}