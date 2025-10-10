<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan untuk membuat request ini.
     */
    public function authorize(): bool
    {
        // Pastikan pengguna sudah login dan memiliki peran yang diizinkan (Admin atau Mentor)
        if (!$this->user()) {
            return false;
        }

        // Otorisasi menggunakan Spatie hasAnyRole()
        return $this->user()->hasAnyRole(['admin', 'mentor']); 
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     */
    public function rules(): array
    {
        // Ambil ID Course dari route
        $courseId = $this->route('id'); 
        
        return [
            // Nama harus unik, kecuali untuk course yang sedang diupdate (ID saat ini)
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('courses', 'name')->ignore($courseId)],
            
            // Thumbnail bersifat opsional saat update
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], 
            
            'about' => ['nullable', 'string'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'is_popular' => ['sometimes', 'required', 'boolean'],
        ];
    }
}