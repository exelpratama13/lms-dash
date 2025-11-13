<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\CertificateServiceInterface;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateServiceInterface $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Mengambil daftar sertifikat untuk user yang sedang login.
     */
    public function myCertificates(): JsonResponse
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $certificates = $this->certificateService->getMyCertificates($userId);

            return response()->json([
                'status' => 'success',
                'message' => 'My certificates retrieved successfully.',
                'count' => $certificates->count(),
                'data' => $certificates,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve my certificates: ' . $e->getMessage(),
            ], 500);
        }
    }
}
