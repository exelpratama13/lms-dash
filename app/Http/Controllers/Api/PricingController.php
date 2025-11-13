<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\PricingServiceInterface;
use Illuminate\Http\JsonResponse;

class PricingController extends Controller
{
    protected $service;

    public function __construct(PricingServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * List semua harga paket (Pricings) untuk Course tertentu.
     */
    public function listPricings(int $courseId): JsonResponse
    {
        try {
            // Variabel diubah menjadi $course untuk merefleksikan data yang diterima
            $course = $this->service->listPricings($courseId);

            // Jika course tidak ditemukan, kembalikan 404
            if (!$course) {
                 return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found.',
                ], 404);
            }

            // Kembalikan data course yang sudah berisi pricings dan batches
            return response()->json([
                'status' => 'success',
                'data' => $course,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve course data: ' . $e->getMessage(),
            ], 500);
        }
    }
}