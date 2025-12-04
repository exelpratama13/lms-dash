<?php

namespace App\Services;

use App\Models\Sertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CertificateGeneratorService
{
    public function generatePdf(Sertificate $certificate): ?string
    {
        // Ensure relationships are loaded
        $certificate->load(['user', 'course', 'courseBatch', 'courseProgress']);

        if (!$certificate->user || !$certificate->course) {
            // Log error or throw exception if essential data is missing
            return null;
        }

        // Prepare data for the Blade view
        $data = [
            'certificate' => $certificate,
            'user' => $certificate->user,
            'course' => $certificate->course,
            'completion_date' => $certificate->created_at ? Carbon::parse($certificate->created_at)->format('F d, Y') : Carbon::now()->format('F d, Y'),
            // You might need to fetch mentor data if it's not directly on the course or certificate
            'mentor' => $certificate->course->mentors->first() ? $certificate->course->mentors->first()->user : null,
            // Optional: background image URL if you have one
            // 'background_image' => public_path('images/certificate_background.png'),
        ];

        // Render the Blade view to HTML
        $html = view('certificates.certificate', $data)->render();

        // Generate PDF with custom 16:9 ratio (e.g., 1280x720) and landscape orientation
        $pdf = Pdf::loadHtml($html)->setPaper([0, 0, 720.00, 1280.00], 'landscape');

        // Define file path
        $fileName = 'certificates/' . $certificate->code . '_' . $certificate->user->id . '_' . $certificate->course->id . '.pdf';

        // Store the PDF
        Storage::disk('public')->put($fileName, $pdf->output());

        // Update the certificate record with the file path
        $certificate->sertificate_url = $fileName;
        $certificate->save();

        return $fileName;
    }
}
