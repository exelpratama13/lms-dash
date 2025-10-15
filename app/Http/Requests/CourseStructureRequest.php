<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseStructureRequest extends FormRequest
{
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

    public function rules(): array
    {
        // Aturan untuk Tambah (POST) dan Update (PUT) Section
        $sectionId = $this->route('sectionId'); // Ambil ID untuk aturan unique
        
        return [
            'name' => ['required', 'string', 'max:255', 'unique:course_sections,name,' . $sectionId],
            'course_id' => ['required', 'exists:courses,id'],
            'position' => ['required', 'integer'],
        ];
    }
}