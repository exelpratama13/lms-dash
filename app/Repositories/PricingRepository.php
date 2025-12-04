<?php

namespace App\Repositories;

use App\Interfaces\PricingRepositoryInterface;
use App\Models\Pricing; // Asumsi model Pricing sudah ada
use Illuminate\Database\Eloquent\Collection;

class PricingRepository implements PricingRepositoryInterface
{
    public function getPricingsByCourseId(int $courseId): Collection
    {
        // Mengambil semua Pricing yang terhubung ke Course ID tertentu melalui tabel pivot 'coursepricing'
        return Pricing::whereHas('courses', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })
        ->get();
        
        /*
        CATATAN: Implementasi ini mengasumsikan bahwa:
        1. Model Pricing memiliki relasi belongsToMany ke Course, dengan nama relasi 'courses'.
           (function courses() { return $this->belongsToMany(Course::class, 'coursepricing', 'pricing_id', 'course_id'); })
        2. Jika relasi belongsToMany belum didefinisikan, Anda bisa menggunakan query JOIN eksplisit:
        
           return Pricing::join('coursepricing', 'pricings.id', '=', 'coursepricing.pricing_id')
                ->where('coursepricing.course_id', $courseId)
                ->select('pricings.*') // Pastikan hanya mengambil kolom dari tabel Pricing
                ->get();
        */
    }

    public function findById(int $id)
    {
        return Pricing::findOrFail($id);
    }
}