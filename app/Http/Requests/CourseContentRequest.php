<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseContentRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat request ini.
     */
    public function authorize(): bool
    {
        if (!$this->user()) {
            return false;
        }

        // Pastikan pengguna adalah Mentor atau Admin (Menggunakan Spatie)
        return $this->user()->hasAnyRole(['admin', 'mentor']); 
        
        // Catatan: Untuk keamanan optimal, Anda juga harus memverifikasi bahwa
        // mentor yang membuat request ini adalah mentor yang mengajarkan course ini.
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     */
    public function rules(): array
    {
        $contentId = $this->route('contentId');
        // ATURAN VALIDASI HANYA UNTUK FIELD YANG ADA DI $fillable MODEL CourseContent.php:
        // 'name', 'course_section_id', dan 'content'
        return [
            'name' => ['required', 'string', 'max:255' .$contentId],
            'course_section_id' => ['required', 'exists:course_sections,id'], // Memastikan Section ID valid
            'content' => ['nullable', 'string'], // Asumsi: 'content' adalah body teks atau data singkat
        ];
    }
}
