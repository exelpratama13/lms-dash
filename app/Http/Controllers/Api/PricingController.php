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
            $pricings = $this->service->listPricings($courseId);

            if ($pricings->isEmpty()) {
                 return response()->json([
                    'status' => 'success',
                    'message' => 'No pricings found for this course.',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => $pricings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve pricings: ' . $e->getMessage(),
            ], 500);
        }
    }
}