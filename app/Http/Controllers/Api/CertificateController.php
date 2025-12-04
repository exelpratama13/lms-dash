<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\CertificateServiceInterface;
use App\Http\Requests\StoreCertificateRequest;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateServiceInterface $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Create a new certificate.
     */
    public function store(StoreCertificateRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $validatedData['user_id'] = Auth::id();

            // Potentially check for eligibility here or in the service
            // e.g., if user has already been issued a certificate for this course.

            $certificate = $this->certificateService->createCertificate($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Certificate created successfully. The PDF is being generated.',
                'data' => $certificate,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create certificate: ' . $e->getMessage(),
            ], 500);
        }
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
